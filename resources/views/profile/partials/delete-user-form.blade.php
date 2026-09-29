<x-card class="border-red-200">
    <div class="p-5 sm:p-6">
        <h3 class="text-base font-semibold text-red-700">Eliminar cuenta</h3>
        <p class="mt-1 text-sm text-slate-500">
            Una vez eliminada tu cuenta, todos los datos asociados se borrarán de forma permanente.
            Antes de continuar, descarga cualquier información que quieras conservar.
        </p>

        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="mt-4"
        >Eliminar mi cuenta</x-danger-button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h3 class="text-lg font-semibold text-slate-800">
                ¿Seguro que quieres eliminar tu cuenta?
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Esta acción es permanente. Ingresa tu contraseña para confirmar que deseas eliminar tu cuenta.
            </p>

            <div class="mt-6">
                <x-input-label for="delete_password" value="Contraseña" class="sr-only" />
                <x-text-input
                    id="delete_password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Contraseña"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button>
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</x-card>
