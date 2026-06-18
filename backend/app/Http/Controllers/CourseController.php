<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CourseController extends Controller
{
    // =========================================================================
    // D1. CATALOGUE DES FORMATIONS
    // =========================================================================

    public function index()
    {
        $courses = Course::withCount('enrollments')->orderBy('start_date', 'asc')->get();

        $courses->transform(function ($course) {
            $course->available_places = max(0, $course->max_capacity - $course->enrollments_count);
            return $course;
        });

        return response()->json($courses, 200);
    }

    // =========================================================================
    // D2. INSCRIPTIONS & TÉLÉCHARGEMENTS
    // =========================================================================

    public function enrollClient(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'client_id' => 'required|exists:clients,id',
            'payment_status' => 'required|in:Payé,Partiel,Non payé',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $course = Course::findOrFail($request->course_id);

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

        return response()->json(['message' => 'Inscription réussie !', 'enrollment_id' => $enrollment->id], 201);
    }

    public function myEnrollments(Request $request)
    {
        $user = $request->user();
        
        $enrollments = CourseEnrollment::whereHas('client', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('course')->get();

        return response()->json($enrollments, 200);
    }

    public function downloadReceipt($id)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->findOrFail($id);

        $pdf = Pdf::loadView('exports.receipt', compact('enrollment'));
        return $pdf->download("RECU-{$enrollment->receipt_number}.pdf");
    }

    public function downloadInvoice($id)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->findOrFail($id);

        $pdf = Pdf::loadView('exports.invoice', compact('enrollment'));
        
        return $pdf->download("FACTURE-{$enrollment->receipt_number}.pdf");
    }

    // =========================================================================
    // D3. PRÉSENCES
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

        return response()->json(['message' => 'Émargement enregistré !'], 200);
    }

    // =========================================================================
    // D4. ATTESTATIONS
    // =========================================================================

    public function downloadCertificate($id)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->findOrFail($id);

        if ($enrollment->attendance_rate < 70.00) {
            return response()->json(['message' => "Seuil minimal de 70% non atteint. Votre taux: {$enrollment->attendance_rate}%"], 403);
        }

        $pdf = Pdf::loadView('exports.certificate', [
            'enrollment' => $enrollment,
            'attendance_rate' => $enrollment->attendance_rate
        ]);
        
        return $pdf->download("ATTESTATION-" . Str::slug($enrollment->client->name) . ".pdf");
    }
}