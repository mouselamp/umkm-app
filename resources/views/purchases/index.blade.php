<x-app-layout :title="'Pembelian'" :breadcrumbs="[['label' => 'Inventori'], ['label' => 'Pembelian']]">
    <div x-data="{
        purchases: [],
        suppliers: [],
        materials: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,

        showModal: false,
        showDetailModal: false,
        formLoading: false,
        formErrors: {},
        selected: null,

        form: {
            supplier_id: '',
            purchase_date: new Date().toISOString().split('T')[0],
            payment_type: 'cash',
            notes: '',
            items: []
        },

        resetForm() {
            this.form = {
                supplier_id: '',
                purchase_date: new Date().toISOString().split('T')[0],
                payment_type: 'cash',
                notes: '',
                items: [{ material_id: '', quantity: 1, price: 0 }]
            };
            this.formErrors = {};
            this.selected = null;
        },

        addItem() { this.form.items.push({ material_id: '', quantity: 1, price: 0 }); },
        removeItem(index) { if (this.form.items.length > 1) this.form.items.splice(index, 1); },
        getTotal() { return this.form.items.reduce((sum, item) => sum + (Number(item.price) * item.quantity), 0); },
        getMaterial(id) { return this.materials.find(m => m.id == id); },
        formatDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); },
        openCreateModal() { this.resetForm(); this.showModal = true; },
        async openDetailModal(purchase) {
            this.selected = purchase;
            this.showDetailModal = true;
            try {
                const r = await fetch(`/api/v1/purchases/${purchase.id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                if (r.ok) { const d = await r.json(); this.selected = d.data; }
            } catch (e) { console.error(e); }
        },

        async save() {
            this.formLoading = true;
            this.formErrors = {};
            $store.loading.start('Menyimpan pembelian...');
            try {
                const response = await fetch('/api/v1/purchases', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                if (response.ok) { this.showModal = false; this.fetch(); }
                else if (response.status === 422) { this.formErrors = data.errors || {}; }
                else { alert(data.message || 'Terjadi kesalahan'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, search: this.search });
                const response = await fetch(`/api/v1/purchases?${params}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const data = await response.json();
                this.purchases = data.data;
                this.pagination = data.meta;
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchDropdowns() {
            const [supRes, matRes] = await Promise.all([
                fetch('/api/v1/suppliers', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }),
                fetch('/api/v1/materials', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } })
            ]);
            this.suppliers = (await supRes.json()).data || [];
            this.materials = (await matRes.json()).data || [];
        },

        init() {
            this.fetch();
            this.fetchDropdowns();
            this.$watch('search', () => { this.page = 1; this.fetch(); });
        }
    }">
        <x-page-header title="Pembelian Bahan" description="Riwayat pembelian bahan baku">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Pembelian Baru
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari pembelian...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">No. Pembelian</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Tanggal</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Supplier</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Pembayaran</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Total</th>
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
                    <template x-for="item in purchases" :key="item.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-foreground" x-text="item.purchase_number"></span>
                            </td>
                            <td class="p-4 text-sm text-muted-foreground" x-text="formatDate(item.purchase_date)"></td>
                            <td class="p-4 text-sm text-foreground" x-text="item.supplier?.name || '-'"></td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info/10 text-info" x-text="item.payment_type === 'cash' ? 'Tunai' : item.payment_type === 'transfer' ? 'Transfer' : 'Kredit'"></span>
                            </td>
                            <td class="p-4 text-right font-medium text-foreground" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.total)"></td>
                            <td class="p-4 text-right">
                                <button @click="openDetailModal(item)" class="btn btn-ghost btn-icon h-8 w-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
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
                    <h3 class="text-lg font-bold text-foreground">Pembelian Baru</h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Supplier</label>
                            <select x-model="form.supplier_id" class="input" required>
                                <option value="">Pilih Supplier</option>
                                <template x-for="s in suppliers" :key="s.id">
                                    <option :value="s.id" x-text="s.name"></option>
                                </template>
                            </select>
                            <p x-show="formErrors.supplier_id" class="text-destructive text-xs" x-text="formErrors.supplier_id?.[0]"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Tanggal</label>
                            <input type="date" x-model="form.purchase_date" class="input" required>
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
                            <h4 class="font-semibold text-foreground">Item Pembelian</h4>
                            <button type="button" @click="addItem()" class="text-sm text-primary hover:underline">+ Tambah Item</button>
                        </div>
                        <template x-for="(item, index) in form.items" :key="index">
                            <div class="grid grid-cols-12 gap-2 mb-2 items-end">
                                <div class="col-span-5">
                                    <select x-model="form.items[index].material_id" class="input text-sm" required>
                                        <option value="">Pilih Bahan</option>
                                        <template x-for="m in materials" :key="m.id">
                                            <option :value="m.id" x-text="m.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-3 flex items-center gap-1">
                                    <input type="number" x-model="form.items[index].quantity" min="1" class="input text-sm flex-1" placeholder="Qty" required>
                                    <span class="text-xs text-muted-foreground w-8" x-text="getMaterial(form.items[index].material_id)?.unit?.symbol || ''"></span>
                                </div>
                                <div class="col-span-3">
                                    <input type="text"
                                        :value="$formatNumber(form.items[index].price)"
                                        @input="form.items[index].price = $parseNumber($event.target.value); $event.target.value = $formatNumber(form.items[index].price)"
                                        class="input text-sm text-right" placeholder="Harga" required>
                                </div>
                                <div class="col-span-1">
                                    <button type="button" @click="removeItem(index)" class="btn btn-ghost btn-icon h-10 w-10 text-destructive">
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
                        <textarea x-model="form.notes" rows="2" class="input resize-none" placeholder="Catatan pembelian..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading">Simpan Pembelian</span>
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
                    <h3 class="text-lg font-bold text-foreground" x-text="'Pembelian ' + selected?.purchase_number"></h3>
                    <button @click="showDetailModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Supplier:</span>
                            <p class="font-medium text-foreground" x-text="selected?.supplier?.name || '-'"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Tanggal:</span>
                            <p class="font-medium text-foreground" x-text="formatDate(selected?.purchase_date)"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Pembayaran:</span>
                            <p class="font-medium text-foreground" x-text="selected?.payment_type"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Total:</span>
                            <p class="font-bold text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selected?.total || 0)"></p>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <h4 class="font-semibold text-foreground mb-3">Item Pembelian</h4>
                        <template x-for="item in selected?.items" :key="item.id">
                            <div class="flex justify-between text-sm py-2 border-b border-border last:border-0">
                                <span class="text-foreground" x-text="item.material?.name"></span>
                                <span class="text-muted-foreground" x-text="item.quantity + ' ' + (item.material?.unit || '') + ' x Rp ' + new Intl.NumberFormat('id-ID').format(item.price)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
