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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('refEHK')->unique(); 
            // Désignation avec contrainte unique et limite de taille
            $table->string('designation', 100)->unique();
            // Marque avec limite de taille (facultatif)
            $table->string('marque', 50)->nullable();
            $table->string('accessoire', 50)->nullable();
            $table->string('refOrigine', 50)->index()->nullable();

            // Prix avec type decimal
            $table->decimal('prixHT', 8, 2);
            // Image avec limite de taille (255 caractères)
            $table->string('imageart', 255)->nullable();
            $table->integer('categorieID');
            $table->foreign('categorieID')->references('refcategorie')->on('categories')->onDelete('restrict');
            $table->integer('formeID')->nullable();
            // Définition de la clé étrangère avec contrainte onDelete restrict
            $table->foreign('formeID')->references('refforme')->on('formes')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};