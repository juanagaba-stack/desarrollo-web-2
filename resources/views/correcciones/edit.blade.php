<x-app-layout>
    <x-slot name="header">
        <span class="text-xs font-bold text-[#006837] bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">{{ $correccion->proyecto->codigo_radicado }}</span>
        <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight mt-1">Editar observación</h2>
    </x-slot>

    @php
        $campo = 'mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-[#006837] focus:ring-[#006837]';
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-200 p-8">

                @if ($errors->correccion->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->correccion->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('correcciones.update', $correccion) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="descripcion_observacion" class="block text-sm font-semibold text-slate-700">Observación</label>
                        <textarea name="descripcion_observacion" id="descripcion_observacion" rows="4" class="{{ $campo }}" required>{{ old('descripcion_observacion', $correccion->descripcion_observacion) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="fecha_limite" class="block text-sm font-semibold text-slate-700">Fecha límite</label>
                            <input type="date" name="fecha_limite" id="fecha_limite" class="{{ $campo }}"
                                value="{{ old('fecha_limite', $correccion->fecha_limite->format('Y-m-d')) }}" required>
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-slate-700">Estado</label>
                            <select name="estado" id="estado" class="{{ $campo }}">
                                @foreach (App\Models\Correccion::ESTADOS as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('estado', $correccion->estado) == $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <a href="{{ route('proyectos.show', $correccion->proyecto_id) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 px-4 py-2">Cancelar</a>
                        <button type="submit" class="bg-[#006837] hover:bg-[#00522b] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
