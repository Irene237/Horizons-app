<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 
        'user_id', 
        'subtotal', 
        'discount', 
        'total', 
        'amount_paid', 
        'payment_method', 
        'invoice_number'
    ];

    // Relation avec les détails de la vente
    public function details()
    {
        return $this->hasMany(Product::class, 'sale_details')->withPivot('quantity', 'price');
    }

    // RELATION CORRIGÉE ICI : On utilise $this au lieu de $table
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}