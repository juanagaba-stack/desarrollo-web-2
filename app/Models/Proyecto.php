<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    const ESTADOS = [
        'EN_REVISION' => 'En revisión',
        'CON_OBSERVACIONES' => 'Con observaciones',
        'APROBADO' => 'Aprobado',
        'RECHAZADO' => 'Rechazado',
    ];

    protected $guarded = [];

    protected $casts = [
        'fecha_radicacion' => 'date',
    ];

    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function correcciones()
    {
        return $this->hasMany(Correccion::class);
    }

    public function integrantes()
    {
        return $this->hasMany(IntegranteProyecto::class);
    }

    /*
     * Porcentaje de avance del expediente:
     *  - radicado / en revisión / rechazado: 30%
     *  - con observaciones: de 30% a 90% según cuántas estén resueltas
     *  - aprobado: 100% (el último 10% es el aval del comité)
     */
    public function getPorcentajeAvanceAttribute()
    {
        if ($this->estado == 'APROBADO') {
            return 100;
        }

        if ($this->estado == 'CON_OBSERVACIONES') {
            $total = $this->correcciones->count();

            if ($total > 0) {
                $resueltas = $this->correcciones->where('estado', 'RESUELTO')->count();
                return (int) round(30 + 60 * $resueltas / $total);
            }
        }

        return 30;
    }

    // Genera el consecutivo del año, ej: PRJ-2026-004
    public static function siguienteCodigo()
    {
        $anio = now()->year;

        $ultimo = static::where('codigo_radicado', 'like', "PRJ-{$anio}-%")
            ->pluck('codigo_radicado')
            ->map(fn ($codigo) => (int) substr($codigo, strrpos($codigo, '-') + 1))
            ->max();

        return sprintf('PRJ-%d-%03d', $anio, ($ultimo ?? 0) + 1);
    }
}
