<x-app-layout :title="'Produksi'" :breadcrumbs="[['label' => 'Produksi'], ['label' => 'Input Produksi']]">
    <div x-data="{
        productions: [],
        recipes: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,
        showModal: false,
        showDetailModal: false,
        formLoading: false,
        formErrors: {},
        selected: null,
        form: { production_date: new Date().toISOString().split('T')[0], notes: '', items: [] },

        resetForm() { this.form = { production_date: new Date().toISOString().split('T')[0], notes: '', items: [{ recipe_id: '', quantity: 1 }] }; this.formErrors = {}; },
        addItem() { this.form.items.push({ recipe_id: '', quantity: 1 }); },
        removeItem(i) { if (this.form.items.length > 1) this.form.items.splice(i, 1); },
        formatDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); },
        openCreateModal() { this.resetForm(); this.showModal = true; },
        async openDetailModal(p) {
            this.selected = p;
            this.showDetailModal = true;
            try {
                const r = await fetch(`/api/v1/productions/${p.id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                if (r.ok) { const d = await r.json(); this.selected = d.data; }
            } catch (e) { console.error(e); }
        },

        getRecipe(recipeId) { return this.recipes.find(r => r.id == recipeId); },
        onRecipeChange(idx) {
            const recipe = this.getRecipe(this.form.items[idx].recipe_id);
            if (recipe) this.form.items[idx].quantity = recipe.output_qty;
        },
        getBatchInfo(item) {
            const recipe = this.getRecipe(item.recipe_id);
            if (!recipe || !item.quantity || item.quantity <= 0) return '';
            const batches = (item.quantity / recipe.output_qty).toFixed(2);
            return `${batches} batch`;
        },

        async save() {
            this.formLoading = true;
            $store.loading.start('Memulai produksi...');
            const payload = { ...this.form, items: this.form.items.map(item => ({ recipe_id: item.recipe_id, quantity: item.quantity })) };
            try {
                const r = await fetch('/api/v1/productions', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(payload) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async updateStatus(p, s) {
            $store.loading.start('Memperbarui status...');
            try { const r = await fetch(`/api/v1/productions/${p.id}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify({ status: s }) }); if (r.ok) this.fetch(); else alert((await r.json()).message || 'Error'); } catch (e) { alert('Error'); } finally { $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const r = await fetch(`/api/v1/productions?page=${this.page}&search=${this.search}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.productions = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchRecipes() { const r = await fetch('/api/v1/recipes', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); this.recipes = (await r.json()).data || []; },
        init() { this.fetch(); this.fetchRecipes(); this.$watch('search', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Input Produksi" description="Monitor status dan output produksi">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Mulai Produksi
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari produksi...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">No. Produksi</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Tanggal</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Status</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Item</th>
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
                    <template x-for="p in productions" :key="p.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-foreground" x-text="p.production_number"></span>
                            </td>
                            <td class="p-4 text-sm text-muted-foreground" x-text="formatDate(p.production_date)"></td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-muted text-muted-foreground': p.status === 'draft',
                                        'bg-info/10 text-info': p.status === 'in_progress',
                                        'bg-success/10 text-success': p.status === 'completed',
                                        'bg-destructive/10 text-destructive': p.status === 'cancelled'
                                    }"
                                    x-text="p.status === 'draft' ? 'Draf' : p.status === 'in_progress' ? 'Berlangsung' : p.status === 'completed' ? 'Selesai' : 'Dibatalkan'"
                                ></span>
                            </td>
                            <td class="p-4 text-sm text-foreground">
                                <template x-for="i in p.items?.slice(0,2)" :key="i.id">
                                    <div x-text="(i.recipe?.product?.name || i.product?.name || 'Product') + ' (' + i.quantity + ')'"></div>
                                </template>
                                <span x-show="p.items?.length > 2" class="text-muted-foreground text-xs">+<span x-text="p.items?.length - 2"></span> lainnya</span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openDetailModal(p)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <template x-if="p.status === 'draft'">
                                        <button @click="updateStatus(p, 'in_progress')" class="btn btn-sm bg-info/10 text-info hover:bg-info/20">Mulai</button>
                                    </template>
                                    <template x-if="p.status === 'in_progress'">
                                        <button @click="updateStatus(p, 'completed')" class="btn btn-sm bg-success/10 text-success hover:bg-success/20">Selesai</button>
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
                    <h3 class="text-lg font-bold text-foreground">Mulai Produksi</h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Tanggal Produksi</label>
                        <input type="date" x-model="form.production_date" class="input" required>
                    </div>
                    <div class="border-t border-border pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-foreground">Resep yang Diproduksi</h4>
                            <button type="button" @click="addItem()" class="text-sm text-primary hover:underline">+ Tambah</button>
                        </div>
                        <template x-for="(item, idx) in form.items" :key="idx">
                            <div class="mb-3">
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-7">
                                        <select x-model="form.items[idx].recipe_id" @change="onRecipeChange(idx)" class="input text-sm" required>
                                            <option value="">Pilih Resep</option>
                                            <template x-for="rc in recipes" :key="rc.id">
                                                <option :value="rc.id" x-text="rc.name + ' → ' + rc.product?.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <input type="number" x-model="form.items[idx].quantity" min="1" class="input text-sm" placeholder="Qty" required>
                                    </div>
                                    <div class="col-span-1">
                                        <button type="button" @click="removeItem(idx)" class="btn btn-ghost btn-icon h-10 w-10 text-destructive">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 mt-1 text-xs text-muted-foreground" x-show="item.recipe_id">
                                    <span x-text="'Output/batch: ' + (getRecipe(item.recipe_id)?.output_qty || '-') + ' pcs'"></span>
                                    <span class="text-primary font-medium" x-text="getBatchInfo(item)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea x-model="form.notes" rows="2" class="input resize-none" placeholder="Catatan produksi..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading">Mulai</span>
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
                    <h3 class="text-lg font-bold text-foreground" x-text="'Produksi ' + selected?.production_number"></h3>
                    <button @click="showDetailModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Tanggal:</span>
                            <p class="font-medium text-foreground" x-text="formatDate(selected?.production_date)"></p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Status:</span>
                            <p class="font-medium text-foreground" x-text="selected?.status"></p>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <h4 class="font-semibold text-foreground mb-3">Produk</h4>
                        <template x-for="i in selected?.items" :key="i.id">
                            <div class="flex justify-between text-sm py-2 border-b border-border last:border-0">
                                <span class="text-foreground" x-text="i.recipe?.product?.name || i.product?.name || 'Product'"></span>
                                <span class="text-muted-foreground" x-text="'x' + i.quantity"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
