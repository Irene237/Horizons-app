<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\PrintOrder;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use App\Exports\PrintOrdersExport;
use App\Exports\CoursesExport;

class ReportController extends Controller
{
    // =========================================================================
    // 1. RAPPORT DES VENTES (Boutique)
    // =========================================================================
    public function salesReport(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfDay()->toDateString());

        $query = Sale::with(['user', 'products'])->whereBetween('created_at', [$startDate, $endDate]);

        // Filtrer par vendeur si spécifié
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sales = $query->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalSalesCount = $sales->count();

        $data = [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_revenue' => $totalRevenue,
            'total_sales_count' => $totalSalesCount,
            'sales' => $sales
        ];

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.reports.sales', $data);
            return $pdf->download("Rapport_Ventes_{$startDate}_to_{$endDate}.pdf");
        }

        if ($request->query('format') === 'excel') {
            return Excel::download(new SalesExport($startDate, $endDate, $request->user_id), 'Rapport_Ventes.xlsx');
        }

        return response()->json($data, 200);
    }

    // =========================================================================
    // 2. RAPPORT DES IMPRESSIONS (Module C)
    // =========================================================================
    public function printReport(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfDay()->toDateString());

        // Uniquement les commandes fermes (pas les devis)
        $orders = PrintOrder::where('is_quotation', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRevenue = $orders->sum('total_price');
        $ordersCount = $orders->count();
        
        // Chiffre d'affaires groupé par type de support
        $revenueBySupport = $orders->groupBy('support_type')->map(function ($group) {
            return $group->sum('total_price');
        });

        $data = [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_revenue' => $totalRevenue,
            'orders_count' => $ordersCount,
            'revenue_by_support' => $revenueBySupport,
            'orders' => $orders
        ];

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.reports.prints', $data);
            return $pdf->download("Rapport_Impressions_{$startDate}_to_{$endDate}.pdf");
        }

        if ($request->query('format') === 'excel') {
            return Excel::download(new PrintOrdersExport($startDate, $endDate), 'Rapport_Impressions.xlsx');
        }

        return response()->json($data, 200);
    }

    // =========================================================================
    // 3. RAPPORT DES FORMATIONS (Module D)
    // =========================================================================
    public function coursesReport(Request $request)
    {
        $totalEnrollments = CourseEnrollment::count();
        $totalRevenues = CourseEnrollment::sum('amount_paid');

        // Récupérer tous les cours pour calculer la moyenne de présence
        $courses = Course::with('enrollments')->get();
        
        $coursesData = $courses->map(function ($course) {
            $enrollments = $course->enrollments;
            $avgAttendance = $enrollments->avg('attendance_rate') ?? 0;
            
            return [
                'id' => $course->id,
                'title' => $course->title,
                'enrollments_count' => $enrollments->count(),
                'average_attendance_rate' => round($avgAttendance, 2) . '%',
                'revenues_generated' => $enrollments->sum('amount_paid')
            ];
        });

        $data = [
            'total_enrollments' => $totalEnrollments,
            'total_revenues' => $totalRevenues,
            'courses_summary' => $coursesData
        ];

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.reports.courses', $data);
            return $pdf->download("Rapport_Formations.pdf");
        }

        if ($request->query('format') === 'excel') {
            return Excel::download(new CoursesExport(), 'Rapport_Formations.xlsx');
        }

        return response()->json($data, 200);
    }
}