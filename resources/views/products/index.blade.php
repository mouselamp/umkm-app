<x-app-layout :title="'Produk'" :breadcrumbs="[['label' => 'Master Data'], ['label' => 'Produk']]">
    <div x-data="{
        products: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,

        // Modal state
        showModal: false,
        showDeleteModal: false,
        modalMode: 'create',
        formLoading: false,
        formErrors: {},
        selectedProduct: null,

        // Form data
        form: {
            name: '',
            sku: '',
            price: '',
            category: ''
        },

        resetForm() {
            this.form = { name: '', sku: '', price: '', category: '' };
            this.formErrors = {};
            this.selectedProduct = null;
        },

        openCreateModal() {
            this.resetForm();
            this.modalMode = 'create';
            this.showModal = true;
        },

        openEditModal(product) {
            this.resetForm();
            this.modalMode = 'edit';
            this.selectedProduct = product;
            this.form = {
                name: product.name,
                sku: product.sku,
                price: product.price,
                category: product.category || ''
            };
            this.showModal = true;
        },

        openDeleteModal(product) {
            this.selectedProduct = product;
            this.showDeleteModal = true;
        },

        async saveProduct() {
            this.formLoading = true;
            this.formErrors = {};
            $store.loading.start('Menyimpan produk...');

            const url = this.modalMode === 'create'
                ? '/api/v1/products'
                : `/api/v1/products/${this.selectedProduct.id}`;
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
                    this.fetchProducts();
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

        async deleteProduct() {
            this.formLoading = true;
            $store.loading.start('Menghapus produk...');
            try {
                const response = await fetch(`/api/v1/products/${this.selectedProduct.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.showDeleteModal = false;
                    this.fetchProducts();
                } else {
                    alert(data.message || 'Tidak dapat menghapus produk');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan');
            } finally {
                this.formLoading = false;
                $store.loading.stop();
            }
        },

        async fetchProducts() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.page,
                    search: this.search
                });
                const response = await fetch(`/api/v1/products?${params}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
                });
                const data = await response.json();
                this.products = data.data;
                this.pagination = data.meta;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        init() {
            this.fetchProducts();
            this.$watch('search', () => {
                this.page = 1;
                this.fetchProducts();
            });
        }
    }">
        <x-page-header title="Master Produk" description="Kelola data produk dan menu">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Produk
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Filter & Search -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input
                    x-model.debounce.500ms="search"
                    type="text"
                    class="input pl-9"
                    placeholder="Cari kode atau nama produk..."
                >
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Kode</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Nama Produk</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Kategori</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Harga</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-center">Stok</th>
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
                    <template x-for="product in products" :key="product.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4 text-sm font-mono text-muted-foreground" x-text="product.sku"></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground" x-text="product.name"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info/10 text-info" x-text="product.category || 'Tanpa Kategori'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <span class="font-medium text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></span>
                            </td>
                            <td class="p-4 text-center">
                                <span
                                    class="font-medium"
                                    :class="(product.stock?.quantity ?? 0) < 10 ? 'text-destructive' : 'text-foreground'"
                                    x-text="product.stock?.quantity ?? 0"
                                ></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(product)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(product)" class="btn btn-ghost btn-icon h-8 w-8 text-destructive hover:text-destructive">
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
                <button @click="page--; fetchProducts()" :disabled="page === 1" class="btn btn-outline btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Sebelumnya
                </button>
                <button @click="page++; fetchProducts()" :disabled="page === pagination?.last_page" class="btn btn-outline btn-sm">
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground" x-text="modalMode === 'create' ? 'Tambah Produk Baru' : 'Edit Produk'"></h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveProduct()" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Kode Produk</label>
                            <input type="text" x-model="form.sku" class="input" placeholder="PRD001" required>
                            <p x-show="formErrors.sku" class="text-destructive text-xs" x-text="formErrors.sku?.[0]"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Nama Produk</label>
                            <input type="text" x-model="form.name" class="input" placeholder="Masukkan nama produk" required>
                            <p x-show="formErrors.name" class="text-destructive text-xs" x-text="formErrors.name?.[0]"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Kategori</label>
                            <input type="text" x-model="form.category" class="input" placeholder="Makanan">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Harga (Rp)</label>
                            <input type="text" x-money="form.price" class="input" placeholder="0" required>
                            <p x-show="formErrors.price" class="text-destructive text-xs" x-text="formErrors.price?.[0]"></p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading" x-text="modalMode === 'create' ? 'Tambah Produk' : 'Simpan Perubahan'"></span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showDeleteModal = false" x-show="showDeleteModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-destructive/10 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-destructive"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-foreground">Hapus Produk?</h3>
                        <p class="text-sm text-muted-foreground">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-foreground mb-6">Apakah Anda yakin ingin menghapus produk "<span class="font-semibold" x-text="selectedProduct?.name"></span>"?</p>
                <div class="flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="btn btn-outline btn-md">Batal</button>
                    <button @click="deleteProduct()" :disabled="formLoading" class="btn btn-destructive btn-md">
                        <span x-show="!formLoading">Hapus</span>
                        <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
