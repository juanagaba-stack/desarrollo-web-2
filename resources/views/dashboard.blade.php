<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-bold text-[#006837] bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">Módulo Analítico y Académico</span>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight mt-1">
                    Panel de Control General
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('proyectos.create') }}" class="text-xs font-semibold bg-[#006837] hover:bg-[#00522b] text-white px-3.5 py-2 rounded-xl shadow-sm transition-all">
                    + Nuevo proyecto
                </a>
                <a href="{{ route('profile.edit') }}" class="text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 px-3.5 py-2 rounded-xl shadow-sm border border-slate-200 transition-all">
                    Mi Perfil
                </a>
                <div class="bg-[#006837] text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm border border-emerald-800">
                    Periodo Académico: <span class="text-emerald-200 font-bold">2026-I</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"
            x-data="listado(@js($proyectos->map(fn ($p) => ['id' => $p->id, 'estado' => $p->estado, 'texto' => mb_strtolower($p->codigo_radicado . ' ' . $p->titulo . ' ' . $p->asunto_linea_investigacion)])))">

            <!-- Alertas de plazos -->
            @if ($alertas->isNotEmpty())
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-red-200">
                    <h3 class="text-sm font-bold text-red-700 mb-3">
                        Observaciones que requieren atención ({{ $alertas->count() }})
                    </h3>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($alertas as $alerta)
                            <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <a href="{{ route('proyectos.show', $alerta->proyecto_id) }}" class="font-mono font-bold text-[#006837] hover:underline">{{ $alerta->proyecto->codigo_radicado }}</a>
                                    <span class="text-slate-600 ml-2">{{ Str::limit($alerta->descripcion_observacion, 70) }}</span>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-md {{ $alerta->semaforo == 'rojo' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $alerta->texto_plazo }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Tarjetas de Métricas Dinámicas (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-[#006837]">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Radicados</p>
                    <p class="text-3xl font-black text-slate-800 mt-1">{{ $proyectos->count() }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-amber-500">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Con Observaciones</p>
                    <p class="text-3xl font-black text-amber-600 mt-1">{{ $proyectos->where('estado', 'CON_OBSERVACIONES')->count() }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-600">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aprobados</p>
                    <p class="text-3xl font-black text-emerald-600 mt-1">{{ $proyectos->where('estado', 'APROBADO')->count() }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-sky-600">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">En Revisión</p>
                    <p class="text-3xl font-black text-sky-600 mt-1">{{ $proyectos->where('estado', 'EN_REVISION')->count() }}</p>
                </div>
            </div>

            <!-- 2. Sección Visual: Gráficos Estadísticos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gráfico de Dona: Estados -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-slate-800">Distribución por Estado de Evaluación</h3>
                        <p class="text-xs text-slate-500">Proporción actual de anteproyectos según dictamen del comité.</p>
                    </div>
                    <div class="relative h-64 flex justify-center">
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Barras: Líneas de Investigación -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-slate-800">Proyectos por Línea de Investigación</h3>
                        <p class="text-xs text-slate-500">Demanda académica por área temática en Ingeniería de Sistemas.</p>
                    </div>
                    <div class="relative h-64">
                        <canvas id="chartLineas"></canvas>
                    </div>
                </div>
            </div>

            <!-- 3. Tabla Principal de Proyectos -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-6 bg-slate-50 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Proyectos de Grado Radicados</h3>
                        <p class="text-xs text-slate-500">
                            Mostrando <span x-text="visibles().length"></span> de {{ $proyectos->count() }} proyectos.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="search" x-model="busqueda" placeholder="Buscar por código, título o línea..."
                            class="w-full sm:w-72 rounded-xl border-slate-300 text-sm focus:border-[#006837] focus:ring-[#006837]">
                        <select x-model="estado" class="rounded-xl border-slate-300 text-sm focus:border-[#006837] focus:ring-[#006837]">
                            <option value="">Todos los estados</option>
                            @foreach (App\Models\Proyecto::ESTADOS as $valor => $etiqueta)
                                <option value="{{ $valor }}">{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                <th class="px-6 py-3">Radicado</th>
                                <th class="px-6 py-3">Título de Investigación</th>
                                <th class="px-6 py-3">Línea</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Avance</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white text-sm">
                            @forelse ($proyectos as $proyecto)
                                <tr class="hover:bg-slate-50/80 transition-colors" x-show="visibles().includes({{ $proyecto->id }})">
                                    <td class="px-6 py-4 font-mono font-bold text-[#006837]">{{ $proyecto->codigo_radicado }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-900 max-w-xs truncate">{{ $proyecto->titulo }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs">{{ $proyecto->asunto_linea_investigacion }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-extrabold rounded-full 
                                            {{ $proyecto->estado == 'APROBADO' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : ($proyecto->estado == 'CON_OBSERVACIONES' ? 'bg-amber-100 text-amber-700 border border-amber-200' : ($proyecto->estado == 'RECHAZADO' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-sky-100 text-sky-700 border border-sky-200')) }}">
                                            {{ str_replace('_', ' ', $proyecto->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 w-40">
                                        @php
                                            $colorBarra = ['APROBADO' => 'bg-emerald-500', 'RECHAZADO' => 'bg-red-500', 'CON_OBSERVACIONES' => 'bg-amber-500', 'EN_REVISION' => 'bg-sky-500'][$proyecto->estado];
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $colorBarra }}" style="width: {{ $proyecto->porcentaje_avance }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-600 w-9 text-right">{{ $proyecto->porcentaje_avance }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('proyectos.show', $proyecto) }}" class="inline-flex items-center px-3 py-1.5 bg-[#006837] hover:bg-[#00522b] text-white text-xs font-semibold rounded-lg transition-all shadow-sm">
                                                Ver
                                            </a>
                                            <a href="{{ route('proyectos.edit', $proyecto) }}" class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 transition-all">
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('proyectos.destroy', $proyecto) }}"
                                                onsubmit="return confirm('¿Eliminar el proyecto {{ $proyecto->codigo_radicado }}? Se borrarán también sus observaciones e integrantes.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-red-50 text-red-600 text-xs font-semibold rounded-lg border border-red-200 transition-all">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400">No hay proyectos registrados en el sistema.</td>
                                </tr>
                            @endforelse
                            @if ($proyectos->isNotEmpty())
                                <tr x-show="visibles().length === 0" x-cloak>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400">Ningún proyecto coincide con la búsqueda.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('listado', (proyectos) => ({
                proyectos,
                busqueda: '',
                estado: '',

                // ids de los proyectos que cumplen el texto y el estado elegidos
                visibles() {
                    const texto = this.busqueda.trim().toLowerCase();
                    return this.proyectos
                        .filter(p => (this.estado === '' || p.estado === this.estado) && p.texto.includes(texto))
                        .map(p => p.id);
                },
            }));
        });
    </script>

    <!-- Script de Chart.js conectado directamente a los datos de Laravel -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Datos dinámicos desde Blade/MySQL para Estados
            const aprobados = {{ $proyectos->where('estado', 'APROBADO')->count() }};
            const observaciones = {{ $proyectos->where('estado', 'CON_OBSERVACIONES')->count() }};
            const revision = {{ $proyectos->where('estado', 'EN_REVISION')->count() }};
            const rechazados = {{ $proyectos->where('estado', 'RECHAZADO')->count() }};

            new Chart(document.getElementById('chartEstados'), {
                type: 'doughnut',
                data: {
                    labels: ['Aprobados', 'Con Observaciones', 'En Revisión', 'Rechazados'],
                    datasets: [{
                        data: [aprobados, observaciones, revision, rechazados],
                        backgroundColor: ['#059669', '#f59e0b', '#0284c7', '#dc2626'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { family: 'Inter', size: 12, weight: '600' }, padding: 16 }
                        }
                    }
                }
            });

            // 2. Agrupación dinámica por Línea de Investigación
            @php
                $lineasAgrupadas = $proyectos->groupBy('asunto_linea_investigacion')->map->count();
            @endphp
            const labelsLineas = {!! json_encode($lineasAgrupadas->keys()) !!};
            const dataLineas = {!! json_encode($lineasAgrupadas->values()) !!};

            new Chart(document.getElementById('chartLineas'), {
                type: 'bar',
                data: {
                    labels: labelsLineas.map(l => l.length > 22 ? l.substring(0, 22) + '...' : l),
                    datasets: [{
                        label: 'Cantidad de Proyectos',
                        data: dataLineas,
                        backgroundColor: '#006837',
                        borderRadius: 8,
                        barThickness: 36
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { family: 'Inter' } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { family: 'Inter', size: 11 } },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>