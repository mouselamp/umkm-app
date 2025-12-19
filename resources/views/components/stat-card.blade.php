@props([
    'title',
    'value' => null,
    'subtitle' => null,
    'icon' => null,
    'trend' => null,
    'variant' => 'default'
])

@php
$variantStyles = [
    'default' => 'bg-card',
    'primary' => 'bg-primary/10 border-primary/20',
    'success' => 'bg-success/10 border-success/20',
    'warning' => 'bg-warning/10 border-warning/20',
    'info' => 'bg-info/10 border-info/20',
];

$iconStyles = [
    'default' => 'bg-muted text-muted-foreground',
    'primary' => 'bg-primary/20 text-primary',
    'success' => 'bg-success/20 text-success',
    'warning' => 'bg-warning/20 text-warning',
    'info' => 'bg-info/20 text-info',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border p-5 transition-all hover:shadow-md ' . $variantStyles[$variant]]) }}>
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <p class="text-sm font-medium text-muted-foreground">{{ $title }}</p>
            @if($value)
                <p class="text-2xl font-bold text-foreground">{{ $value }}</p>
            @else
                {{ $slot }}
            @endif
            @if($subtitle)
                <p class="text-xs text-muted-foreground">{{ $subtitle }}</p>
            @endif
            @if($trend)
                <p class="text-xs font-medium {{ $trend['isPositive'] ? 'text-success' : 'text-destructive' }}">
                    {{ $trend['isPositive'] ? '↑' : '↓' }} {{ abs($trend['value']) }}%
                    <span class="text-muted-foreground font-normal">dari kemarin</span>
                </p>
            @endif
        </div>
        @if($icon)
            <div class="rounded-lg p-2.5 {{ $iconStyles[$variant] }}">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
