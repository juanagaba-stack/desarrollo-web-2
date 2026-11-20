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
    Schema::create('correccions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
        $table->text('descripcion_observacion');
        $table->date('fecha_limite');
        $table->enum('estado', ['PENDIENTE', 'RESUELTO'])->default('PENDIENTE');
        $table->timestamps();
    });
}
};
