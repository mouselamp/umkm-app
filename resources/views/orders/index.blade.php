<x-app-layout :title="'Pesanan'" :breadcrumbs="[['label' => 'Penjualan'], ['label' => 'Pesanan']]">
    <div x-data="{
        orders: [],
        customers: [],
        products: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,
        showModal: false,
        showDetailModal: false,
        formLoading: false,
        formErrors: {},
        selected: null,
        showPaymentModal: false,
        form: { customer_id: '', order_date: new Date().toISOString().split('T')[0], payment_type: 'cash', notes: '', items: [] },

        resetForm() { this.form = { customer_id: '', order_date: new Date().toISOString().split('T')[0], payment_type: 'cash', notes: '', items: [{ product_id: '', qty: 1, unit_price: 0 }] }; this.formErrors = {}; },
        addItem() { this.form.items.push({ product_id: '', qty: 1, unit_price: 0 }); },
        removeItem(i) { if (this.form.items.length > 1) this.form.items.splice(i, 1); },
        getTotal() { return this.form.items.reduce((s, i) => s + (i.qty * Number(i.unit_price)), 0); },
        formatDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); },
        openCreateModal() { this.resetForm(); this.showModal = true; },
        async openDetailModal(o) {
            this.selected = o;
            this.showDetailModal = true;
            // Fetch detail with items
            try {
                const r = await fetch(`/api/v1/orders/${o.id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                if (r.ok) { const d = await r.json(); this.selected = d.data; }
            } catch (e) { console.error(e); }
        },
        openPaymentModal(o) { this.selected = o; this.showPaymentModal = true; },

        setProductPrice(idx) {
            const pid = this.form.items[idx].product_id;
            const prod = this.products.find(p => p.id == pid);
            if (prod) this.form.items[idx].unit_price = prod.price;
        },

        async save() {
            this.formLoading = true;
            $store.loading.start('Menyimpan pesanan...');
            try {
                const r = await fetch('/api/v1/orders', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.form) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async markAsPaid() {
            this.formLoading = true;
            $store.loading.start('Memproses pelunasan...');
            try {
                const r = await fetch(`/api/v1/orders/${this.selected.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` },
                    body: JSON.stringify({ payment_status: 'paid', status: this.selected.status })
                });
                if (r.ok) { this.showPaymentModal = false; this.fetch(); } else { const d = await r.json(); alert(d.message || 'Gagal melunasi pesanan'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const r = await fetch(`/api/v1/orders?page=${this.page}&search=${this.search}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.orders = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchDropdowns() {
            const [cr, pr] = await Promise.all([fetch('/api/v1/customers', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }), fetch('/api/v1/products', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } })]);
            this.customers = (await cr.json()).data || []; this.products = (await pr.json()).data || [];
        },
        init() { this.fetch(); this.fetchDropdowns(); this.$watch('search', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Pesanan Penjualan" description="Kelola dan pantau status pesanan">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    Pesanan Baru
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari pesanan...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">No. Pesanan</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Tanggal</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Pelanggan</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Status</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Pembayaran</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Total</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="p-8 text-center text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin mx-auto mb-2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                Memuat...
                            </td>
                        </tr>
                    </template>
                    <template x-for="o in orders" :key="o.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-foreground" x-text="o.order_number"></span>
                            </td>
                            <td class="p-4 text-sm text-muted-foreground" x-text="formatDate(o.order_date)"></td>
                            <td class="p-4 text-sm text-foreground" x-text="o.customer?.name || 'Walk-in'"></td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-muted text-muted-foreground': o.status === 'pending',
                                        'bg-info/10 text-info': o.status === 'processing',
                                        'bg-success/10 text-success': o.status === 'completed',
                                        'bg-destructive/10 text-destructive': o.status === 'cancelled'
                                    }"
                                    x-text="o.status === 'pending' ? 'Menunggu' : o.status === 'processing' ? 'Diproses' : o.status === 'completed' ? 'Selesai' : 'Dibatalkan'"
                                ></span>
                            </td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-destructive/10 text-destructive': o.payment_status === 'unpaid',
                                        'bg-warning/10 text-warning': o.payment_status === 'partial',
                                        'bg-success/10 text-success': o.payment_status === 'paid'
                                    }"
                                    x-text="o.payment_status === 'unpaid' ? 'Belum Bayar' : o.payment_status === 'partial' ? 'Sebagian' : 'Lunas'"
                                ></span>
                            </td>
                            <td class="p-4 text-right font-medium text-foreground" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(o.total)"></td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDetailModal(o)" class="btn btn-ghost btn-icon h-8 w-8" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <template x-if="o.payment_status !== 'paid'">
                                        <button @click="openPaymentModal(o)" class="btn btn-ghost btn-icon h-8 w-8 text-success hover:text-success" title="Lunasi">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                        </button>
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
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground">Pesanan Baru</h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Pelanggan</label>
                            <select x-model="form.customer_id" class="input">
                                <option value="">Pelanggan Umum</option>
                                <template x-for="c in customers" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Tanggal</label>
                            <input type="date" x-model="form.order_date" class="input" required>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Metode Pembayaran</label>
                        <select x-model="form.payment_type" class="input">
                            <option value="cash">Tunai</option>
                            <option value="credit">Kredit</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>

                    <!-- Items -->
                    <div class="border-t border-border pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-foreground">Item Pesanan</h4>
                            <button type="button" @click="addItem()" class="text-sm text-primary hover:underline">+ Tambah Item</button>
                        </div>
                        <template x-for="(item, idx) in form.items" :key="idx">
                            <div class="grid grid-cols-12 gap-2 mb-2 items-end">
                                <div class="col-span-5">
                                    <select x-model="form.items[idx].product_id" @change="setProductPrice(idx)" class="input text-sm" required>
                                        <option value="">Pilih Produk</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" x-text="p.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <input type="number" x-model="form.items[idx].qty" min="1" class="input text-sm" placeholder="Qty" required>
                                </div>
                                <div class="col-span-4">
                                    <input type="text"
                                        :value="$formatNumber(form.items[idx].unit_price)"
                                        @input="form.items[idx].unit_price = $parseNumber($event.target.value); $event.target.value = $formatNumber(form.items[idx].unit_price)"
                                        class="input text-sm text-right" placeholder="Harga" required>
                                </div>
                                <div class="col-span-1">
                                    <button type="button" @click="removeItem(idx)" class="btn btn-ghost btn-icon h-10 w-10 text-destructive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div class="flex justify-end mt-4 pt-4 border-t border-border">
                            <div class="text-right">
                                <span class="text-sm text-muted-foreground">Total:</span>
                                <span class="text-lg font-bold text-foreground ml-2" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(getTotal())"></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea x-model="form.notes" rows="2" class="input resize-none" placeholder="Catatan pesanan..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading">Buat Pesanan</span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="showDetailModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showDetailModal = false" x-show="showDetailModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="'Pesanan ' + selected?.order_number"></h3>
                    <button @click="showDetailModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Pelanggan:</span>
                            <p class="font-medium text-foreground" x-text="selected?.customer?.name || 'Walk-in'"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Tanggal:</span>
                            <p class="font-medium text-foreground" x-text="formatDate(selected?.order_date)"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Status:</span>
                            <p class="font-medium text-foreground" x-text="selected?.status"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Total:</span>
                            <p class="font-bold text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selected?.total || 0)"></p>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <h4 class="font-semibold text-foreground mb-3">Item Pesanan</h4>
                        <template x-for="i in selected?.items" :key="i.id">
                            <div class="flex justify-between text-sm py-2 border-b border-border last:border-0">
                                <span class="text-foreground" x-text="i.product?.name"></span>
                                <span class="text-muted-foreground" x-text="i.qty + ' x Rp ' + new Intl.NumberFormat('id-ID').format(i.price)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showPaymentModal = false" x-show="showPaymentModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-success/10 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-foreground">Konfirmasi Pelunasan</h3>
                        <p class="text-sm text-muted-foreground">Tandai pesanan sebagai lunas?</p>
                    </div>
                </div>
                <div class="bg-muted/50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-muted-foreground">No. Pesanan:</span>
                        <span class="font-medium text-foreground" x-text="selected?.order_number"></span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-muted-foreground">Pelanggan:</span>
                        <span class="font-medium text-foreground" x-text="selected?.customer?.name || 'Walk-in'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Total:</span>
                        <span class="font-bold text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selected?.total || 0)"></span>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="showPaymentModal = false" class="btn btn-outline btn-md">Batal</button>
                    <button @click="markAsPaid()" :disabled="formLoading" class="btn btn-success btn-md">
                        <span x-show="!formLoading">Konfirmasi Lunas</span>
                        <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
