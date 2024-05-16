<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Shop  extends Model
{
    use HasFactory;
    protected $table = 'shop';
    protected $primaryKey = 'shopId';
    protected $fillable = [


        'shopId',
        'nameOfStore',
        'storeLocation',
        'ownerId',

    ];
    public function owner()
    {
        return $this->belongsTo(User::class, 'ownerId',);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'shopproduct', 'shopId', 'productId')->withTimestamps();
    }
}
