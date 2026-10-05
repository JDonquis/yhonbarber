<!DOCTYPE html>
<html class="h-full bg-[#0b1326]" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ __('Recuperar contraseña') }} | {{ config('app.name', 'MR Yhon Barber Studio') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa')

    <style data-purpose="ambient-lighting">
        .glow-amber-radial {
            background: radial-gradient(circle at 50% 15%, rgba(245, 158, 11, 0.18) 0%, rgba(11, 19, 38, 0) 70%);
        }
    </style>
</head>

<body
    class="h-full bg-[#0b1326] text-[#f8fafc] font-sans antialiased selection:bg-amber-500/30 selection:text-amber-200"
    style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">
    <main
        class="min-h-full flex flex-col gap-6 relative overflow-hidden px-4 py-10 sm:px-6 glow-amber-radial max-w-[430px] mx-auto"
        data-purpose="mobile-viewport-wrapper">
        <div aria-hidden="true"
            class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl"></div>

        <header class="w-full pt-4 flex flex-col items-center text-center relative z-10">
            <div class="mb-3 flex items-center justify-center p-1 rounded-2xl bg-gradient-to-b from-amber-500/40 via-[#1e293b] to-[#0b1326]"
                style="box-shadow: 0 0 25px -5px rgba(245, 158, 11, 0.35);">
                <div class="w-16 h-16 bg-[#0f172a] rounded-[14px] flex items-center justify-center overflow-hidden p-1.5 border border-amber-500/30">
                    <img alt="MR Yhon Barber Studio Logo" class="w-full h-full object-contain" height="64"
                        src="{{ asset('logo.jpeg') }}" width="64" />
                </div>
            </div>
            <h1 class="text-lg font-extrabold tracking-tight text-[#f8fafc]">
                MR YHON <span class="text-amber-400 font-black">BARBER STUDIO</span>
            </h1>
            <p class="text-xs text-[#94a3b8] mt-1 font-medium tracking-wide">Recuperar acceso a tu cuenta</p>
        </header>

        <section class="w-full z-10">
            <div class="bg-[#131b2e]/90 backdrop-blur-md border border-slate-700/60 rounded-3xl p-5 shadow-2xl shadow-black/60">
                <p class="text-sm text-slate-300">
                    Ingresa tu correo y registraremos una solicitud. Un administrador te asignará una nueva
                    contraseña y te la entregará personalmente.
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-2.5 text-xs text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300" for="email">
                            Correo Electrónico
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5 text-amber-500/70" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.161V6a2 2 0 00-2-2H3z"></path>
                                    <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"></path>
                                </svg>
                            </div>
                            <input autocomplete="email"
                                class="block w-full pl-10 pr-3.5 py-3.5 text-sm rounded-xl bg-[#1e293b]/90 border border-slate-700 text-[#f8fafc] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors @error('email') border-red-500/70 @enderror"
                                id="email" name="email" placeholder="barber@mryhonbarber.com" required autofocus
                                value="{{ old('email') }}" type="email" />
                        </div>
                        @error('email')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        class="w-full min-h-[50px] py-3.5 px-4 flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 via-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 active:scale-[0.99] text-slate-950 font-black text-sm tracking-wider uppercase transition-all"
                        style="box-shadow: 0 8px 24px -4px rgba(245, 158, 11, 0.45);" type="submit">
                        <span>Solicitar nueva contraseña</span>
                    </button>
                </form>

                <div class="mt-5 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300 transition-colors">
                        Volver a iniciar sesión
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
