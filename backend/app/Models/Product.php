<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference',
        'category',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'alert_threshold',
        'image_path',
        'supplier'
    ];
}