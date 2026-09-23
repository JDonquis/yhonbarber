<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Tipos de corte</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <x-card title="Catálogo de servicios" description="Ajusta el precio de cada tipo de corte">
            <x-slot name="actions">
                <a href="{{ route('services.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                    <x-icon name="plus" class="h-4 w-4" /> Nuevo corte
                </a>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Nombre</th>
                            <th class="px-5 py-3 font-medium">Descripción</th>
                            <th class="px-5 py-3 font-medium text-right">Precio</th>
                            <th class="px-5 py-3 font-medium text-center">Estado</th>
                            <th class="px-5 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($services as $service)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $service->name }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $service->description ?: '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-semibold text-slate-700">{{ usd($service->price) }}</span>
                                    <span class="block text-xs text-slate-400">{{ ves(to_ves($service->price)) }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :tone="$service->active ? 'green' : 'red'">{{ $service->active ? 'Activo' : 'Inactivo' }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('services.edit', $service) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Editar">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('¿Eliminar este tipo de corte?');">
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
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay tipos de corte registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-5">{{ $services->links() }}</div>
    </div>
</x-app-layout>
