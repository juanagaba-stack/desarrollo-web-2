<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegranteProyecto extends Model
{
    const ROLES = [
        'AUTOR_ESTUDIANTE' => 'Autor (estudiante)',
        'DIRECTOR_TUTOR' => 'Director / Tutor',
        'JURADO_EVALUADOR' => 'Jurado evaluador',
    ];

    protected $fillable = [
        'proyecto_id',
        'user_id',
        'rol_en_proyecto',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
