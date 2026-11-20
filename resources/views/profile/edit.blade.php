<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-xs font-bold text-[#006837] bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">Gestión de Cuenta</span>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight mt-1">
                    Perfil del Evaluador / Usuario
                </h2>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold py-2 px-4 rounded-xl transition-all shadow-sm">
                &larr; Volver al Panel
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Tarjeta de Información del Usuario -->
            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-200 p-8">
                <div class="flex items-center space-x-4 border-b border-slate-100 pb-6 mb-6">
                    <div class="h-16 w-16 rounded-full bg-[#006837] text-white flex items-center justify-center font-black text-2xl shadow-inner">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">{{ Auth::user()->name }}</h3>
                        <p class="text-sm text-slate-500">{{ Auth::user()->email }}</p>
                        <span class="inline-block mt-2 px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full">
                            Comité Académico - Unicórdoba
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-slate-400 font-bold text-xs uppercase">Rol del Sistema</p>
                        <p class="font-semibold text-slate-700 mt-1">Administrador / Evaluador de Grados</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-slate-400 font-bold text-xs uppercase">Institución</p>
                        <p class="font-semibold text-slate-700 mt-1">Universidad de Córdoba</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>