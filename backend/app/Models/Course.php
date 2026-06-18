<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'duration_hours', 'level', 
        'trainer_name', 'price', 'max_capacity', 'start_date', 'end_date'
    ];

    protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'price' => 'decimal:2',
    ];

    // Relation : Une formation a plusieurs inscriptions
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}