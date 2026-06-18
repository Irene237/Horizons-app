<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'phone', 
        'email', 
        'address', 
        'balance_due',
        'user_id' // Ajoute ceci pour permettre l'assignation de masse
    ];

    /**
     * Relation inverse : Un client appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}