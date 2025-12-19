<x-app-layout :title="'Bahan Baku'" :breadcrumbs="[['label' => 'Master Data'], ['label' => 'Bahan Baku']]">
    <div x-data="{
        materials: [],
        categories: [],
        units: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,

        showModal: false,
        showDeleteModal: false,
        modalMode: 'create',
        formLoading: false,
        formErrors: {},
        selectedMaterial: null,

        form: {
            name: '',
            category_id: '',
            unit_id: '',
            min_stock: 0
        },

        resetForm() {
            this.form = { name: '', category_id: '', unit_id: '', min_stock: 0 };
            this.formErrors = {};
            this.selectedMaterial = null;
        },

        openCreateModal() {
            this.resetForm();
            this.modalMode = 'create';
            this.showModal = true;
        },

        openEditModal(material) {
            this.resetForm();
            this.modalMode = 'edit';
            this.selectedMaterial = material;
            this.form = {
                name: material.name,
                category_id: material.category?.id || '',
                unit_id: material.unit?.id || '',
                min_stock: material.min_stock || 0
            };
            this.showModal = true;
        },

        openDeleteModal(material) {
            this.selectedMaterial = material;
            this.showDeleteModal = true;
        },

        async saveMaterial() {
            this.formLoading = true;
            this.formErrors = {};
            $store.loading.start('Menyimpan bahan...');

            const url = this.modalMode === 'create'
                ? '/api/v1/materials'
                : `/api/v1/materials/${this.selectedMaterial.id}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    this.showModal = false;
                    this.fetchMaterials();
                } else if (response.status === 422) {
                    this.formErrors = data.errors || {};
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan');
            } finally {
                this.formLoading = false;
                $store.loading.stop();
            }
        },

        async deleteMaterial() {
            this.formLoading = true;
            $store.loading.start('Menghapus bahan...');
            try {
                const response = await fetch(`/api/v1/materials/${this.selectedMaterial.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.showDeleteModal = false;
                    this.fetchMaterials();
                } else {
                    alert(data.message || 'Tidak dapat menghapus bahan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan');
            } finally {
                this.formLoading = false;
                $store.loading.stop();
            }
        },

        async fetchMaterials() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, search: this.search });
                const response = await fetch(`/api/v1/materials?${params}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
                });
                const data = await response.json();
                this.materials = data.data;
                this.pagination = data.meta;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        async fetchDropdowns() {
            const [catRes, unitRes] = await Promise.all([
                fetch('/api/v1/material-categories', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }),
                fetch('/api/v1/units', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } })
            ]);
            this.categories = (await catRes.json()).data || [];
            this.units = (await unitRes.json()).data || [];
        },

        init() {
            this.fetchMaterials();
            this.fetchDropdowns();
            this.$watch('search', () => { this.page = 1; this.fetchMaterials(); });
        }
    }">
        <x-page-header title="Bahan Baku" description="Kelola bahan baku, stok, dan minimum stok">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Bahan
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari nama bahan...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Nama Bahan</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Kategori</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Stok</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Satuan</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Biaya Rata-rata</th>
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
                    <template x-for="material in materials" :key="material.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-info/10 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l3 1.8a2 2 0 0 0 2.06 0L12 19v-5.5l-5-3-4.03 2.42Z"/><path d="m7 16.5-4.74-2.85"/><path d="m7 16.5 5-3"/><path d="M7 16.5v5.17"/><path d="M12 13.5V19l3.97 2.38a2 2 0 0 0 2.06 0l3-1.8a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L17 10.5l-5 3Z"/><path d="m17 16.5-5-3"/><path d="m17 16.5 4.74-2.85"/><path d="M17 16.5v5.17"/><path d="M7.97 4.42A2 2 0 0 0 7 6.13v4.37l5 3 5-3V6.13a2 2 0 0 0-.97-1.71l-3-1.8a2 2 0 0 0-2.06 0l-3 1.8Z"/><path d="M12 8 7.26 5.15"/><path d="m12 8 4.74-2.85"/><path d="M12 13.5V8"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground" x-text="material.name"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info/10 text-info" x-text="material.category?.name || 'Tanpa Kategori'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex flex-col items-end gap-1">
                                    <span class="font-medium" :class="(material.stock?.quantity ?? 0) <= material.min_stock ? 'text-destructive' : 'text-foreground'" x-text="new Intl.NumberFormat('id-ID').format(material.stock?.quantity ?? 0)"></span>
                                    <div class="w-16 h-1.5 bg-muted rounded-full overflow-hidden">
                                        <div class="h-full rounded-full" :class="(material.stock?.quantity ?? 0) <= material.min_stock ? 'bg-destructive' : 'bg-success'" :style="'width: ' + Math.min(((material.stock?.quantity ?? 0) / 100) * 100, 100) + '%'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-muted-foreground" x-text="material.unit?.symbol"></td>
                            <td class="p-4 text-right font-medium text-foreground" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(material.stock?.avg_cost || 0)"></td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(material)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(material)" class="btn btn-ghost btn-icon h-8 w-8 text-destructive hover:text-destructive">
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
                <button @click="page--; fetchMaterials()" :disabled="page === 1" class="btn btn-outline btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Sebelumnya
                </button>
                <button @click="page++; fetchMaterials()" :disabled="page === pagination?.last_page" class="btn btn-outline btn-sm">
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="modalMode === 'create' ? 'Tambah Bahan Baru' : 'Edit Bahan'"></h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveMaterial()" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Nama Bahan</label>
                        <input type="text" x-model="form.name" class="input" placeholder="Masukkan nama bahan" required>
                        <p x-show="formErrors.name" class="text-destructive text-xs" x-text="formErrors.name?.[0]"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Kategori</label>
                            <select x-model="form.category_id" class="input" required>
                                <option value="">Pilih Kategori</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Satuan</label>
                            <select x-model="form.unit_id" class="input" required>
                                <option value="">Pilih Satuan</option>
                                <template x-for="unit in units" :key="unit.id">
                                    <option :value="unit.id" x-text="unit.name + ' (' + unit.symbol + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Minimum Stok</label>
                        <input type="number" x-model="form.min_stock" class="input" placeholder="0">
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading" x-text="modalMode === 'create' ? 'Tambah Bahan' : 'Simpan Perubahan'"></span>
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
                        <h3 class="text-lg font-bold text-foreground">Hapus Bahan?</h3>
                        <p class="text-sm text-muted-foreground">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-foreground mb-6">Apakah Anda yakin ingin menghapus "<span class="font-semibold" x-text="selectedMaterial?.name"></span>"?</p>
                <div class="flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="btn btn-outline btn-md">Batal</button>
                    <button @click="deleteMaterial()" :disabled="formLoading" class="btn btn-destructive btn-md">
                        <span x-show="!formLoading">Hapus</span>
                        <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
