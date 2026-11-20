<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorreccionRequest;
use App\Models\Correccion;
use App\Models\Proyecto;

class CorreccionController extends Controller
{
    public function store(CorreccionRequest $request, Proyecto $proyecto)
    {
        $proyecto->correcciones()->create($request->validated());

        // si estaba en revisión, al tener una observación pasa a "con observaciones"
        $mensaje = 'Observación registrada.';
        if ($proyecto->estado == 'EN_REVISION') {
            $proyecto->update(['estado' => 'CON_OBSERVACIONES']);
            $mensaje .= ' El proyecto pasó a estado "Con observaciones".';
        }

        return redirect()->route('proyectos.show', $proyecto)->with('mensaje', $mensaje);
    }

    public function edit(Correccion $correccion)
    {
        return view('correcciones.edit', compact('correccion'));
    }

    public function update(CorreccionRequest $request, Correccion $correccion)
    {
        $correccion->update($request->validated());

        return redirect()->route('proyectos.show', $correccion->proyecto_id)
            ->with('mensaje', 'Observación actualizada.');
    }

    // marca como resuelta (o vuelve a pendiente) y devuelve el avance actualizado
    public function alternar(Correccion $correccion)
    {
        $correccion->update([
            'estado' => $correccion->estado == 'RESUELTO' ? 'PENDIENTE' : 'RESUELTO',
        ]);

        $proyecto = $correccion->proyecto->load('correcciones');

        return response()->json([
            'estado' => $correccion->estado,
            'porcentaje' => $proyecto->porcentaje_avance,
            'resueltas' => $proyecto->correcciones->where('estado', 'RESUELTO')->count(),
            'total' => $proyecto->correcciones->count(),
        ]);
    }

    public function destroy(Correccion $correccion)
    {
        $proyectoId = $correccion->proyecto_id;
        $correccion->delete();

        return redirect()->route('proyectos.show', $proyectoId)
            ->with('mensaje', 'Observación eliminada.');
    }
}
