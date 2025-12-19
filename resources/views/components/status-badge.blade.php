@props([
    'variant' => 'default'
])

@php
$variants = [
    'default' => 'bg-muted text-muted-foreground',
    'success' => 'bg-success/10 text-success',
    'warning' => 'bg-warning/10 text-warning',
    'info' => 'bg-info/10 text-info',
    'destructive' => 'bg-destructive/10 text-destructive',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $variants[$variant]]) }}>
    {{ $slot }}
</span>
