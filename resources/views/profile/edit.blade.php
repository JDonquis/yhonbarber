<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Mi perfil</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-card title="Datos de la cuenta" description="Actualiza tu nombre, correo y teléfono de contacto">
            <div class="p-5 sm:p-6">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-card>

        <x-card id="seguridad" title="Seguridad" description="Cambia la contraseña de acceso a tu cuenta">
            <div class="p-5 sm:p-6">
                @include('profile.partials.update-password-form')
            </div>
        </x-card>

        @include('profile.partials.delete-user-form')
    </div>

    <script>
        (function () {
            var key = 'scroll:' + window.location.pathname;
            var saved = sessionStorage.getItem(key);

            if (saved === null) {
                document.addEventListener('submit', function () {
                    sessionStorage.setItem(key, window.scrollY);
                });
                return;
            }

            sessionStorage.removeItem(key);
            var y = parseInt(saved, 10) || 0;
            var restore = function () { window.scrollTo(0, y); };
            document.addEventListener('DOMContentLoaded', restore);
            window.addEventListener('load', restore);
        })();
    </script>
</x-app-layout>
