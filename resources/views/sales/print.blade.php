<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante {{ $sale->code }} · {{ $shopName }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: "Courier New", ui-monospace, SFMono-Regular, Menlo, monospace;
            background: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 20px 12px;
            font-size: 12px;
            line-height: 1.45;
        }

        .ticket {
            width: 300px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 16px 16px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .toolbar {
            width: 300px;
            max-width: 100%;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
        }

        button {
            font-family: inherit;
            padding: 8px 16px;
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .center { text-align: center; }
        .muted { color: #64748b; }
        .bold { font-weight: 700; }

        .logo { width: 64px; height: 64px; object-fit: contain; display: block; margin: 0 auto 6px; }

        .shop-name { font-size: 15px; font-weight: 800; letter-spacing: 0.02em; text-transform: uppercase; }

        .sep { border-top: 1px dashed #94a3b8; margin: 8px 0; }

        .row { display: flex; justify-content: space-between; gap: 8px; }
        .row .label { color: #475569; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; vertical-align: top; }
        th { font-size: 10px; text-transform: uppercase; color: #64748b; text-align: left; }
        td.qty { width: 30px; }
        td.amount { text-align: right; white-space: nowrap; }

        .item-name { word-break: break-word; }
        .item-unit { font-size: 10px; color: #64748b; }

        .total-row { font-size: 14px; font-weight: 800; }
        .grand { font-size: 16px; }

        .stamp {
            display: inline-block;
            margin: 6px auto;
            padding: 3px 10px;
            border: 2px solid #b91c1c;
            color: #b91c1c;
            font-weight: 800;
            letter-spacing: 0.15em;
            transform: rotate(-4deg);
        }

        .thanks { margin-top: 6px; font-weight: 700; }
        .barcode { font-size: 20px; letter-spacing: 2px; }

        @media print {
            body { background: #fff; padding: 0; }
            .ticket { border: none; border-radius: 0; width: auto; padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 6mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <div class="ticket">
        <!-- Encabezado -->
        <div class="center">
            <img src="{{ asset('logo.jpeg') }}" alt="{{ $shopName }}" class="logo">
            <div class="shop-name">{{ $shopName }}</div>
            @if (setting('shop_rif'))<div class="muted">RIF: {{ setting('shop_rif') }}</div>@endif
            @if (setting('shop_phone'))<div class="muted">Tel: {{ setting('shop_phone') }}</div>@endif
            @if (setting('shop_address'))<div class="muted">{{ setting('shop_address') }}</div>@endif
        </div>

        <div class="sep"></div>

        <div class="row"><span class="label">COMPROBANTE</span><span class="bold">{{ $sale->code }}</span></div>
        <div class="row"><span class="label">Fecha</span><span>{{ $sale->sold_at->format('d/m/Y h:i a') }}</span></div>
        <div class="row"><span class="label">Atendió</span><span>{{ $sale->user->name ?? '—' }}</span></div>
        <div class="row"><span class="label">Barbero</span><span>{{ $sale->barber->name ?? 'Venta tienda' }}</span></div>

        @if ($sale->isCancelled())
            <div class="center"><span class="stamp">ANULADA</span></div>
        @endif

        <div class="sep"></div>

        <!-- Ítems -->
        <table>
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Descripción</th>
                    <th style="text-align:right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td>
                            <div class="item-name">{{ $item->name }}</div>
                            <div class="item-unit">{{ usd($item->unit_price_usd, false) }} c/u ·
                                {{ $item->item_type === \App\Models\SaleItem::TYPE_SERVICE ? 'Servicio' : 'Producto' }}</div>
                        </td>
                        <td class="amount">{{ usd($item->line_total_usd, false) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sep"></div>

        <!-- Totales -->
        <div class="row"><span class="label">Artículos</span><span>{{ $sale->items->sum('quantity') }}</span></div>
        <div class="row total-row"><span>TOTAL USD</span><span>{{ usd($sale->total_usd) }}</span></div>
        <div class="row"><span class="label">TOTAL Bs</span><span>{{ ves($sale->total_ves) }}</span></div>
        <div class="row"><span class="label">Tasa aplicada</span><span>Bs. {{ number_format($sale->exchange_rate, 2, ',', '.') }} / $1</span></div>

        <div class="sep"></div>

        <div class="row"><span class="label">Método de pago</span><span>{{ $sale->payment_method ?: 'Sin especificar' }}</span></div>
        <div class="row"><span class="label">Moneda</span><span class="bold">{{ $sale->payment_currency }}</span></div>
        <div class="row"><span class="label">Comisión barbero</span><span>{{ usd($sale->barber_commission_usd) }}</span></div>

        @if ($sale->notes)
            <div class="sep"></div>
            <div class="label">Notas:</div>
            <div>{{ $sale->notes }}</div>
        @endif

        <div class="sep"></div>

        <div class="center">
            <div class="thanks">¡Gracias por su visita!</div>
            <div class="muted">Conserve este comprobante</div>
            <div class="barcode">*{{ $sale->code }}*</div>
            <div class="muted">Emitido: {{ now()->format('d/m/Y h:i a') }}</div>
        </div>
    </div>
</body>
</html>
