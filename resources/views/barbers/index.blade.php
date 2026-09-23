<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Barberos</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <x-card title="Equipo" description="Barberos con acceso al sistema">
            <x-slot name="actions">
                <a href="{{ route('barbers.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                    <x-icon name="plus" class="h-4 w-4" /> Nuevo barbero
                </a>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Nombre</th>
                            <th class="px-5 py-3 font-medium">Correo</th>
                            <th class="px-5 py-3 font-medium">Teléfono</th>
                            <th class="px-5 py-3 font-medium text-center">Estado</th>
                            <th class="px-5 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($barbers as $barber)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $barber->name }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $barber->email }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $barber->phone ?: '—' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :tone="$barber->active ? 'green' : 'red'">{{ $barber->active ? 'Activo' : 'Inactivo' }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('barbers.edit', $barber) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Editar">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('barbers.destroy', $barber) }}" onsubmit="return confirm('¿Eliminar este barbero?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Eliminar">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay barberos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-5">{{ $barbers->links() }}</div>
    </div>
</x-app-layout>
