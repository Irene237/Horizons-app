<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoursesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Récupérer la liste des formations avec leurs inscrits
     */
    public function collection()
    {
        return Course::with('enrollments')->get();
    }

    /**
     * Entêtes du fichier Excel
     */
    public function headings(): array
    {
        return [
            'ID Formation',
            'Intitulé du Cours',
            'Niveau',
            'Formateur',
            'Nombre d\'Inscrits',
            'Taux d\'Assiduité Moyen',
            'Revenus Générés (FCFA)'
        ];
    }

    /**
     * Mapping des indicateurs clés demandés par le module E
     */
    public function map($course): array
    {
        $enrollments = $course->enrollments;
        $avgAttendance = $enrollments->avg('attendance_rate') ?? 0;

        return [
            $course->id,
            $course->title,
            $course->level,
            $course->trainer_name,
            $enrollments->count(),
            round($avgAttendance, 2) . '%',
            $enrollments->sum('amount_paid')
        ];
    }
}