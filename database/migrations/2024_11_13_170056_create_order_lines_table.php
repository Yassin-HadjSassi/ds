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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orderID');
            $table->foreign('orderID')->references('id')->on('orders')->onDelete('restrict');
            $table->string('articleID');
            $table->foreign('articleID')->references('refEHK')->on('articles')->onDelete('restrict');
            $table->integer('qte')->unsigned();
            $table->integer('qte_d')->unsigned();
            $table->decimal('unitprice', 8, 2);
            $table->decimal('linetotal', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};