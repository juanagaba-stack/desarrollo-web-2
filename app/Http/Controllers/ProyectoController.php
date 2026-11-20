<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProyectoRequest;
use App\Models\Correccion;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::with(['usuarioCreador', 'correcciones'])->latest()->get();

        // observaciones pendientes que ya vencieron o vencen en los próximos 3 días
        $alertas = Correccion::with('proyecto')
            ->where('estado', 'PENDIENTE')
            ->whereDate('fecha_limite', '<=', today()->addDays(3))
            ->orderBy('fecha_limite')
            ->get();

        return view('dashboard', compact('proyectos', 'alertas'));
    }

    public function create()
    {
        return view('proyectos.create', [
            'proyecto' => new Proyecto(),
            'lineas' => $this->lineasExistentes(),
        ]);
    }

    public function store(ProyectoRequest $request)
    {
        $datos = $request->validated();
        $datos['codigo_radicado'] = Proyecto::siguienteCodigo();
        $datos['usuario_creador_id'] = $request->user()->id;

        $proyecto = Proyecto::create($datos);

        return redirect()->route('proyectos.show', $proyecto)
            ->with('mensaje', "Proyecto {$proyecto->codigo_radicado} radicado correctamente.");
    }

    public function show(Proyecto $proyecto)
    {
        $proyecto->load(['correcciones', 'integrantes.user']);

        // solo se ofrecen los usuarios que todavía no hacen parte del proyecto
        $usuarios = User::whereNotIn('id', $proyecto->integrantes->pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('proyectos.show', compact('proyecto', 'usuarios'));
    }

    public function edit(Proyecto $proyecto)
    {
        return view('proyectos.edit', [
            'proyecto' => $proyecto,
            'lineas' => $this->lineasExistentes(),
        ]);
    }

    public function update(ProyectoRequest $request, Proyecto $proyecto)
    {
        $proyecto->update($request->validated());

        return redirect()->route('proyectos.show', $proyecto)
            ->with('mensaje', 'Los datos del proyecto se actualizaron.');
    }

    // lo usa el botón "Aprobar proyecto" de la vista de detalle (fetch)
    public function cambiarEstado(Request $request, Proyecto $proyecto)
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(array_keys(Proyecto::ESTADOS))],
        ]);

        $proyecto->update($datos);
        session()->flash('mensaje', 'El estado del proyecto cambió a ' . Proyecto::ESTADOS[$proyecto->estado] . '.');

        return response()->json(['estado' => $proyecto->estado]);
    }

    public function destroy(Proyecto $proyecto)
    {
        $codigo = $proyecto->codigo_radicado;
        $proyecto->delete();

        return redirect()->route('dashboard')
            ->with('mensaje', "El proyecto {$codigo} fue eliminado.");
    }

    private function lineasExistentes()
    {
        return Proyecto::select('asunto_linea_investigacion')
            ->distinct()
            ->orderBy('asunto_linea_investigacion')
            ->pluck('asunto_linea_investigacion');
    }
}
