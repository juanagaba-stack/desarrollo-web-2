<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGTG - Unicórdoba') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="bg-slate-50 text-slate-800 antialiased">
        <div class="min-h-screen flex flex-col">
            
            <nav class="bg-[#006837] border-b border-[#00522b] text-white shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center space-x-3">
                            <span class="bg-white text-[#006837] font-black text-xs px-2.5 py-1.5 rounded-md tracking-wider shadow-sm">UNICÓRDOBA</span>
                            <div>
                                <span class="font-extrabold text-base tracking-tight text-white block leading-tight">SGTG</span>
                                <span class="text-[10px] text-emerald-100 tracking-wider uppercase font-medium">Trabajos de Grado</span>
                            </div>
                        </div>

                        <!-- Menú de Usuario -->
                        <div class="flex items-center space-x-4">
                            <div class="text-right hidden sm:block">
                                <p class="text-[10px] font-bold text-emerald-200 uppercase tracking-widest">Comité Evaluador</p>
                                <p class="text-xs font-medium text-white">{{ Auth::user()->name ?? 'Usuario' }}</p>
                            </div>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bg-[#00522b] hover:bg-red-700 text-white px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border border-emerald-800 shadow-sm">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white shadow-sm border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            @if (session('mensaje'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full">
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium px-4 py-3 rounded-xl">
                        {{ session('mensaje') }}
                    </div>
                </div>
            @endif

            <main class="flex-grow">
                {{ $slot }}
            </main>

            <footer class="bg-[#004d26] text-emerald-100 py-6 text-center text-xs border-t border-[#00381b] mt-auto">
                <p class="font-medium">Universidad de Córdoba &mdash; Sistema de Gestión y Seguimiento de Trabajos de Grado (SGTG)</p>
                <p class="text-emerald-300 mt-1">Facultad de Ingeniería &bull; Ingeniería de Sistemas</p>
            </footer>
        </div>
    </body>
</html>