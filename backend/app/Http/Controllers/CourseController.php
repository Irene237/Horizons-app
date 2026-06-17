<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf; // IMPORTATION DE DOMPDF POUR LES EXPORTS (MODULE D)

class CourseController extends Controller
{
    // =========================================================================
    // D1. CATALOGUE DES FORMATIONS
    // =========================================================================

    /**
     * Lister toutes les formations du catalogue
     */
    public function index()
    {
        // On récupère les cours en comptant le nombre d'inscrits actuels
        $courses = Course::withCount('enrollments')->orderBy('start_date', 'asc')->get();
        return response()->json($courses, 200);
    }

    /**
     * Ajouter une nouvelle formation au catalogue
     */
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

    /**
     * Inscrire un apprenant (client) à une formation avec vérification des places
     */
    public function enrollClient(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'client_id' => 'required|exists:clients,id',
            'payment_status' => 'required|in:Payé,Partiel,Non payé',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $course = Course::find($request->course_id);

        // BLOCAGE CRITIQUE : Vérifier si la capacité maximale est atteinte
        $currentInscriptions = $course->enrollments()->count();
        if ($currentInscriptions >= $course->max_capacity) {
            return response()->json([
                'message' => "Inscription refusée : Cette formation a atteint sa capacité maximale de {$course->max_capacity} places !"
            ], 400);
        }

        // Vérifier si l'apprenant n'est pas déjà inscrit à ce même cours
        $alreadyEnrolled = CourseEnrollment::where('course_id', $request->course_id)
            ->where('client_id', $request->client_id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'Cet apprenant is déjà inscrit à cette formation.'
            ], 400);
        }

        // Générer un numéro de reçu unique (Ex: REC-A8F92D)
        $receiptNumber = 'REC-' . strtoupper(Str::random(6));

        $enrollment = CourseEnrollment::create([
            'course_id' => $request->course_id,
            'client_id' => $request->client_id,
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->amount_paid,
            'receipt_number' => $receiptNumber
        ]);

        return response()->json([
            'message' => 'Apprenant inscrit avec succès !',
            'enrollment' => $enrollment->load(['course', 'client'])
        ], 201);
    }

    /**
     * Télécharger le reçu d'inscription officiel en format PDF
     */
    public function downloadReceipt($id)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->find($id);

        if (!$enrollment) {
            return response()->json(['message' => 'Inscription introuvable.'], 404);
        }

        // Générer le PDF à partir du template HTML exports/receipt.blade.php
        $pdf = Pdf::loadView('exports.receipt', compact('enrollment'));
        
        return $pdf->download("RECU-{$enrollment->receipt_number}.pdf");
    }

    // =========================================================================
    // D3. PRÉSENCES / ÉMARGEMENT
    // =========================================================================

    /**
     * Prendre les présences pour une session/date donnée
     */
    public function saveAttendance(Request $request)
    {
        $request->validate([
            'course_enrollment_id' => 'required|exists:course_enrollments,id',
            'attendance_date' => 'required|date',
            'is_present' => 'required|boolean'
        ]);

        // Mettre à jour si la fiche existe déjà pour cette date, sinon la créer
        $attendance = CourseAttendance::updateOrCreate(
            [
                'course_enrollment_id' => $request->course_enrollment_id,
                'attendance_date' => $request->attendance_date
            ],
            [
                'is_present' => $request->is_present
            ]
        );

        return response()->json([
            'message' => 'Émargement enregistré !',
            'attendance' => $attendance
        ], 200);
    }

    // =========================================================================
    // D4. ATTESTATIONS DE FORMATION
    // =========================================================================

    /**
     * Vérifier l'éligibilité et générer les données textuelles de l'attestation
     */
    public function generateCertificate($enrollmentId)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->find($enrollmentId);

        if (!$enrollment) {
            return response()->json(['message' => 'Inscription introuvable.'], 404);
        }

        // Récupérer le taux calculé dynamiquement par notre modèle (getAttendanceRateAttribute)
        $rate = $enrollment->attendance_rate;

        // VÉRIFICATION STRICTE DES 70% EXIGÉS
        if ($rate < 70.00) {
            return response()->json([
                'message' => "Génération impossible : Le taux de présence de l'apprenant est de {$rate}%. Le seuil minimal requis est de 70%."
            ], 403);
        }

        return response()->json([
            'message' => 'Apprenant éligible ! Taux de présence validé.',
            'attendance_rate' => $rate . '%',
            'certificate_data' => [
                'recipient' => $enrollment->client->name,
                'course_title' => $enrollment->course->title,
                'duration' => $enrollment->course->duration_hours . ' heures',
                'trainer' => $enrollment->course->trainer_name,
                'date' => date('d/m/Y')
            ]
        ], 200);
    }

    /**
     * Télécharger l'attestation officielle en PDF (Vérification stricte de l'assiduité >= 70%)
     */
    public function downloadCertificate($id)
    {
        $enrollment = CourseEnrollment::with(['client', 'course'])->find($id);

        if (!$enrollment) {
            return response()->json(['message' => 'Inscription introuvable.'], 404);
        }

        // Récupérer le taux calculé
        $attendance_rate = $enrollment->attendance_rate;

        // SÉCURITÉ DU SEUIL : Blocage strict si l'apprenant n'a pas été assez assidu
        if ($attendance_rate < 70.00) {
            return response()->json([
                'message' => "Génération impossible : Le taux de présence est de {$attendance_rate}%. Le seuil minimal requis est de 70%."
            ], 403);
        }

        // Génération du PDF avec la vue HTML exports/certificate.blade.php
        $pdf = Pdf::loadView('exports.certificate', compact('enrollment', 'attendance_rate'));
        
        $slugName = Str::slug($enrollment->client->name);
        return $pdf->download("ATTESTATION-{$slugName}.pdf");
    }
}