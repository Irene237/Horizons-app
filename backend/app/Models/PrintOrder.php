<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'user_id', 'support_type', 'width_cm', 'height_cm', 
        'quantity', 'file_path', 'unit_price', 'total_price', 'status', 
        'is_quotation', 'document_number'
    ];

    // Relation : Une commande appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation : Une commande est enregistrée par un utilisateur (vendeur/admin)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}