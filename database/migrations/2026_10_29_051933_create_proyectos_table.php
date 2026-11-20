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
    Schema::create('proyectos', function (Blueprint $table) {
        $table->id();
        $table->string('codigo_radicado')->unique(); // ej. PRJ-2026-001
        $table->string('titulo');
        $table->string('asunto_linea_investigacion');
        $table->text('resumen');
        $table->enum('estado', ['EN_REVISION', 'CON_OBSERVACIONES', 'APROBADO', 'RECHAZADO'])->default('EN_REVISION');
        $table->date('fecha_radicacion');
        $table->foreignId('usuario_creador_id')->constrained('users')->onDelete('cascade');
        $table->timestamps();
    });
}};
