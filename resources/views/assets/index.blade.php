<x-app-layout :title="'Aset'" :breadcrumbs="[['label' => 'Aset'], ['label' => 'Daftar Aset']]">
    <div x-data="{
        assets: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,
        showModal: false,
        showDetailModal: false,
        showDepreciateModal: false,
        formLoading: false,
        formErrors: {},
        selected: null,
        form: { name: '', asset_number: '', purchase_date: new Date().toISOString().split('T')[0], purchase_price: '', useful_life_month: 12, residual_value: 0, payment_type: 'cash' },
        depreciateForm: { period: new Date().toISOString().slice(0, 7) },

        resetForm() { this.form = { name: '', asset_number: '', purchase_date: new Date().toISOString().split('T')[0], purchase_price: '', useful_life_month: 12, residual_value: 0, payment_type: 'cash' }; this.formErrors = {}; },
        openCreateModal() { this.resetForm(); this.showModal = true; },
        openDetailModal(a) { this.selected = a; this.showDetailModal = true; },
        openDepreciateModal(a) { this.selected = a; this.depreciateForm.period = new Date().toISOString().slice(0, 7); this.showDepreciateModal = true; },

        async save() {
            this.formLoading = true;
            $store.loading.start('Menyimpan aset...');
            this.formErrors = {};
            try {
                const r = await fetch('/api/v1/assets', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.form) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async depreciate() {
            this.formLoading = true;
            $store.loading.start('Mencatat depresiasi...');
            try {
                const r = await fetch(`/api/v1/assets/${this.selected.id}/depreciate`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.depreciateForm) });
                const d = await r.json();
                if (r.ok) { this.showDepreciateModal = false; this.fetch(); alert('Depresiasi berhasil dicatat!'); } else { alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const r = await fetch(`/api/v1/assets?page=${this.page}&search=${this.search}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.assets = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },
        formatCurrency(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v || 0); },
        init() { this.fetch(); this.$watch('search', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Aset Tetap" description="Kelola aset tetap dan pantau depresiasi">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Aset
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari aset...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Aset</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Tgl Pembelian</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Harga Beli</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Nilai Buku</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Status</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="p-8 text-center text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin mx-auto mb-2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                Memuat...
                            </td>
                        </tr>
                    </template>
                    <template x-for="a in assets" :key="a.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-warning/10 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground" x-text="a.name"></p>
                                        <p class="text-xs text-muted-foreground" x-text="a.asset_number"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-muted-foreground" x-text="a.purchase_date"></td>
                            <td class="p-4 text-right font-medium text-foreground" x-text="formatCurrency(a.purchase_price)"></td>
                            <td class="p-4 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-bold" :class="a.book_value < a.residual_value ? 'text-destructive' : 'text-primary'" x-text="formatCurrency(a.book_value)"></span>
                                    <span class="text-xs text-muted-foreground" x-text="formatCurrency(a.monthly_depreciation) + '/bln'"></span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-success/10 text-success': a.status === 'active',
                                        'bg-muted text-muted-foreground': a.status === 'disposed',
                                        'bg-info/10 text-info': a.status === 'sold'
                                    }"
                                    x-text="a.status === 'active' ? 'Aktif' : a.status === 'disposed' ? 'Dibuang' : 'Dijual'"
                                ></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openDetailModal(a)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <template x-if="a.status === 'active'">
                                        <button @click="openDepreciateModal(a)" class="btn btn-sm bg-warning/10 text-warning hover:bg-warning/20">Depresiasi</button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4" x-show="pagination && pagination.last_page > 1">
            <p class="text-sm text-muted-foreground">
                Halaman <span class="font-medium text-foreground" x-text="pagination?.current_page"></span> dari <span class="font-medium text-foreground" x-text="pagination?.last_page"></span>
            </p>
            <div class="flex items-center gap-2">
                <button @click="page--; fetch()" :disabled="page === 1" class="btn btn-outline btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Sebelumnya
                </button>
                <button @click="page++; fetch()" :disabled="page === pagination?.last_page" class="btn btn-outline btn-sm">
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground">Tambah Aset</h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Nama Aset</label>
                        <input type="text" x-model="form.name" class="input" placeholder="Contoh: Komputer, Printer" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Tanggal Pembelian</label>
                            <input type="date" x-model="form.purchase_date" class="input" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Metode Pembayaran</label>
                            <select x-model="form.payment_type" class="input" required>
                                <option value="cash">Tunai</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Harga Beli</label>
                            <input type="text" x-money="form.purchase_price" class="input" placeholder="0" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Masa Pakai (bulan)</label>
                            <input type="number" x-model="form.useful_life_month" min="1" class="input" required>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Nilai Residu</label>
                        <input type="text" x-money="form.residual_value" class="input" placeholder="0">
                    </div>
                    <div class="bg-muted/50 rounded-lg p-4">
                        <div class="text-sm text-muted-foreground">Depresiasi Bulanan:</div>
                        <div class="text-lg font-bold text-primary" x-text="formatCurrency((form.purchase_price - form.residual_value) / (form.useful_life_month || 1))"></div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading">Tambah Aset</span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="showDetailModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showDetailModal = false" x-show="showDetailModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="selected?.name"></h3>
                    <button @click="showDetailModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">No. Aset:</span>
                            <p class="font-medium text-foreground" x-text="selected?.asset_number"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Tgl Pembelian:</span>
                            <p class="font-medium text-foreground" x-text="selected?.purchase_date"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Harga Beli:</span>
                            <p class="font-medium text-foreground" x-text="formatCurrency(selected?.purchase_price)"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Masa Pakai:</span>
                            <p class="font-medium text-foreground" x-text="(selected?.useful_life_month || 0) + ' bulan'"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Nilai Residu:</span>
                            <p class="font-medium text-foreground" x-text="formatCurrency(selected?.residual_value)"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Nilai Buku:</span>
                            <p class="font-bold text-primary" x-text="formatCurrency(selected?.book_value)"></p>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <h4 class="font-semibold text-foreground mb-3">Riwayat Depresiasi</h4>
                        <template x-if="!selected?.depreciations || selected?.depreciations.length === 0">
                            <p class="text-sm text-muted-foreground">Belum ada depresiasi dicatat.</p>
                        </template>
                        <template x-if="selected?.depreciations && selected?.depreciations.length > 0">
                            <div class="space-y-2">
                                <template x-for="d in selected?.depreciations" :key="d.id">
                                    <div class="flex justify-between items-center text-sm py-2 border-b border-border last:border-0">
                                        <span class="text-muted-foreground" x-text="d.period"></span>
                                        <div class="text-right">
                                            <div class="font-medium text-destructive" x-text="'-' + formatCurrency(d.amount)"></div>
                                            <div class="text-xs text-muted-foreground" x-text="'Nilai Buku: ' + formatCurrency(d.book_value_after)"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Depreciate Modal -->
        <div x-show="showDepreciateModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showDepreciateModal = false" x-show="showDepreciateModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground">Catat Depresiasi</h3>
                    <button @click="showDepreciateModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="depreciate()" class="p-6 space-y-4">
                    <div class="bg-muted/50 rounded-lg p-4">
                        <div class="text-sm text-muted-foreground">Aset:</div>
                        <div class="font-semibold text-foreground" x-text="selected?.name"></div>
                        <div class="mt-2 flex justify-between text-sm">
                            <span class="text-muted-foreground">Nilai Buku:</span>
                            <span class="font-medium text-foreground" x-text="formatCurrency(selected?.book_value)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Depresiasi/Bulan:</span>
                            <span class="font-bold text-warning" x-text="formatCurrency(selected?.monthly_depreciation)"></span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Periode</label>
                        <input type="month" x-model="depreciateForm.period" class="input" required>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showDepreciateModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-warning btn-md">
                            <span x-show="!formLoading">Catat Depresiasi</span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
