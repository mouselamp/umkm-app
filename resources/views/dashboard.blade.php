<x-app-layout :title="'Dashboard'" :breadcrumbs="[['label' => 'Dashboard']]">
    <div x-data="{
        stats: null,
        loading: true,
        async init() {
            try {
                const response = await fetch('/api/v1/dashboard', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
                });
                const data = await response.json();
                this.stats = data.data;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    }">
        <x-page-header title="Dashboard" description="Ringkasan bisnis Anda hari ini">
            <x-slot:action>
                <div class="flex gap-3">
                    <a href="/orders" class="btn btn-outline btn-md gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        Buat Pesanan
                    </a>
                    <a href="/purchases" class="btn btn-primary btn-md gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        Tambah Stok
                    </a>
                </div>
            </x-slot:action>
        </x-page-header>

        <template x-if="loading">
            <div class="flex justify-center py-10">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin text-primary"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            </div>
        </template>

        <div x-show="!loading && stats" class="space-y-6" style="display: none;">
            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card
                    title="Penjualan Hari Ini"
                    x-bind:value="'Rp ' + new Intl.NumberFormat('id-ID').format(stats?.sales?.today || 0)"
                    variant="primary"
                >
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    </x-slot:icon>
                </x-stat-card>

                <x-stat-card
                    title="Pesanan Pending"
                    variant="info"
                >
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </x-slot:icon>
                </x-stat-card>

                <x-stat-card
                    title="Stok Rendah"
                    variant="warning"
                >
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </x-slot:icon>
                </x-stat-card>

                <x-stat-card
                    title="Produksi Bulan Ini"
                    variant="success"
                >
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/></svg>
                    </x-slot:icon>
                </x-stat-card>
            </div>

            <!-- Dynamic Stats Values -->
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.effect(() => {
                        // This will be handled by x-bind in the stat cards
                    });
                });
            </script>

            <!-- Two Column Layout -->
            <div class="grid gap-6 lg:grid-cols-2" x-data="{
                inventory: null,
                async loadInventory() {
                    const res = await fetch('/api/v1/dashboard/inventory-status', {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
                    });
                    const data = await res.json();
                    this.inventory = data.data;
                }
            }" x-init="loadInventory()">

                <!-- Product Stock -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-foreground">Stok Produk</h3>
                        <a href="/products" class="text-sm text-primary hover:underline">Lihat Semua</a>
                    </div>
                    <div class="rounded-lg border bg-card overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-muted/50">
                                    <th class="p-3 text-left text-sm font-semibold text-foreground">Produk</th>
                                    <th class="p-3 text-right text-sm font-semibold text-foreground">Stok</th>
                                    <th class="p-3 text-right text-sm font-semibold text-foreground">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <template x-for="item in inventory?.products?.slice(0, 5)" :key="item.id">
                                    <tr class="hover:bg-muted/30">
                                        <td class="p-3">
                                            <p class="font-medium text-foreground" x-text="item.name"></p>
                                        </td>
                                        <td class="p-3 text-right text-muted-foreground" x-text="item.quantity + ' unit'"></td>
                                        <td class="p-3 text-right">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="item.quantity < 10 ? 'bg-destructive/10 text-destructive' : 'bg-success/10 text-success'"
                                                x-text="item.quantity < 10 ? 'Stok Rendah' : 'Tersedia'"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Material Stock -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-foreground flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            Stok Bahan Baku
                        </h3>
                        <a href="/materials" class="text-sm text-primary hover:underline">Kelola Stok</a>
                    </div>
                    <div class="rounded-lg border bg-card overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-muted/50">
                                    <th class="p-3 text-left text-sm font-semibold text-foreground">Bahan</th>
                                    <th class="p-3 text-right text-sm font-semibold text-foreground">Stok</th>
                                    <th class="p-3 text-right text-sm font-semibold text-foreground">Min</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <template x-for="item in inventory?.materials?.slice(0, 5)" :key="item.id">
                                    <tr class="hover:bg-muted/30">
                                        <td class="p-3">
                                            <p class="font-medium text-foreground" x-text="item.name"></p>
                                        </td>
                                        <td class="p-3 text-right" :class="item.is_low ? 'text-destructive font-medium' : 'text-muted-foreground'" x-text="item.quantity + ' ' + (item.unit || '')"></td>
                                        <td class="p-3 text-right text-muted-foreground" x-text="(item.min_stock || '-') + ' ' + (item.unit || '')"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-primary/10 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Pendapatan Bulan Ini</p>
                            <p class="text-xl font-bold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(stats?.sales?.this_month || 0)"></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-success/10 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Produksi Bulan Ini</p>
                            <p class="text-xl font-bold" x-text="(stats?.production?.completed_this_month || 0) + ' batch'"></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-info/10 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Pelanggan</p>
                            <p class="text-xl font-bold" x-text="(stats?.customers_count || 0) + ' pelanggan'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
