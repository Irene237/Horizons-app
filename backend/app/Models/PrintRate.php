<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintRate extends Model
{
    use HasFactory;

    protected $fillable = ['support_type', 'rate', 'calculation_type'];
}