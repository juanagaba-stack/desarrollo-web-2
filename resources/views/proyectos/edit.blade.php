<x-app-layout>
    <x-slot name="header">
        <span class="text-xs font-bold text-[#006837] bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">Edición de expediente</span>
        <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight mt-1">Editar proyecto</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-200 p-8">
                <form method="POST" action="{{ route('proyectos.update', $proyecto) }}">
                    @csrf
                    @method('PUT')
                    @include('proyectos._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
