<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id('productId');
            $table->string('image')->nullable();
            $table->string('name')->isNotEmpty();
            $table->float('price')->isNotEmpty();
            $table->integer('amount')->isNotEmpty();
            $table->unsignedBigInteger('shopId');
            $table->foreign('shopId')->references('shopId')->on('shop');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
