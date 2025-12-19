<x-app-layout :title="'Gaji'" :breadcrumbs="[['label' => 'Keuangan'], ['label' => 'Gaji']]">
    <div x-data="{
        wages: [],
        employees: [],
        paymentMethods: [],
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
        form: { employee_id: '', wage_date: new Date().toISOString().split('T')[0], amount: '', wage_type: 'daily', payment_method_id: '', payment_status: 'pending', notes: '' },

        resetForm() { this.form = { employee_id: '', wage_date: new Date().toISOString().split('T')[0], amount: '', wage_type: 'daily', payment_method_id: '', payment_status: 'pending', notes: '' }; this.formErrors = {}; this.selected = null; },
        openCreateModal() { this.resetForm(); this.modalMode = 'create'; this.showModal = true; },
        openEditModal(w) { this.resetForm(); this.modalMode = 'edit'; this.selected = w; this.form = { employee_id: w.employee_id, wage_date: w.wage_date, amount: w.amount, wage_type: w.wage_type, payment_method_id: w.payment_method?.id || '', payment_status: w.payment_status, notes: w.notes || '' }; this.showModal = true; },
        openDeleteModal(w) { this.selected = w; this.showDeleteModal = true; },

        async save() {
            this.formLoading = true;
            $store.loading.start('Menyimpan gaji...');
            this.formErrors = {};
            const url = this.modalMode === 'create' ? '/api/v1/wages' : `/api/v1/wages/${this.selected.id}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';
            try {
                const r = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.form) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async remove() {
            this.formLoading = true;
            $store.loading.start('Menghapus gaji...');
            try { const r = await fetch(`/api/v1/wages/${this.selected.id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); if (r.ok) { this.showDeleteModal = false; this.fetch(); } else { const d = await r.json(); alert(d.message || 'Tidak dapat menghapus'); } } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const r = await fetch(`/api/v1/wages?page=${this.page}&search=${this.search}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.wages = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchDropdowns() {
            const [er, pr] = await Promise.all([fetch('/api/v1/employees', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }), fetch('/api/v1/payment-methods', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } })]);
            this.employees = (await er.json()).data || []; this.paymentMethods = (await pr.json()).data || [];
        },
        init() { this.fetch(); this.fetchDropdowns(); this.$watch('search', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Gaji Karyawan" description="Kelola gaji dan pembayaran karyawan">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Gaji
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari berdasarkan karyawan...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Tanggal</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Karyawan</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Tipe</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Jumlah</th>
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
                    <template x-for="w in wages" :key="w.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4 text-sm text-muted-foreground" x-text="w.wage_date"></td>
                            <td class="p-4 text-sm font-medium text-foreground" x-text="w.employee?.name || '-'"></td>
                            <td class="p-4">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-info/10 text-info" x-text="w.wage_type === 'daily' ? 'Harian' : w.wage_type === 'weekly' ? 'Mingguan' : w.wage_type === 'monthly' ? 'Bulanan' : 'Bonus'"></span>
                            </td>
                            <td class="p-4 text-right font-bold text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(w.amount)"></td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="w.payment_status === 'paid' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'"
                                    x-text="w.payment_status === 'paid' ? 'Dibayar' : 'Tertunda'"
                                ></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(w)" class="btn btn-ghost btn-icon h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(w)" class="btn btn-ghost btn-icon h-8 w-8 text-destructive hover:text-destructive">
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
                    <h3 class="text-lg font-bold text-foreground" x-text="modalMode === 'create' ? 'Tambah Gaji' : 'Edit Gaji'"></h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Karyawan</label>
                        <select x-model="form.employee_id" class="input" required>
                            <option value="">Pilih Karyawan</option>
                            <template x-for="e in employees" :key="e.id">
                                <option :value="e.id" x-text="e.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Tanggal</label>
                            <input type="date" x-model="form.wage_date" class="input" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Tipe</label>
                            <select x-model="form.wage_type" class="input" required>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="bonus">Bonus</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Jumlah</label>
                        <input type="text" x-money="form.amount" class="input" placeholder="0" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Metode Bayar</label>
                            <select x-model="form.payment_method_id" class="input">
                                <option value="">Tunai</option>
                                <template x-for="pm in paymentMethods" :key="pm.id">
                                    <option :value="pm.id" x-text="pm.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Status</label>
                            <select x-model="form.payment_status" class="input" required>
                                <option value="pending">Tertunda</option>
                                <option value="paid">Dibayar</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea x-model="form.notes" rows="2" class="input resize-none" placeholder="Catatan tambahan..."></textarea>
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
                        <h3 class="text-lg font-bold text-foreground">Hapus Gaji?</h3>
                        <p class="text-sm text-muted-foreground">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-foreground mb-6">Hapus gaji untuk "<span class="font-semibold" x-text="selected?.employee?.name"></span>"?</p>
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
