@props([
    'title',
    'description' => null,
    'action' => null
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-foreground">{{ $title }}</h2>
        @if($description)
            <p class="mt-1 text-sm text-muted-foreground">{{ $description }}</p>
        @endif
    </div>
    @if($action)
        {{ $action }}
    @endif
</div>
