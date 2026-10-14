@props([
    'title',
    'value',
    'subvalue' => '',
    'badgeText' => '',
    'badgeType' => 'rose', 
    'iconBgClass' => 'bg-rose-50 text-rose-600'
])

@php
    $badgeClasses = match($badgeType) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'warning', 'orange' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger' => 'bg-red-50 text-red-700 border-red-200',
        'purple' => 'bg-purple-50 text-purple-700 border-purple-200',
        default => 'bg-rose-50 text-rose-700 border-rose-200',
    };
@endphp

<div class="bg-gradient-to-br from-white/95 via-rose-50/40 to-white/90 backdrop-blur-md rounded-2xl p-5 border border-rose-200/70 shadow-xs hover:shadow-sm hover:border-rose-300 transition-all duration-200 relative overflow-hidden">
    {{-- Subtle blurred color spot inside card --}}
    <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-rose-300/15 blur-xl pointer-events-none"></div>

    <div class="relative z-10 flex items-center justify-between gap-3 mb-3">
        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">{{ $title }}</span>
        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $iconBgClass }} shadow-xs relative">
            <div class="absolute inset-0 rounded-xl bg-rose-400/20 blur-xs"></div>
            <div class="relative z-10">
                {{ $icon ?? '' }}
            </div>
        </div>
    </div>
    <div class="relative z-10 flex items-baseline justify-between gap-2 flex-wrap">
        <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-extrabold text-stone-900 leading-none">{{ $value }}</span>
            @if($subvalue)
                <span class="text-xs font-semibold text-stone-400">{{ $subvalue }}</span>
            @endif
        </div>
        @if($badgeText)
            <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $badgeClasses }}">
                {!! $badgeText !!}
            </span>
        @endif
    </div>
</div>
