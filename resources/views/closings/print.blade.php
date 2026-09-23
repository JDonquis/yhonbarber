<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cierre {{ ucfirst($closing->period_type) }} · {{ $shopName }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, Arial, sans-serif; color: #0f172a; margin: 0; padding: 24px; font-size: 13px; }
        h1 { font-size: 20px; margin: 0; }
        h2 { font-size: 14px; margin: 24px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .muted { color: #64748b; }
        .row { display: flex; justify-content: space-between; padding: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { text-align: left; padding: 5px 6px; border-bottom: 1px solid #e2e8f0; }
        th { font-size: 11px; text-transform: uppercase; color: #64748b; }
        .text-right { text-align: right; }
        .totals { margin-top: 12px; font-size: 14px; }
        .totals .row.big { font-size: 16px; font-weight: 700; border-top: 2px solid #0f172a; padding-top: 6px; margin-top: 6px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
        .no-print { margin-bottom: 16px; }
        button { padding: 8px 16px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $shopName }}</h1>
            <p class="muted">
                @if (setting('shop_rif')) RIF: {{ setting('shop_rif') }}<br>@endif
                @if (setting('shop_phone')) Tel: {{ setting('shop_phone') }}<br>@endif
                @if (setting('shop_address')) {{ setting('shop_address') }}@endif
            </p>
        </div>
        <div class="text-right">
            <h1>Cierre {{ ucfirst($closing->period_type) }}</h1>
            <p class="muted">
                {{ $closing->period_start->format('d/m/Y') }} — {{ $closing->period_end->format('d/m/Y') }}<br>
                Emitido: {{ now()->format('d/m/Y h:i a') }}<br>
                Estado: {{ $closing->isClosed() ? 'CERRADO' : 'ABIERTO' }}
            </p>
        </div>
    </div>

    <div class="grid">
        <div>
            <h2>Resumen</h2>
            <div class="row"><span>Servicios</span><span>{{ usd($closing->total_services_usd) }}</span></div>
            <div class="row"><span>Productos</span><span>{{ usd($closing->total_products_usd) }}</span></div>
            <div class="row"><span>Tickets</span><span>{{ $closing->ticket_count }}</span></div>
            <div class="row"><span>Tasa aplicada</span><span>Bs. {{ number_format($closing->exchange_rate, 2, ',', '.') }}</span></div>
            <div class="totals">
                <div class="row big"><span>Total USD</span><span>{{ usd($closing->total_usd) }}</span></div>
                <div class="row big"><span>Total Bs</span><span>{{ ves($closing->total_ves) }}</span></div>
            </div>
            <div class="row" style="margin-top:8px"><span>Comisión barberos</span><span>{{ usd($closing->barber_commission_usd) }}</span></div>
            <div class="row"><span>Monto tienda</span><span>{{ usd($closing->shop_amount_usd) }}</span></div>
        </div>

        <div>
            <h2>Por barbero</h2>
            <table>
                <thead><tr><th>Barbero</th><th class="text-right">Tickets</th><th class="text-right">Comisión</th></tr></thead>
                <tbody>
                    @foreach ($closing->details['barberos'] ?? [] as $row)
                        <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['tickets'] }}</td><td class="text-right">{{ usd($row['commission_usd']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            <h2>Métodos de pago</h2>
            <table>
                <thead><tr><th>Método</th><th class="text-right">Tickets</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                    @foreach ($closing->details['metodos_pago'] ?? [] as $row)
                        <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['count'] }}</td><td class="text-right">{{ usd($row['total_usd']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid">
        <div>
            <h2>Servicios vendidos</h2>
            <table>
                <thead><tr><th>Servicio</th><th class="text-right">Cant.</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                    @foreach ($closing->details['servicios'] ?? [] as $row)
                        <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['quantity'] }}</td><td class="text-right">{{ usd($row['total_usd']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <h2>Productos vendidos</h2>
            <table>
                <thead><tr><th>Producto</th><th class="text-right">Cant.</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                    @foreach ($closing->details['productos'] ?? [] as $row)
                        <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['quantity'] }}</td><td class="text-right">{{ usd($row['total_usd']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($closing->notes)
        <h2>Notas</h2>
        <p>{{ $closing->notes }}</p>
    @endif

    @if ($closing->closed_at)
        <p class="muted" style="margin-top:32px">
            Cerrado por {{ $closing->closedBy->name ?? '—' }} el {{ $closing->closed_at->format('d/m/Y h:i a') }}.
        </p>
    @endif

    @if ($closing->reopened_at)
        <p class="muted">
            Reabierto por {{ $closing->reopenedBy->name ?? '—' }} el {{ $closing->reopened_at->format('d/m/Y h:i a') }}.
        </p>
    @endif
</body>
</html>
