@php
    $campo = 'mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-[#006837] focus:ring-[#006837]';
@endphp

@if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        <p class="font-bold mb-1">Revisa los siguientes campos:</p>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">
    @if ($proyecto->exists)
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Código de radicado</label>
            <p class="mt-1 font-mono font-bold text-[#006837]">{{ $proyecto->codigo_radicado }}</p>
        </div>
    @endif

    <div>
        <label for="titulo" class="block text-sm font-semibold text-slate-700">Título del proyecto</label>
        <input type="text" name="titulo" id="titulo" maxlength="255" class="{{ $campo }}"
            value="{{ old('titulo', $proyecto->titulo) }}" required>
    </div>

    <div>
        <label for="asunto_linea_investigacion" class="block text-sm font-semibold text-slate-700">Línea de investigación</label>
        <input type="text" name="asunto_linea_investigacion" id="asunto_linea_investigacion" maxlength="255"
            list="lineas" class="{{ $campo }}"
            value="{{ old('asunto_linea_investigacion', $proyecto->asunto_linea_investigacion) }}" required>
        <datalist id="lineas">
            @foreach ($lineas as $linea)
                <option value="{{ $linea }}">
            @endforeach
        </datalist>
        <p class="text-xs text-slate-400 mt-1">Puedes escoger una línea ya registrada o escribir una nueva.</p>
    </div>

    <div>
        <label for="resumen" class="block text-sm font-semibold text-slate-700">Resumen</label>
        <textarea name="resumen" id="resumen" rows="5" class="{{ $campo }}" required>{{ old('resumen', $proyecto->resumen) }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="fecha_radicacion" class="block text-sm font-semibold text-slate-700">Fecha de radicación</label>
            <input type="date" name="fecha_radicacion" id="fecha_radicacion" class="{{ $campo }}"
                value="{{ old('fecha_radicacion', optional($proyecto->fecha_radicacion)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>

        @if ($proyecto->exists)
            <div>
                <label for="estado" class="block text-sm font-semibold text-slate-700">Estado</label>
                <select name="estado" id="estado" class="{{ $campo }}">
                    @foreach (App\Models\Proyecto::ESTADOS as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(old('estado', $proyecto->estado) == $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-8">
    <a href="{{ $proyecto->exists ? route('proyectos.show', $proyecto) : route('dashboard') }}"
        class="text-sm font-semibold text-slate-600 hover:text-slate-900 px-4 py-2">Cancelar</a>
    <button type="submit" class="bg-[#006837] hover:bg-[#00522b] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all">
        {{ $proyecto->exists ? 'Guardar cambios' : 'Radicar proyecto' }}
    </button>
</div>
