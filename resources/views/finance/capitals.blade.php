<x-app-layout :title="'Modal'" :breadcrumbs="[['label' => 'Keuangan'], ['label' => 'Modal']]">
    <div x-data="{
        capitals: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,
        showModal: false,
        showDeleteModal: false,
        modalMode: 'create',
        formLoading: false,
        formErrors: {},
        selected: null,
        form: { capital_date: new Date().toISOString().split('T')[0], amount: '', source: '', description: '' },

        resetForm() { this.form = { capital_date: new Date().toISOString().split('T')[0], amount: '', source: '', description: '' }; this.formErrors = {}; this.selected = null; },
        openCreateModal() { this.resetForm(); this.modalMode = 'create'; this.showModal = true; },
        openEditModal(c) { this.resetForm(); this.modalMode = 'edit'; this.selected = c; this.form = { capital_date: c.capital_date, amount: c.amount, source: c.source, description: c.description || '' }; this.showModal = true; },
        openDeleteModal(c) { this.selected = c; this.showDeleteModal = true; },

        async save() {
            this.formLoading = true;
            $store.loading.start('Menyimpan modal...');
            this.formErrors = {};
            const url = this.modalMode === 'create' ? '/api/v1/capitals' : `/api/v1/capitals/${this.selected.id}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';
            try {
                const r = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.form) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async remove() {
            this.formLoading = true;
            $store.loading.start('Menghapus modal...');
            try { const r = await fetch(`/api/v1/capitals/${this.selected.id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); if (r.ok) { this.showDeleteModal = false; this.fetch(); } else { const d = await r.json(); alert(d.message || 'Tidak dapat menghapus'); } } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const r = await fetch(`/api/v1/capitals?page=${this.page}&search=${this.search}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.capitals = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },
        init() { this.fetch(); this.$watch('search', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Modal Usaha" description="Pantau suntikan modal dan investasi">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Modal
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari modal...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Tanggal</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Sumber</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Jumlah</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Keterangan</th>
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
                    <template x-for="c in capitals" :key="c.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4 text-sm text-muted-foreground" x-text="c.capital_date"></td>
                            <td class="p-4">
                                <span class="font-medium text-foreground" x-text="c.source"></span>
                            </td>
                            <td class="p-4 text-right font-bold text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(c.amount)"></td>
                            <td class="p-4 text-sm text-muted-foreground max-w-xs truncate" x-text="c.description || '-'"></td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(c)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(c)" class="btn btn-ghost btn-icon h-8 w-8 text-destructive hover:text-destructive">
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
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="modalMode === 'create' ? 'Tambah Modal' : 'Edit Modal'"></h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Tanggal</label>
                        <input type="date" x-model="form.capital_date" class="input" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Jumlah</label>
                        <input type="text" x-money="form.amount" class="input" placeholder="0" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Sumber</label>
                        <input type="text" x-model="form.source" class="input" placeholder="Contoh: Investasi Pemilik, Pinjaman Bank" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Keterangan</label>
                        <textarea x-model="form.description" rows="2" class="input resize-none" placeholder="Keterangan tambahan..."></textarea>
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
                        <h3 class="text-lg font-bold text-foreground">Hapus Modal?</h3>
                        <p class="text-sm text-muted-foreground">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-foreground mb-6">Hapus modal dari "<span class="font-semibold" x-text="selected?.source"></span>"?</p>
                <div class="flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="btn btn-outline btn-md">Batal</button>
                    <button @click="remove()" :disabled="formLoading" class="btn btn-destructive btn-md">
                        <span x-show="!formLoading">Hapus</span>
                        <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
