@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'slate'])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-600',
        'amber' => 'bg-amber-100 text-amber-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'rose' => 'bg-rose-100 text-rose-700',
    ];
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        @if ($icon)
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['slate'] }}">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>
    <p class="mt-3 text-2xl font-bold text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
