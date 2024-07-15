<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'product';
    protected $primaryKey = 'productId';
    protected $fillable = [
        'image',
        'name',
        'price',
        'amount',
        'shopId',
    ];
    public function shops()
    {
        return $this->belongsTo(Shop::class,   'shopId');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')->withPivot('quantity', 'created_at', 'updated_at')->as('order_product');
    }
}
