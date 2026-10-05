<form method="post" action="{{ route('password.update') }}" class="space-y-4"
      x-data="{
          password: '',
          showCurrent: false,
          showPassword: false,
          showConfirmation: false,
          get score() {
              const v = this.password || '';
              if (! v) return 0;
              let s = 0;
              if (v.length >= 8) s++;
              if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
              if (/\d/.test(v) || /[^A-Za-z0-9]/.test(v)) s++;
              return s;
          },
          get strengthLabel() { return ['', 'Débil', 'Media', 'Fuerte'][this.score] || ''; },
          get strengthBar() { return ['bg-slate-200', 'bg-rose-500', 'bg-amber-500', 'bg-emerald-500'][this.score] || 'bg-slate-200'; },
          get strengthText() { return ['text-slate-400', 'text-rose-500', 'text-amber-600', 'text-emerald-600'][this.score] || 'text-slate-400'; }
      }">
    @csrf
    @method('put')

    <!-- Contraseña actual -->
    <div class="space-y-1.5">
        <x-input-label for="update_password_current_password" value="Contraseña actual" />
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon name="key" class="h-4 w-4" />
            </span>
            <input id="update_password_current_password" name="current_password"
                   :type="showCurrent ? 'text' : 'password'" autocomplete="current-password"
                   placeholder="••••••••••••"
                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-11 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
            <button type="button" @click="showCurrent = ! showCurrent"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-700" title="Mostrar u ocultar">
                <x-icon name="eye" class="h-4 w-4" x-show="! showCurrent" />
                <x-icon name="eye-off" class="h-4 w-4" x-show="showCurrent" x-cloak />
            </button>
        </div>
        <x-input-error :messages="$errors->updatePassword->get('current_password')" />
    </div>

    <!-- Nueva contraseña -->
    <div class="space-y-1.5">
        <x-input-label for="update_password_password" value="Nueva contraseña" />
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon name="lock" class="h-4 w-4" />
            </span>
            <input id="update_password_password" name="password" x-model="password"
                   :type="showPassword ? 'text' : 'password'" autocomplete="new-password"
                   placeholder="Mínimo 8 caracteres"
                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-11 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
            <button type="button" @click="showPassword = ! showPassword"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-700" title="Mostrar u ocultar">
                <x-icon name="eye" class="h-4 w-4" x-show="! showPassword" />
                <x-icon name="eye-off" class="h-4 w-4" x-show="showPassword" x-cloak />
            </button>
        </div>

        <!-- Fortaleza -->
        <div class="space-y-1 pt-0.5" x-show="password.length > 0" x-cloak>
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Seguridad de la clave</span>
                <span class="font-bold" :class="strengthText" x-text="strengthLabel"></span>
            </div>
            <div class="grid h-1.5 grid-cols-3 gap-1.5">
                <div class="rounded-full" :class="score >= 1 ? strengthBar : 'bg-slate-200'"></div>
                <div class="rounded-full" :class="score >= 2 ? strengthBar : 'bg-slate-200'"></div>
                <div class="rounded-full" :class="score >= 3 ? strengthBar : 'bg-slate-200'"></div>
            </div>
        </div>

        <x-input-error :messages="$errors->updatePassword->get('password')" />
    </div>

    <!-- Confirmar -->
    <div class="space-y-1.5">
        <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contraseña" />
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon name="lock" class="h-4 w-4" />
            </span>
            <input id="update_password_password_confirmation" name="password_confirmation"
                   :type="showConfirmation ? 'text' : 'password'" autocomplete="new-password"
                   placeholder="Repite tu nueva contraseña"
                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-11 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
            <button type="button" @click="showConfirmation = ! showConfirmation"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-700" title="Mostrar u ocultar">
                <x-icon name="eye" class="h-4 w-4" x-show="! showConfirmation" />
                <x-icon name="eye-off" class="h-4 w-4" x-show="showConfirmation" x-cloak />
            </button>
        </div>
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
    </div>

    <div class="flex justify-end pt-1">
        <button type="submit"
                class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-slate-100 px-6 text-sm font-bold uppercase tracking-wide text-slate-700 transition hover:bg-slate-200 active:scale-[0.985] sm:w-auto">
            <x-icon name="lock" class="h-5 w-5 text-amber-500" /> Actualizar contraseña
        </button>
    </div>
</form>
