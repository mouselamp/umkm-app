@props(['title' => '', 'breadcrumbs' => []])

<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-6">
    <div class="flex flex-col gap-1">
        <!-- Breadcrumb -->
        @if(count($breadcrumbs) > 0)
        <nav class="flex items-center gap-1 text-sm text-muted-foreground">
            @foreach($breadcrumbs as $index => $item)
                <span class="flex items-center gap-1">
                    @if($index > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    @endif
                    @if(isset($item['href']))
                        <a href="{{ $item['href'] }}" class="hover:text-foreground transition-colors">{{ $item['label'] }}</a>
                    @else
                        <span class="text-foreground">{{ $item['label'] }}</span>
                    @endif
                </span>
            @endforeach
        </nav>
        @endif
        <!-- Page Title -->
        <h1 class="text-xl font-semibold text-foreground">{{ $title }}</h1>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-4">
        <!-- Search -->
        <div class="relative hidden md:block">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" placeholder="Cari..." class="w-64 h-10 pl-9 pr-3 rounded-md border border-input bg-muted/50 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
        </div>

        <!-- Dark Mode Toggle -->
        <button @click="toggleDarkMode()" class="relative rounded-full p-2 hover:bg-accent transition-colors">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        </button>

        <!-- User Menu -->
        <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false" x-show="user">
            <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-foreground" x-text="user?.name"></p>
                    <p class="text-xs text-muted-foreground" x-text="user?.email"></p>
                </div>
                <div class="bg-primary/10 rounded-full h-10 w-10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground" :class="profileOpen ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div x-show="profileOpen" x-transition class="absolute top-full right-0 mt-2 w-56 bg-card border border-border rounded-lg shadow-lg py-2 z-50" style="display: none;">
                <div class="px-4 py-3 border-b border-border">
                    <p class="text-sm font-semibold text-foreground" x-text="user?.name"></p>
                    <p class="text-xs text-muted-foreground" x-text="user?.email"></p>
                </div>
                <a href="/profile" class="flex items-center gap-3 px-4 py-2 text-sm text-foreground hover:bg-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v6M12 17v6M4.22 4.22l4.24 4.24M15.54 15.54l4.24 4.24M1 12h6M17 12h6M4.22 19.78l4.24-4.24M15.54 8.46l4.24-4.24"/></svg>
                    Pengaturan
                </a>
                <div class="border-t border-border mt-2 pt-2">
                    <button @click="logout()" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
