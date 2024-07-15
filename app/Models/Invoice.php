<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'invoiceId';
    protected $table = 'invoice';

    protected $fillable = ([
        'order_id',
        'invoiceTotal'
    ]);

    public function order()
    {
        return $this->hasOne(Order::class, 'order_id', 'invoiceId');
    }
}
