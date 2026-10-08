<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cierre {{ ucfirst($closing->period_type) }} · {{ $shopName }}</title>
    <style>
        :root {
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --soft: #f8fafc;
            --accent: #b45309;
            --emerald: #047857;
        }

        * { box-sizing: border-box; }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background: #f1f5f9;
            margin: 0;
            padding: 24px 16px;
            font-size: 13px;
            line-height: 1.5;
        }

        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 32px;
        }

        .toolbar { max-width: 820px; margin: 0 auto 14px; display: flex; justify-content: flex-end; }

        button {
            padding: 9px 18px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        button:hover { background: var(--soft); }

        .muted { color: var(--muted); margin: 0; }

        /* Header */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--text);
        }

        .doc-header h1 { font-size: 20px; margin: 0 0 4px; letter-spacing: -0.01em; }

        .brand { display: flex; align-items: center; gap: 14px; }

        .brand-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--soft);
            padding: 4px;
            flex-shrink: 0;
        }

        .doc-meta { text-align: right; }

        .doc-meta .doc-type {
            display: inline-block;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .status {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border: 1px solid currentColor;
        }

        .status.closed { color: var(--emerald); }
        .status.open { color: var(--accent); }

        /* KPI cards */
        .kpis {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin: 22px 0;
        }

        .kpi {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
        }

        .kpi .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); }
        .kpi .value { font-size: 18px; font-weight: 700; margin-top: 4px; }
        .kpi .hint { font-size: 10px; color: var(--muted); margin-top: 2px; }

        .kpi.highlight { background: var(--soft); border-color: #cbd5e1; }
        .kpi.highlight .value { color: var(--emerald); }

        /* Sections */
        section { margin-top: 22px; }

        h2 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin: 0 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border);
        }

        .section-head { display: flex; justify-content: space-between; align-items: baseline; }
        .section-head span { font-size: 11px; color: var(--muted); }

        table { width: 100%; border-collapse: collapse; }

        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--border); }
        th { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); font-weight: 600; }
        td { font-size: 13px; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:nth-child(even) { background: var(--soft); }

        .text-right { text-align: right; }
        .strong { font-weight: 700; }
        .accent { color: var(--accent); font-weight: 700; }
        .emerald { color: var(--emerald); font-weight: 700; }

        .empty { color: var(--muted); text-align: center; padding: 16px; font-size: 12px; }

        /* Summary key/value */
        .kv td { border-bottom: 1px solid var(--border); }
        .kv tr:nth-child(even) { background: transparent; }

        .totals {
            margin-top: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
        }

        .totals .row:last-child { border-bottom: none; }
        .totals .row.main { background: var(--soft); }
        .totals .row .amount { font-weight: 700; }
        .totals .row .amount.big { font-size: 18px; color: var(--accent); }

        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

        /* Footer */
        .doc-footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            font-size: 11px;
            color: var(--muted);
        }

        @media (max-width: 640px) {
            .doc-header { flex-direction: column; }
            .doc-meta { text-align: left; }
            .kpis { grid-template-columns: 1fr 1fr; }
            .two-col { grid-template-columns: 1fr; }
        }

        @media print {
            body { background: #fff; padding: 0; font-size: 12px; }
            .sheet { border: none; border-radius: 0; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
            section { break-inside: avoid; }
            .kpi, .totals { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <div class="sheet">
        <!-- Encabezado -->
        <header class="doc-header">
            <div class="brand">
                <img src="{{ asset('logo.jpeg') }}" alt="{{ $shopName }}" class="brand-logo">
                <div>
                    <h1>{{ $shopName }}</h1>
                    <p class="muted">
                        @if (setting('shop_rif')) RIF: {{ setting('shop_rif') }}<br>@endif
                        @if (setting('shop_phone')) Tel: {{ setting('shop_phone') }}<br>@endif
                        @if (setting('shop_address')) {{ setting('shop_address') }}@endif
                    </p>
                </div>
            </div>
            <div class="doc-meta">
                <span class="doc-type">Cierre {{ ucfirst($closing->period_type) }}</span>
                <p class="muted">
                    {{ $closing->period_start->format('d/m/Y') }} — {{ $closing->period_end->format('d/m/Y') }}<br>
                    Emitido: {{ now()->format('d/m/Y h:i a') }}
                </p>
                <span class="status {{ $closing->isClosed() ? 'closed' : 'open' }}">
                    {{ $closing->isClosed() ? 'Cerrado' : 'Abierto' }}
                </span>
            </div>
        </header>

        @php
            $services = collect($closing->details['servicios'] ?? []);
            $products = collect($closing->details['productos'] ?? []);
            $expenses = collect($closing->details['gastos'] ?? []);
            $serviceQty = (int) $services->sum('quantity');
            $productQty = (int) $products->sum('quantity');
            $reference = $closing->total_ves_reference > 0
                ? $closing->total_ves_reference
                : to_ves($closing->total_usd, (float) $closing->exchange_rate);
        @endphp

        <!-- KPIs -->
        <div class="kpis">
            <div class="kpi">
                <div class="label">Servicios</div>
                <div class="value">{{ usd($closing->total_services_usd) }}</div>
                <div class="hint">{{ $serviceQty }} atenciones</div>
            </div>
            <div class="kpi">
                <div class="label">Productos</div>
                <div class="value">{{ usd($closing->total_products_usd) }}</div>
                <div class="hint">{{ $productQty }} artículos</div>
            </div>
            <div class="kpi">
                <div class="label">Comisiones</div>
                <div class="value">{{ usd($closing->barber_commission_usd) }}</div>
                <div class="hint">A liquidar staff</div>
            </div>
            <div class="kpi">
                <div class="label">Gastos</div>
                <div class="value">{{ usd($closing->total_expenses_usd) }}</div>
                <div class="hint">{{ $expenses->count() }} egresos</div>
            </div>
            <div class="kpi highlight">
                <div class="label">Monto tienda</div>
                <div class="value">{{ usd($closing->shop_amount_usd) }}</div>
                <div class="hint">Ventas − comisiones − gastos</div>
            </div>
        </div>

        <!-- Resumen del período -->
        <section>
            <div class="section-head">
                <h2>Resumen del período</h2>
                <span>{{ ucfirst($closing->period_type) }}</span>
            </div>
            <table class="kv">
                <tbody>
                    <tr>
                        <td class="muted">Rango de fechas</td>
                        <td class="text-right strong">{{ $closing->period_start->format('d/m/Y') }} — {{ $closing->period_end->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Total operaciones</td>
                        <td class="text-right strong">{{ $closing->ticket_count }} tickets</td>
                    </tr>
                    <tr>
                        <td class="muted">Tasa promedio del período</td>
                        <td class="text-right strong">Bs. {{ number_format($closing->average_rate ?: $closing->exchange_rate, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Tasa de cierre</td>
                        <td class="text-right strong">Bs. {{ number_format($closing->exchange_rate, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="totals">
                <div class="row main">
                    <span class="muted">Total recaudado (USD)</span>
                    <span class="amount big">{{ usd($closing->total_usd) }}</span>
                </div>
                <div class="row">
                    <span class="muted">Bs cobrados (tasa histórica del período)</span>
                    <span class="amount emerald">{{ ves($closing->total_ves) }}</span>
                </div>
                <div class="row">
                    <span class="muted">Equivalente a tasa de cierre</span>
                    <span class="amount">{{ ves($reference) }}</span>
                </div>
                <div class="row">
                    <span class="muted">Comisiones barberos</span>
                    <span class="amount">- {{ usd($closing->barber_commission_usd) }}</span>
                </div>
                <div class="row">
                    <span class="muted">Gastos del período</span>
                    <span class="amount">- {{ usd($closing->total_expenses_usd) }}</span>
                </div>
                <div class="row main">
                    <span class="strong">Ganancia neta tienda</span>
                    <span class="amount emerald">{{ usd($closing->shop_amount_usd) }}</span>
                </div>
            </div>
        </section>

        <!-- Por barbero / método de pago -->
        <div class="two-col">
            <section>
                <div class="section-head">
                    <h2>Por barbero</h2>
                    <span>{{ count($closing->details['barberos'] ?? []) }} responsables</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Barbero</th><th class="text-right">Tickets</th><th class="text-right">Comisión</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($closing->details['barberos'] ?? [] as $row)
                            @php($isStore = in_array($row['name'] ?? '', ['Sin barbero', 'Venta tienda'], true))
                            <tr>
                                <td>{{ $isStore ? 'Venta tienda' : $row['name'] }}</td>
                                <td class="text-right">{{ $row['tickets'] }}</td>
                                <td class="text-right strong">{{ usd($row['commission_usd']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section>
                <div class="section-head">
                    <h2>Por método de pago</h2>
                    <span>{{ count($closing->details['metodos_pago'] ?? []) }} vías</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Método</th><th class="text-right">Tickets</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($closing->details['metodos_pago'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-right">{{ $row['count'] }}</td>
                                <td class="text-right strong">{{ usd($row['total_usd']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Gastos del período -->
        <section>
            <div class="section-head">
                <h2>Gastos registrados</h2>
                <span>{{ $expenses->count() }} registros</span>
            </div>
            <table>
                <thead>
                    <tr><th>Fecha</th><th>Concepto</th><th>Nota</th><th class="text-right">Monto</th></tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['concept'] }}</td>
                            <td class="muted">{{ $row['notes'] ?? '—' }}</td>
                            <td class="text-right strong">- {{ usd($row['amount_usd']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Sin gastos registrados en este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <!-- Servicios / productos vendidos -->
        <div class="two-col">
            <section>
                <div class="section-head">
                    <h2>Servicios vendidos</h2>
                    <span>{{ $services->count() }} ítems</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Servicio</th><th class="text-right">Cant.</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-right">{{ $row['quantity'] }}</td>
                                <td class="text-right strong">{{ usd($row['total_usd']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Sin servicios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section>
                <div class="section-head">
                    <h2>Productos vendidos</h2>
                    <span>{{ $products->count() }} ítems</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Producto</th><th class="text-right">Cant.</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-right">{{ $row['quantity'] }}</td>
                                <td class="text-right strong">{{ usd($row['total_usd']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Sin productos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        @if ($closing->notes)
            <section>
                <h2>Notas</h2>
                <p>{{ $closing->notes }}</p>
            </section>
        @endif

        <footer class="doc-footer">
            <div>
                @if ($closing->closed_at)
                    <p class="muted">Cerrado por <strong>{{ $closing->closedBy->name ?? '—' }}</strong> el {{ $closing->closed_at->format('d/m/Y h:i a') }}.</p>
                @endif
                @if ($closing->reopened_at)
                    <p class="muted">Reabierto por <strong>{{ $closing->reopenedBy->name ?? '—' }}</strong> el {{ $closing->reopened_at->format('d/m/Y h:i a') }}.</p>
                @endif
            </div>
            <div class="muted">{{ $shopName }} · Cierre {{ ucfirst($closing->period_type) }}</div>
        </footer>
    </div>
</body>
</html>
