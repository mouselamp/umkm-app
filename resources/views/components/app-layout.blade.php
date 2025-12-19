<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Siomay Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js Loading Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('loading', {
                show: false,
                message: 'Menyimpan data...',

                start(message = 'Menyimpan data...') {
                    this.message = message;
                    this.show = true;
                },

                stop() {
                    this.show = false;
                }
            });
        });
    </script>
</head>
<body class="antialiased" x-data="{
    darkMode: localStorage.getItem('darkMode') === 'true',
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    user: null,
    token: localStorage.getItem('token'),
    authReady: false,
    async init() {
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        }

        // Skip auth check for login/register pages
        if (window.location.pathname.includes('login') || window.location.pathname.includes('register')) {
            this.authReady = true;
            return;
        }

        if (this.token) {
            await this.fetchUser();
            this.authReady = true;
        } else {
            window.location.href = '/login';
        }
    },
    async fetchUser() {
        try {
            const response = await fetch('/api/v1/auth/me', {
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                this.user = data.data;
            } else {
                this.logout();
            }
        } catch (error) {
            console.error('Auth check failed', error);
        }
    },
    async logout() {
        localStorage.removeItem('token');
        this.token = null;
        this.user = null;
        window.location.href = '/login';
    }
}" @sidebar-toggle.window="sidebarCollapsed = $event.detail.collapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)">
    <!-- Auth Loading Screen -->
    <div x-show="!authReady" class="fixed inset-0 bg-background z-50 flex items-center justify-center">
        <div class="text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin mx-auto text-primary mb-3"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            <p class="text-muted-foreground text-sm">Memuat...</p>
        </div>
    </div>

    <div x-show="authReady" x-cloak class="min-h-screen bg-background">
        <!-- Sidebar -->
        <x-app-sidebar :collapsed="false" />

        <!-- Main Content -->
        <div :class="sidebarCollapsed ? 'ml-16' : 'ml-64'" class="transition-all duration-300">
            <!-- Header -->
            <x-app-header :title="$title ?? 'Dashboard'" :breadcrumbs="$breadcrumbs ?? []" />

            <!-- Page Content -->
            <main class="p-6 animate-fade-in">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Loading Overlay -->
    <x-loading-overlay />
</body>
</html>
