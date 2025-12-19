@props(['collapsed' => false])

<aside
    x-data="{
        collapsed: @js($collapsed),
        expandedMenus: ['Master Data'],
        toggleMenu(title) {
            if (this.expandedMenus.includes(title)) {
                this.expandedMenus = this.expandedMenus.filter(t => t !== title);
            } else {
                this.expandedMenus.push(title);
            }
        },
        isActive(path) {
            return window.location.pathname === path || window.location.pathname.startsWith(path + '/');
        },
        isParentActive(paths) {
            return paths.some(p => window.location.pathname === p || window.location.pathname.startsWith(p + '/'));
        }
    }"
    :class="collapsed ? 'w-16' : 'w-64'"
    class="fixed left-0 top-0 z-40 h-screen bg-sidebar text-sidebar-foreground transition-all duration-300 flex flex-col"
>
    <!-- Logo -->
    <div class="flex h-16 items-center justify-between border-b border-sidebar-border px-4">
        <template x-if="!collapsed">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sidebar-primary"><path d="M3 11v3a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-3"/><path d="M12 19H4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-3.83"/><path d="m3 11 7.77-6.04a2 2 0 0 1 2.46 0L21 11H3Z"/><path d="M12.97 19.77 7 15h12.5l-3.75 4.5a2 2 0 0 1-2.78.27Z"/></svg>
                <span class="font-semibold text-lg">Siomay Manager</span>
            </div>
        </template>
        <template x-if="collapsed">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sidebar-primary mx-auto"><path d="M3 11v3a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-3"/><path d="M12 19H4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-3.83"/><path d="m3 11 7.77-6.04a2 2 0 0 1 2.46 0L21 11H3Z"/><path d="M12.97 19.77 7 15h12.5l-3.75 4.5a2 2 0 0 1-2.78.27Z"/></svg>
        </template>
        <button
            @click="collapsed = !collapsed; $dispatch('sidebar-toggle', { collapsed })"
            :class="collapsed && 'mx-auto'"
            class="rounded-lg p-1.5 hover:bg-sidebar-accent transition-colors"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="collapsed && 'rotate-180'" class="transition-transform"><path d="m15 18-6-6 6-6"/></svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto scrollbar-thin py-4 px-2">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="/"
                   :class="isActive('/') && window.location.pathname === '/' ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent'"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span x-show="!collapsed" class="text-sm font-medium">Dashboard</span>
                </a>
            </li>

            <!-- Master Data -->
            <li>
                <button
                    @click="!collapsed && toggleMenu('Master Data')"
                    :class="isParentActive(['/products', '/materials', '/customers', '/suppliers', '/employees', '/units', '/payment-methods', '/material-categories']) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent'"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l3 1.8a2 2 0 0 0 2.06 0L12 19v-5.5l-5-3-4.03 2.42Z"/><path d="m7 16.5-4.74-2.85"/><path d="m7 16.5 5-3"/><path d="M7 16.5v5.17"/><path d="M12 13.5V19l3.97 2.38a2 2 0 0 0 2.06 0l3-1.8a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L17 10.5l-5 3Z"/><path d="m17 16.5-5-3"/><path d="m17 16.5 4.74-2.85"/><path d="M17 16.5v5.17"/><path d="M7.97 4.42A2 2 0 0 0 7 6.13v4.37l5 3 5-3V6.13a2 2 0 0 0-.97-1.71l-3-1.8a2 2 0 0 0-2.06 0l-3 1.8Z"/><path d="M12 8 7.26 5.15"/><path d="m12 8 4.74-2.85"/><path d="M12 13.5V8"/></svg>
                    <template x-if="!collapsed">
                        <span class="flex-1 text-left text-sm font-medium">Master Data</span>
                    </template>
                    <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="expandedMenus.includes('Master Data') && 'rotate-180'" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <ul x-show="!collapsed && expandedMenus.includes('Master Data')" x-collapse class="mt-1 space-y-1 pl-4">
                    <li><a href="/products" :class="isActive('/products') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Produk</a></li>
                    <li><a href="/materials" :class="isActive('/materials') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Bahan Baku</a></li>
                    <li><a href="/customers" :class="isActive('/customers') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Pelanggan</a></li>
                    <li><a href="/suppliers" :class="isActive('/suppliers') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Supplier</a></li>
                    <li><a href="/employees" :class="isActive('/employees') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Karyawan</a></li>
                </ul>
            </li>

            <!-- Inventori -->
            <li>
                <button
                    @click="!collapsed && toggleMenu('Inventori')"
                    :class="isParentActive(['/purchases']) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent'"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    <template x-if="!collapsed">
                        <span class="flex-1 text-left text-sm font-medium">Inventori</span>
                    </template>
                    <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="expandedMenus.includes('Inventori') && 'rotate-180'" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <ul x-show="!collapsed && expandedMenus.includes('Inventori')" x-collapse class="mt-1 space-y-1 pl-4">
                    <li><a href="/purchases" :class="isActive('/purchases') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Pembelian</a></li>
                </ul>
            </li>

            <!-- Produksi -->
            <li>
                <button
                    @click="!collapsed && toggleMenu('Produksi')"
                    :class="isParentActive(['/recipes', '/productions']) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent'"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/></svg>
                    <template x-if="!collapsed">
                        <span class="flex-1 text-left text-sm font-medium">Produksi</span>
                    </template>
                    <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="expandedMenus.includes('Produksi') && 'rotate-180'" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <ul x-show="!collapsed && expandedMenus.includes('Produksi')" x-collapse class="mt-1 space-y-1 pl-4">
                    <li><a href="/recipes" :class="isActive('/recipes') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Resep/BOM</a></li>
                    <li><a href="/productions" :class="isActive('/productions') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Input Produksi</a></li>
                </ul>
            </li>

            <!-- Penjualan -->
            <li>
                <button
                    @click="!collapsed && toggleMenu('Penjualan')"
                    :class="isParentActive(['/orders']) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent'"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <template x-if="!collapsed">
                        <span class="flex-1 text-left text-sm font-medium">Penjualan</span>
                    </template>
                    <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="expandedMenus.includes('Penjualan') && 'rotate-180'" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <ul x-show="!collapsed && expandedMenus.includes('Penjualan')" x-collapse class="mt-1 space-y-1 pl-4">
                    <li><a href="/orders" :class="isActive('/orders') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Daftar Pesanan</a></li>
                </ul>
            </li>

            <!-- Keuangan -->
            <li>
                <button
                    @click="!collapsed && toggleMenu('Keuangan')"
                    :class="isParentActive(['/finance/capitals', '/finance/debts', '/finance/wages']) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent'"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>
                    <template x-if="!collapsed">
                        <span class="flex-1 text-left text-sm font-medium">Keuangan</span>
                    </template>
                    <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="expandedMenus.includes('Keuangan') && 'rotate-180'" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <ul x-show="!collapsed && expandedMenus.includes('Keuangan')" x-collapse class="mt-1 space-y-1 pl-4">
                    <li><a href="/finance/capitals" :class="isActive('/finance/capitals') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Modal</a></li>
                    <li><a href="/finance/wages" :class="isActive('/finance/wages') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Upah</a></li>
                    <li><a href="/finance/debts" :class="isActive('/finance/debts') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent text-sidebar-foreground/80'" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>Utang</a></li>
                </ul>
            </li>

            <!-- Aset -->
            <li>
                <a href="/assets"
                   :class="isActive('/assets') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent'"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                    <span x-show="!collapsed" class="text-sm font-medium">Aset</span>
                </a>
            </li>

            <!-- Laporan -->
            <li>
                <a href="/reports"
                   :class="isActive('/reports') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent'"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                    <span x-show="!collapsed" class="text-sm font-medium">Laporan</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer -->
    <div x-show="!collapsed" class="border-t border-sidebar-border p-4">
        <p class="text-xs text-sidebar-foreground/60">
            © 2024 Siomay Manager
        </p>
    </div>
</aside>
