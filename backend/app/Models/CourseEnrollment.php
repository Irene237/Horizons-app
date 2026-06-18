<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'client_id', 'payment_status', 'amount_paid', 'receipt_number'];

    // Ajoute ceci pour que 'attendance_rate' soit inclus automatiquement dans le JSON
    protected $appends = ['attendance_rate'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function attendances()
    {
        return $this->hasMany(CourseAttendance::class);
    }

    /**
     * Accesseur pour le taux de présence.
     * Accessible via : $enrollment->attendance_rate
     */
    public function getAttendanceRateAttribute()
    {
        $totalSessions = $this->attendances()->count();
        
        if ($totalSessions === 0) {
            return 0.0;
        }

        $presentCount = $this->attendances()->where('is_present', true)->count();
        
        return round(($presentCount / $totalSessions) * 100, 2);
    }
}