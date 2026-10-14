@props([
    'title',
    'value',
    'subvalue' => '',
    'badgeText' => '',
    'badgeType' => 'success', 
    'iconBgClass' => 'bg-primary-100 text-primary-600'
])

<div class="admin-stat-card">
    <div class="admin-stat-card__header">
        <h3 class="admin-stat-card__title">{{ $title }}</h3>
        <div class="admin-stat-card__icon {{ $iconBgClass }}">
            {{ $icon }}
        </div>
    </div>
    <div class="admin-stat-card__body">
        <div class="admin-stat-card__value-group">
            <span class="admin-stat-card__value">{{ $value }}</span>
            @if($subvalue)
                <span class="admin-stat-card__subvalue">{{ $subvalue }}</span>
            @endif
        </div>
        @if($badgeText)
            <div class="admin-stat-card__badge badge--{{ $badgeType }}">
                {!! $badgeText !!}
            </div>
        @endif
    </div>
</div>
