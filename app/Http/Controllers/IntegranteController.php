<?php

namespace App\Http\Controllers;

use App\Models\IntegranteProyecto;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IntegranteController extends Controller
{
    public function store(Request $request, Proyecto $proyecto)
    {
        $datos = $request->validateWithBag('integrante', [
            'user_id' => [
                'required',
                'exists:users,id',
                // un usuario no puede estar dos veces en el mismo proyecto
                Rule::unique('integrante_proyectos')->where('proyecto_id', $proyecto->id),
            ],
            'rol_en_proyecto' => ['required', Rule::in(array_keys(IntegranteProyecto::ROLES))],
        ], [
            'user_id.required' => 'Selecciona un usuario.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'user_id.unique' => 'Ese usuario ya hace parte del proyecto.',
            'rol_en_proyecto.required' => 'Selecciona un rol.',
            'rol_en_proyecto.in' => 'El rol seleccionado no es válido.',
        ]);

        $proyecto->integrantes()->create($datos);

        return redirect()->route('proyectos.show', $proyecto)
            ->with('mensaje', 'Integrante agregado al proyecto.');
    }

    public function destroy(IntegranteProyecto $integrante)
    {
        $proyectoId = $integrante->proyecto_id;
        $integrante->delete();

        return redirect()->route('proyectos.show', $proyectoId)
            ->with('mensaje', 'Integrante retirado del proyecto.');
    }
}
