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
    Schema::create('integrante_proyectos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->enum('rol_en_proyecto', ['AUTOR_ESTUDIANTE', 'DIRECTOR_TUTOR', 'JURADO_EVALUADOR']);
        $table->timestamps();
    });
}
};
