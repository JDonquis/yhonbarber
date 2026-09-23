@props(['tone' => 'slate'])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-700',
        'green' => 'bg-emerald-100 text-emerald-700',
        'amber' => 'bg-amber-100 text-amber-800',
        'red' => 'bg-rose-100 text-rose-700',
        'sky' => 'bg-sky-100 text-sky-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '.($tones[$tone] ?? $tones['slate'])]) }}>
    {{ $slot }}
</span>
