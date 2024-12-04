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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('userID');
            $table->foreign('userID')->references('name')->on('users')->onDelete('restrict');
            $table->string('status', 50);
            $table->integer('totalnumber');
            $table->decimal('prixHT', 8, 2);
            $table->decimal('prixtotal', 8, 2);
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};