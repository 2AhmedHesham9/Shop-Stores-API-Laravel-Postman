<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ([
        'clientId'
    ]);

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(product::class, 'order_product',  'order_id', 'product_id')
            ->withPivot('quantity', 'created_at', 'updated_at', 'deleted_at')
            ->as('order_product')
            ->withTimestamps();
    }
}
