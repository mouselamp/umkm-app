<x-app-layout :title="'Resep'" :breadcrumbs="[['label' => 'Produksi'], ['label' => 'Resep']]">
    <div x-data="{
        recipes: [],
        products: [],
        materials: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,
        showModal: false,
        showDeleteModal: false,
        showDetailModal: false,
        modalMode: 'create',
        formLoading: false,
        formErrors: {},
        selected: null,
        form: { product_id: '', name: '', output_qty: 1, items: [] },

        resetForm() { this.form = { product_id: '', name: '', output_qty: 1, items: [{ material_id: '', quantity: 1 }] }; this.formErrors = {}; this.selected = null; },
        addItem() { this.form.items.push({ material_id: '', quantity: 1 }); },
        removeItem(i) { if (this.form.items.length > 1) this.form.items.splice(i, 1); },
        openCreateModal() { this.resetForm(); this.modalMode = 'create'; this.showModal = true; },
        async openEditModal(r) {
            this.resetForm();
            this.modalMode = 'edit';
            this.selected = r;
            // Ensure dropdowns are loaded first
            if (this.materials.length === 0 || this.products.length === 0) {
                await this.fetchDropdowns();
            }
            // Show modal first, then set form values after DOM renders
            this.showModal = true;
            this.$nextTick(() => {
                this.form = {
                    product_id: String(r.product?.id || ''),
                    name: r.name,
                    output_qty: r.output_qty,
                    items: r.items?.map(i => ({ material_id: String(i.material?.id || ''), quantity: i.quantity })) || [{ material_id: '', quantity: 1 }]
                };
            });
        },
        openDeleteModal(r) { this.selected = r; this.showDeleteModal = true; },
        openDetailModal(r) { this.selected = r; this.showDetailModal = true; },

        async save() {
            this.formLoading = true;
            $store.loading.start('Menyimpan resep...');
            this.formErrors = {};
            const url = this.modalMode === 'create' ? '/api/v1/recipes' : `/api/v1/recipes/${this.selected.id}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';
            try {
                const r = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.form) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async remove() {
            this.formLoading = true;
            $store.loading.start('Menghapus resep...');
            try {
                const r = await fetch(`/api/v1/recipes/${this.selected.id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const d = await r.json();
                if (r.ok) { this.showDeleteModal = false; this.fetch(); } else { alert(d.message || 'Tidak dapat menghapus'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const r = await fetch(`/api/v1/recipes?page=${this.page}&search=${this.search}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.recipes = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchDropdowns() {
            const [pr, mr] = await Promise.all([fetch('/api/v1/products', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }), fetch('/api/v1/materials', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } })]);
            this.products = (await pr.json()).data || []; this.materials = (await mr.json()).data || [];
        },
        init() { this.fetch(); this.fetchDropdowns(); this.$watch('search', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Resep Produksi" description="Kelola resep produk dan kebutuhan bahan baku">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Resep
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari resep...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Nama Resep</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Produk</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-center">Output Qty</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-center">Bahan</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="p-8 text-center text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin mx-auto mb-2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                Memuat...
                            </td>
                        </tr>
                    </template>
                    <template x-for="r in recipes" :key="r.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-foreground cursor-pointer hover:text-primary" x-text="r.name" @click="openDetailModal(r)"></span>
                            </td>
                            <td class="p-4 text-sm text-foreground" x-text="r.product?.name || '-'"></td>
                            <td class="p-4 text-sm text-center font-medium text-foreground" x-text="r.output_qty"></td>
                            <td class="p-4 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-info/10 text-info" x-text="r.items_count + ' bahan'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDetailModal(r)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button @click="openEditModal(r)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(r)" class="btn btn-ghost btn-icon h-8 w-8 text-destructive hover:text-destructive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
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

        <!-- Create/Edit Modal -->
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="modalMode === 'create' ? 'Resep Baru' : 'Edit Resep'"></h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Nama Resep</label>
                        <input type="text" x-model="form.name" class="input" placeholder="Contoh: Siomay Ayam Standard" required>
                        <p x-show="formErrors.name" class="text-destructive text-xs" x-text="formErrors.name?.[0]"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Produk Output</label>
                            <select x-model="form.product_id" class="input" required>
                                <option value="">Pilih Produk</option>
                                <template x-for="p in products" :key="p.id">
                                    <option :value="p.id" x-text="p.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Output Qty</label>
                            <input type="number" x-model="form.output_qty" min="1" class="input" required>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-foreground">Bahan yang Diperlukan</h4>
                            <button type="button" @click="addItem()" class="text-sm text-primary hover:underline">+ Tambah Bahan</button>
                        </div>
                        <template x-for="(item, idx) in form.items" :key="idx">
                            <div class="grid grid-cols-12 gap-2 mb-2">
                                <div class="col-span-8">
                                    <select x-model="form.items[idx].material_id" class="input text-sm" required>
                                        <option value="">Pilih Bahan</option>
                                        <template x-for="m in materials" :key="m.id">
                                            <option :value="m.id" x-text="m.name + ' (' + (m.unit?.symbol || '') + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <input type="number" x-model="form.items[idx].quantity" min="0.01" step="0.01" class="input text-sm" placeholder="Qty" required>
                                </div>
                                <div class="col-span-1">
                                    <button type="button" @click="removeItem(idx)" class="btn btn-ghost btn-icon h-10 w-10 text-destructive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading" x-text="modalMode === 'create' ? 'Tambah' : 'Simpan'"></span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showDeleteModal = false" x-show="showDeleteModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-destructive/10 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-destructive"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-foreground">Hapus Resep?</h3>
                        <p class="text-sm text-muted-foreground">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-foreground mb-6">Hapus "<span class="font-semibold" x-text="selected?.name"></span>"?</p>
                <div class="flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="btn btn-outline btn-md">Batal</button>
                    <button @click="remove()" :disabled="formLoading" class="btn btn-destructive btn-md">
                        <span x-show="!formLoading">Hapus</span>
                        <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="showDetailModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showDetailModal = false" x-show="showDetailModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="selected?.name"></h3>
                    <button @click="showDetailModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Produk:</span>
                            <p class="font-medium text-foreground" x-text="selected?.product?.name || '-'"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Output Qty:</span>
                            <p class="font-medium text-primary" x-text="selected?.output_qty"></p>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <h4 class="font-semibold text-foreground mb-3">Bahan yang Diperlukan</h4>
                        <template x-for="i in selected?.items" :key="i.id">
                            <div class="flex justify-between text-sm py-2 border-b border-border last:border-0">
                                <span class="text-foreground" x-text="i.material?.name"></span>
                                <span class="text-muted-foreground" x-text="i.quantity + ' ' + (i.material?.unit || '')"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
