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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('articleID');
            $table->foreign('articleID')->references('refEHK')->on('articles')->onDelete('restrict');
            $table->string('emplacementID');
            $table->foreign('emplacementID')->references('pos')->on('emplacements')->onDelete('restrict');
            $table->integer('qtestock')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};