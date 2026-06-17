<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'client_id', 'payment_status', 'amount_paid', 'receipt_number'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation : Une inscription possède plusieurs fiches de présence
    public function attendances()
    {
        return $this->hasMany(CourseAttendance::class);
    }

    // ALGORITHME DU TAUX DE PRÉSENCE (Retourne un pourcentage, ex: 85.5)
    public function getAttendanceRateAttribute()
    {
        $totalSessions = $this->attendances()->count();
        if ($totalSessions === 0) {
            return 0; // Pas encore de sessions d'émargement enregistrées
        }

        $presentCount = $this->attendances()->where('is_present', true)->count();
        
        return round(($presentCount / $totalSessions) * 100, 2);
    }
}