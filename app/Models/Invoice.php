<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Invoice extends Model
{
    use HasFactory;
    protected $primaryKey = 'invoiceId';
    protected $table = 'invoice';

    protected $fillable = ([
        'order_id',
        'invoiceTotal'
    ]);

    public function order()
    {
        return $this->hasOne(Order::class,'order_id','invoiceId');
    }
}
