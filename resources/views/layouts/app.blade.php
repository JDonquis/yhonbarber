<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <style>[x-cloak] { display: none !important; }</style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-800">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            @include('layouts.navigation')

            <div class="flex-1 min-w-0 flex flex-col">
                <header class="sticky top-0 z-30 h-16 bg-white border-b border-slate-200 flex items-center justify-between gap-4 px-4 sm:px-6">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="sidebarOpen = true" class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                            </svg>
                        </button>
                        <div class="truncate">
                            {{ $header ?? '' }}
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-2 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1.5 text-sm font-medium">
                            <x-icon name="dollar" class="h-4 w-4" />
                            <span>1 $ = {{ ves($currentRate) }}</span>
                        </div>
                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('exchange-rate.refresh') }}">
                                @csrf
                                <button type="submit" title="Actualizar tasa del dólar"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100">
                                    <x-icon name="refresh" class="h-4 w-4" />
                                </button>
                            </form>
                        @endif
                        <div class="hidden md:block text-right leading-tight">
                            <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Barbero' }}</p>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    @if (session('status'))
                        <div class="mb-5 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <x-icon name="check" class="h-5 w-5 shrink-0" />
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-5 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <x-icon name="alert" class="h-5 w-5 shrink-0" />
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <p class="font-medium">Revisa los siguientes errores:</p>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
