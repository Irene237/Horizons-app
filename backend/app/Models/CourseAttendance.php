<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAttendance extends Model
{
    use HasFactory;

    protected $fillable = ['course_enrollment_id', 'attendance_date', 'is_present'];

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class);
    }
}