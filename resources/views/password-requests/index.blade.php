<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Solicitudes de contraseña</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-5">
        @if (session('generated_password'))
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4">
                <p class="text-sm font-semibold text-amber-900">Nueva contraseña temporal</p>
                <p class="mt-1 text-sm text-amber-800">
                    Usuario: <span class="font-medium">{{ session('generated_for') }}</span>
                </p>
                <p class="mt-2 text-sm text-amber-800">
                    Contraseña:
                    <span class="ml-1 select-all rounded-md bg-white px-2 py-1 font-mono text-base font-bold tracking-wider text-slate-900">{{ session('generated_password') }}</span>
                </p>
                <p class="mt-2 text-xs text-amber-700">Por seguridad no se volverá a mostrar. El usuario puede cambiarla en "Mi perfil".</p>
            </div>
        @endif

        <x-card title="Pendientes" description="Usuarios que olvidaron su contraseña y no pueden recibir correo">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Usuario</th>
                            <th class="px-5 py-3 font-medium">Correo</th>
                            <th class="px-5 py-3 font-medium">Solicitado</th>
                            <th class="px-5 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $resetRequest)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $resetRequest->user?->name ?? 'Usuario eliminado' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $resetRequest->user?->email ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $resetRequest->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('password-requests.resolve', $resetRequest) }}"
                                              onsubmit="return confirm('¿Restablecer la contraseña de {{ $resetRequest->user?->name }}?');">
                                            @csrf
                                            <button class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-slate-900 hover:bg-amber-400">
                                                <x-icon name="key" class="h-4 w-4" /> Restablecer
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('password-requests.destroy', $resetRequest) }}"
                                              onsubmit="return confirm('¿Descartar esta solicitud?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100" title="Descartar">
                                                <x-icon name="x" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">No hay solicitudes pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $requests->links() }}</div>
            @endif
        </x-card>

        @if ($resolved->isNotEmpty())
            <x-card title="Recientes" description="Últimas solicitudes atendidas">
                <ul class="divide-y divide-slate-100">
                    @foreach ($resolved as $item)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                            <div>
                                <p class="font-medium text-slate-700">{{ $item->user?->name ?? 'Usuario eliminado' }}</p>
                                <p class="text-xs text-slate-400">
                                    Atendida por {{ $item->resolvedBy?->name ?? '—' }}
                                    @if ($item->resolved_at) · {{ $item->resolved_at->diffForHumans() }} @endif
                                </p>
                            </div>
                            <x-badge tone="green">Resuelta</x-badge>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    </div>
</x-app-layout>
