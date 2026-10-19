@php
    $maxCuts = $topBarbers->max('cuts') ?: 1;
@endphp

<div class="space-y-2">
    @forelse ($topBarbers as $index => $barber)
        @php($words = preg_split('/\s+/', trim($barber->name)))
        @php($initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1)))
        @php($cuts = (int) ($barber->cuts ?? 0))
        @php($width = $maxCuts > 0 ? round($cuts / $maxCuts * 100) : 0)
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="relative shrink-0">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ $initials }}
                        </span>
                        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-extrabold {{ $index === 0 ? 'bg-amber-500 text-slate-900' : 'bg-slate-200 text-slate-500' }}">
                            {{ $index + 1 }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <span class="truncate text-sm font-bold text-slate-800">{{ $barber->name }}</span>
                        <span class="block text-[11px] text-slate-400">{{ $cuts }} corte(s) completados</span>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <span class="block text-sm font-extrabold {{ $index === 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ usd($barber->commission ?? 0) }}</span>
                    <span class="text-[10px] uppercase tracking-wide text-slate-400">Comisión</span>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full {{ $index === 0 ? 'bg-amber-500' : ($index === 1 ? 'bg-amber-400' : 'bg-emerald-500') }}" style="width: {{ $width }}%"></div>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white px-4 py-8 text-center text-sm text-slate-400 shadow-sm ring-1 ring-slate-200">
            Sin datos de barberos todavía.
        </div>
    @endforelse
</div>
