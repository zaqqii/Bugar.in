@props([
    'title',
    'value',
    'subvalue' => '',
    'badgeText' => '',
    'badgeType' => 'blue',
    'iconBgClass' => 'tile-icon--blue',
    'trend' => '',       // e.g. "+2 bulan ini"
    'trendType' => 'up', // 'up' | 'down' | 'neutral'
    'variant' => 'default', // 'default' | 'hero' | 'cyan'
])

@php
    $badgeClasses = match($badgeType) {
        'success', 'green' => 'badge--green',
        'warning', 'orange' => 'badge--orange',
        'danger', 'red'    => 'bg-red-50 text-red-700 border border-red-200',
        'cyan'             => 'badge--cyan',
        'lime'             => 'badge--lime',
        'gray'             => 'badge--gray',
        default            => 'badge--blue',
    };

    $tileClass = match($variant) {
        'hero'  => 'activity-tile activity-tile--hero',
        'cyan'  => 'activity-tile activity-tile--cyan',
        default => 'activity-tile',
    };

    $valueColor = match($variant) {
        'hero', 'cyan' => 'text-white',
        default        => 'text-slate-900',
    };

    $labelColor = match($variant) {
        'hero', 'cyan' => 'text-white/70',
        default        => 'text-slate-500',
    };

    $trendColors = [
        'up'      => $variant !== 'default' ? 'text-white/80' : 'text-emerald-600',
        'down'    => $variant !== 'default' ? 'text-white/80' : 'text-red-500',
        'neutral' => $variant !== 'default' ? 'text-white/70' : 'text-slate-400',
    ];
@endphp

<div class="{{ $tileClass }}">
    {{-- Top row: label + icon --}}
    <div class="flex items-start justify-between gap-2">
        <span class="text-[11px] font-bold uppercase tracking-wider {{ $labelColor }}">{{ $title }}</span>
        <div class="tile-icon {{ $variant !== 'default' ? 'tile-icon--white' : $iconBgClass }} flex-shrink-0">
            {{ $icon ?? '' }}
        </div>
    </div>

    {{-- Value row --}}
    <div class="flex items-baseline gap-1.5 mt-1">
        <span class="text-2xl font-extrabold {{ $valueColor }} leading-none tabular-nums">{{ $value }}</span>
        @if($subvalue)
            <span class="text-xs font-semibold {{ $labelColor }}">{{ $subvalue }}</span>
        @endif
    </div>

    {{-- Bottom row: badge + trend --}}
    <div class="flex items-center justify-between gap-2 mt-auto pt-1">
        @if($badgeText)
            <span class="badge {{ $badgeClasses }} text-[10px] {{ $variant !== 'default' ? 'bg-white/20 text-white border-white/30' : '' }}">
                {!! $badgeText !!}
            </span>
        @endif
        @if($trend)
            <span class="text-[11px] font-semibold {{ $trendColors[$trendType] ?? $trendColors['neutral'] }} ml-auto">
                {{ $trend }}
            </span>
        @endif
    </div>
</div>
