@php($user = $user ?? auth()->user())

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <!-- Nombre -->
    <div class="space-y-1.5">
        <x-input-label for="name" value="Nombre completo" />
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon name="user" class="h-4 w-4" />
            </span>
            <input id="name" name="name" type="text" required autocomplete="name"
                   value="{{ old('name', $user->name) }}"
                   placeholder="Tu nombre y apellido"
                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
        </div>
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <!-- Correo -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between">
            <x-input-label for="email" value="Correo electrónico" />
            <span class="flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                <x-icon name="check" class="h-3.5 w-3.5" /> Verificado
            </span>
        </div>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon name="mail" class="h-4 w-4" />
            </span>
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email', $user->email) }}"
                   placeholder="ejemplo@estudio.com"
                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
            <x-icon name="check" class="pointer-events-none absolute inset-y-0 right-3.5 my-auto h-4 w-4 text-emerald-500" />
        </div>
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-sm text-slate-600">
                    Tu correo no está verificado.
                    <button form="send-verification" class="rounded-md font-medium text-amber-600 underline hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        Reenviar correo de verificación
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-emerald-600">Se envió un nuevo enlace de verificación a tu correo.</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Teléfono -->
    <div class="space-y-1.5">
        <x-input-label for="phone" value="Teléfono / WhatsApp" />
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon name="phone" class="h-4 w-4" />
            </span>
            <input id="phone" name="phone" type="tel" autocomplete="tel"
                   value="{{ old('phone', $user->phone) }}"
                   placeholder="+58 000 000 0000"
                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
        </div>
        <x-input-error :messages="$errors->get('phone')" />
    </div>

    <div class="flex justify-end pt-1">
        <button type="submit"
                class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 px-6 text-sm font-bold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/25 transition-all hover:opacity-95 active:scale-[0.985] sm:w-auto">
            <x-icon name="check" class="h-5 w-5" /> Guardar cambios
        </button>
    </div>
</form>
