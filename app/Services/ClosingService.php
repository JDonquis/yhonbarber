<?php

namespace App\Services;

use App\Models\Closing;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClosingService
{
    public function __construct(protected ExchangeRateService $rates) {}

    /**
     * Resolve the start/end dates for a period type.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function periodRange(string $type, ?Carbon $date = null): array
    {
        $date = ($date ?? now())->copy();

        return match ($type) {
            Closing::PERIOD_WEEKLY => [
                $date->copy()->startOfWeek()->startOfDay(),
                $date->copy()->endOfWeek()->endOfDay(),
            ],
            Closing::PERIOD_MONTHLY => [
                $date->copy()->startOfMonth()->startOfDay(),
                $date->copy()->endOfMonth()->endOfDay(),
            ],
            default => [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Build the totals and breakdown for a period.
     */
    public function summarize(Carbon $start, Carbon $end): array
    {
        /** @var Collection<int, Sale> $sales */
        $sales = Sale::query()
            ->completed()
            ->between($start, $end)
            ->with(['items', 'barber'])
            ->get();

        $serviceTotal = 0.0;
        $productTotal = 0.0;
        $commission = 0.0;
        $totalVes = 0.0;
        $services = [];
        $products = [];
        $byBarber = [];
        $byPayment = [];

        foreach ($sales as $sale) {
            $commission += (float) $sale->barber_commission_usd;
            $totalVes += (float) $sale->total_ves;

            $payment = $sale->payment_method ?: 'Sin especificar';
            $byPayment[$payment]['name'] = $payment;
            $byPayment[$payment]['count'] = ($byPayment[$payment]['count'] ?? 0) + 1;
            $byPayment[$payment]['total_usd'] = ($byPayment[$payment]['total_usd'] ?? 0) + (float) $sale->total_usd;
            $byPayment[$payment]['total_ves'] = ($byPayment[$payment]['total_ves'] ?? 0) + (float) $sale->total_ves;

            $barberKey = $sale->barber_id ?? 0;
            $byBarber[$barberKey]['name'] = $sale->barber->name ?? 'Venta tienda';
            $byBarber[$barberKey]['tickets'] = ($byBarber[$barberKey]['tickets'] ?? 0) + 1;
            $byBarber[$barberKey]['total_usd'] = ($byBarber[$barberKey]['total_usd'] ?? 0) + (float) $sale->total_usd;
            $byBarber[$barberKey]['commission_usd'] = ($byBarber[$barberKey]['commission_usd'] ?? 0) + (float) $sale->barber_commission_usd;

            foreach ($sale->items as $item) {
                if ($item->item_type === SaleItem::TYPE_SERVICE) {
                    $serviceTotal += (float) $item->line_total_usd;
                    $key = $item->name;
                    $services[$key]['name'] = $item->name;
                    $services[$key]['quantity'] = ($services[$key]['quantity'] ?? 0) + $item->quantity;
                    $services[$key]['total_usd'] = ($services[$key]['total_usd'] ?? 0) + (float) $item->line_total_usd;
                } else {
                    $productTotal += (float) $item->line_total_usd;
                    $key = $item->name;
                    $products[$key]['name'] = $item->name;
                    $products[$key]['quantity'] = ($products[$key]['quantity'] ?? 0) + $item->quantity;
                    $products[$key]['total_usd'] = ($products[$key]['total_usd'] ?? 0) + (float) $item->line_total_usd;
                }
            }
        }

        $total = $serviceTotal + $productTotal;

        return [
            'total_services_usd' => round($serviceTotal, 2),
            'total_products_usd' => round($productTotal, 2),
            'total_usd' => round($total, 2),
            'total_ves' => round($totalVes, 2),
            'barber_commission_usd' => round($commission, 2),
            'shop_amount_usd' => round($total - $commission, 2),
            'ticket_count' => $sales->count(),
            'details' => [
                'barberos' => array_values($byBarber),
                'metodos_pago' => array_values($byPayment),
                'servicios' => array_values($services),
                'productos' => array_values($products),
            ],
        ];
    }

    /**
     * Create (or refresh) an open closing record for the given period.
     */
    public function generate(string $type, ?Carbon $date = null, ?User $user = null): Closing
    {
        [$start, $end] = $this->periodRange($type, $date);
        $summary = $this->summarize($start, $end);
        $referenceRate = $this->rates->current();
        $averageRate = $summary['total_usd'] > 0
            ? round($summary['total_ves'] / $summary['total_usd'], 4)
            : 0;

        $closing = Closing::query()->firstOrNew([
            'period_type' => $type,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ]);

        if ($closing->exists && $closing->isClosed()) {
            return $closing;
        }

        $closing->fill($summary + [
            'exchange_rate' => $referenceRate,
            'average_rate' => $averageRate,
            'total_ves_reference' => round($summary['total_usd'] * $referenceRate, 2),
            'status' => Closing::STATUS_OPEN,
        ]);

        $closing->closed_by = null;
        $closing->closed_at = null;
        $closing->save();

        return $closing;
    }

    /**
     * Freeze a closing so its totals cannot change.
     */
    public function close(Closing $closing, User $user, ?string $notes = null): Closing
    {
        if ($closing->isClosed()) {
            return $closing;
        }

        [$start, $end] = [$closing->period_start->copy()->startOfDay(), $closing->period_end->copy()->endOfDay()];
        $summary = $this->summarize($start, $end);
        $referenceRate = $this->rates->current();
        $averageRate = $summary['total_usd'] > 0
            ? round($summary['total_ves'] / $summary['total_usd'], 4)
            : 0;

        $closing->fill($summary + [
            'exchange_rate' => $referenceRate,
            'average_rate' => $averageRate,
            'total_ves_reference' => round($summary['total_usd'] * $referenceRate, 2),
            'status' => Closing::STATUS_CLOSED,
            'closed_by' => $user->id,
            'closed_at' => now(),
            'reopened_by' => null,
            'reopened_at' => null,
            'notes' => $notes ?? $closing->notes,
        ]);

        $closing->save();

        return $closing;
    }

    /**
     * Reopen a previously closed period so corrections can be made.
     */
    public function reopen(Closing $closing, User $user): Closing
    {
        if (! $closing->isClosed()) {
            return $closing;
        }

        $closing->update([
            'status' => Closing::STATUS_OPEN,
            'reopened_by' => $user->id,
            'reopened_at' => now(),
        ]);

        return $closing->refresh();
    }

    public function isPeriodClosed(Carbon $moment): bool
    {
        return Closing::query()
            ->where('status', Closing::STATUS_CLOSED)
            ->whereDate('period_start', '<=', $moment)
            ->whereDate('period_end', '>=', $moment)
            ->exists();
    }
}
