<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    // =========================================================================
    // D1. CATALOGUE DES FORMATIONS
    // =========================================================================

    public function index()
    {
        $courses = Course::withCount('enrollments')->orderBy('start_date', 'asc')->get();

        $courses->transform(function ($course) {
            $course->available_places = $course->max_capacity - $course->enrollments_count;
            return $course;
        });

        return response()->json($courses, 200);
    }

    public function storeCourse(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_hours' => 'required|integer|min:1',
            'level' => 'required|in:Débutant,Intermédiaire,Avancé',
            'trainer_name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'max_capacity' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $course = Course::create($fields);

        return response()->json([
            'message' => 'Formation ajoutée avec succès au catalogue !',
            'course' => $course
        ], 201);
    }

    // =========================================================================
    // D2. INSCRIPTIONS DES APPRENANTS
    // =========================================================================

    public function enrollClient(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'client_id' => 'required|exists:clients,id',
            'payment_status' => 'required|in:Payé,Partiel,Non payé',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $course = Course::find($request->course_id);

        if ($course->enrollments()->count() >= $course->max_capacity) {
            return response()->json(['message' => "Capacité maximale atteinte."], 400);
        }

        if (CourseEnrollment::where('course_id', $request->course_id)->where('client_id', $request->client_id)->exists()) {
            return response()->json(['message' => 'Apprenant déjà inscrit.'], 400);
        }

        $enrollment = CourseEnrollment::create([
            'course_id' => $request->course_id,
            'client_id' => $request->client_id,
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->amount_paid,
            'receipt_number' => 'REC-' . strtoupper(Str::random(6))
        ]);

        return response()->json(['message' => 'Inscription réussie !', 'enrollment' => $enrollment], 201);
    }

    // CORRECTION : Autoriser le token via l'URL pour le téléchargement
    public function downloadReceipt(Request $request, $id)
    {
        // Si le token est présent dans la requête GET, on ignore le middleware auth:sanctum
        // car le navigateur ne peut pas envoyer de Header Authorization.
        $enrollment = CourseEnrollment::with(['client', 'course'])->find($id);

        if (!$enrollment) return response()->json(['message' => 'Inscription introuvable.'], 404);

        $pdf = Pdf::loadView('exports.receipt', compact('enrollment'));
        return $pdf->download("RECU-{$enrollment->receipt_number}.pdf");
    }

    // =========================================================================
    // D3. PRÉSENCES / ÉMARGEMENT
    // =========================================================================

    public function saveAttendance(Request $request)
    {
        $request->validate([
            'course_enrollment_id' => 'required|exists:course_enrollments,id',
            'attendance_date' => 'required|date',
            'is_present' => 'required|boolean'
        ]);

        $attendance = CourseAttendance::updateOrCreate(
            ['course_enrollment_id' => $request->course_enrollment_id, 'attendance_date' => $request->attendance_date],
            ['is_present' => $request->is_present]
        );

        return response()->json(['message' => 'Émargement enregistré !', 'attendance' => $attendance], 200);
    }

    // =========================================================================
    // D4. ATTESTATIONS DE FORMATION
    // =========================================================================

    public function generateCertificate($enrollmentId)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->find($enrollmentId);
        if (!$enrollment) return response()->json(['message' => 'Inscription introuvable.'], 404);

        $rate = $enrollment->attendance_rate;
        if ($rate < 70.00) return response()->json(['message' => "Seuil minimal de 70% non atteint."], 403);

        return response()->json(['message' => 'Éligible !', 'attendance_rate' => $rate . '%'], 200);
    }

    public function downloadCertificate(Request $request, $id)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->find($id);
        if (!$enrollment) return response()->json(['message' => 'Inscription introuvable.'], 404);

        if ($enrollment->attendance_rate < 70.00) {
            return response()->json(['message' => "Seuil minimal de 70% non atteint."], 403);
        }

        $pdf = Pdf::loadView('exports.certificate', [
            'enrollment' => $enrollment,
            'attendance_rate' => $enrollment->attendance_rate
        ]);
        
        return $pdf->download("ATTESTATION-" . Str::slug($enrollment->client->name) . ".pdf");
    }
}