<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'address', 'balance_due'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}