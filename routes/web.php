<?php

use App\Http\Controllers\CorreccionController;
use App\Http\Controllers\IntegranteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // el dashboard es el listado de proyectos
    Route::get('/dashboard', [ProyectoController::class, 'index'])->name('dashboard');
    Route::resource('proyectos', ProyectoController::class)->except('index');

    // observaciones: crear va anidado al proyecto, el resto se hace por id
    Route::resource('proyectos.correcciones', CorreccionController::class)
        ->only(['store', 'edit', 'update', 'destroy'])
        ->parameters(['correcciones' => 'correccion'])
        ->shallow();

    Route::patch('/correcciones/{correccion}/alternar', [CorreccionController::class, 'alternar'])->name('correcciones.alternar');
    Route::patch('/proyectos/{proyecto}/estado', [ProyectoController::class, 'cambiarEstado'])->name('proyectos.estado');

    Route::post('/proyectos/{proyecto}/integrantes', [IntegranteController::class, 'store'])->name('integrantes.store');
    Route::delete('/integrantes/{integrante}', [IntegranteController::class, 'destroy'])->name('integrantes.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
