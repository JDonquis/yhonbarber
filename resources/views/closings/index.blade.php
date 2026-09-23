<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Cierres y reportes</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <x-card title="Generar reporte" description="Calcula los totales de un día, semana o mes">
            <form method="POST" action="{{ route('closings.generate') }}" class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <x-input-label for="period_type" value="Período" />
                    <select id="period_type" name="period_type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="diario">Diario</option>
                        <option value="semanal">Semanal</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="date" value="Fecha de referencia" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', now()->toDateString())" required />
                </div>
                <div>
                    <x-primary-button class="w-full justify-center">Generar / ver reporte</x-primary-button>
                </div>
            </form>
        </x-card>

        <x-card title="Cierres registrados">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Período</th>
                            <th class="px-5 py-3 font-medium">Rango</th>
                            <th class="px-5 py-3 font-medium text-right">Servicios</th>
                            <th class="px-5 py-3 font-medium text-right">Productos</th>
                            <th class="px-5 py-3 font-medium text-right">Total USD</th>
                            <th class="px-5 py-3 font-medium text-right">Comisión</th>
                            <th class="px-5 py-3 font-medium text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($closings as $closing)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('closings.show', $closing) }}" class="font-medium text-amber-600 hover:text-amber-700">{{ ucfirst($closing->period_type) }}</a>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $closing->period_start->format('d/m/Y') }} — {{ $closing->period_end->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right text-slate-500">{{ usd($closing->total_services_usd) }}</td>
                                <td class="px-5 py-3 text-right text-slate-500">{{ usd($closing->total_products_usd) }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ usd($closing->total_usd) }}</td>
                                <td class="px-5 py-3 text-right text-slate-500">{{ usd($closing->barber_commission_usd) }}</td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :tone="$closing->isClosed() ? 'red' : 'amber'">{{ $closing->isClosed() ? 'Cerrado' : 'Abierto' }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No hay cierres generados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4">{{ $closings->links() }}</div>
        </x-card>
    </div>
</x-app-layout>
