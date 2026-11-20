<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correccion extends Model
{
    const ESTADOS = [
        'PENDIENTE' => 'Pendiente',
        'RESUELTO' => 'Resuelto',
    ];

    protected $fillable = [
        'proyecto_id',
        'descripcion_observacion',
        'fecha_limite',
        'estado',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
    ];

    // positivo = faltan días, 0 = vence hoy, negativo = ya venció
    public function getDiasRestantesAttribute()
    {
        return (int) today()->diffInDays($this->fecha_limite, false);
    }

    // solo las pendientes tienen semáforo: rojo (vencida), amarillo (3 días o menos), verde
    public function getSemaforoAttribute()
    {
        if ($this->estado == 'RESUELTO') {
            return 'verde';
        }

        if ($this->dias_restantes < 0) {
            return 'rojo';
        }

        return $this->dias_restantes <= 3 ? 'amarillo' : 'verde';
    }

    public function getTextoPlazoAttribute()
    {
        $dias = $this->dias_restantes;

        if ($dias < 0) {
            return 'Vencida hace ' . abs($dias) . ($dias == -1 ? ' día' : ' días');
        }
        if ($dias == 0) {
            return 'Vence hoy';
        }

        return 'Faltan ' . $dias . ($dias == 1 ? ' día' : ' días');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
}
