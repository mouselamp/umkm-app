<x-app-layout :title="'Hutang'" :breadcrumbs="[['label' => 'Keuangan'], ['label' => 'Hutang']]">
    <div x-data="{
        debts: [],
        suppliers: [],
        pagination: null,
        loading: true,
        search: '',
        page: 1,
        statusFilter: '',
        showModal: false,
        showPaymentModal: false,
        formLoading: false,
        formErrors: {},
        selected: null,
        form: { supplier_id: '', debt_type: 'payable', debt_date: new Date().toISOString().split('T')[0], amount: '', due_date: '' },
        paymentForm: { payment_date: new Date().toISOString().split('T')[0], amount: '', notes: '' },

        resetForm() { this.form = { supplier_id: '', debt_type: 'payable', debt_date: new Date().toISOString().split('T')[0], amount: '', due_date: '' }; this.formErrors = {}; },
        openCreateModal() { this.resetForm(); this.showModal = true; },
        openPaymentModal(d) { this.selected = d; this.paymentForm = { payment_date: new Date().toISOString().split('T')[0], amount: '', notes: '' }; this.showPaymentModal = true; },

        async save() {
            this.formLoading = true;
            $store.loading.start('Menyimpan hutang...');
            this.formErrors = {};
            try {
                const r = await fetch('/api/v1/debts', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.form) });
                if (r.ok) { this.showModal = false; this.fetch(); } else { const d = await r.json(); if (r.status === 422) this.formErrors = d.errors || {}; else alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async addPayment() {
            this.formLoading = true;
            $store.loading.start('Menyimpan pembayaran...');
            try {
                const r = await fetch(`/api/v1/debts/${this.selected.id}/payments`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` }, body: JSON.stringify(this.paymentForm) });
                if (r.ok) { this.showPaymentModal = false; this.fetch(); } else { const d = await r.json(); alert(d.message || 'Error'); }
            } catch (e) { alert('Terjadi kesalahan'); } finally { this.formLoading = false; $store.loading.stop(); }
        },

        async fetch() {
            this.loading = true;
            try { const params = new URLSearchParams({ page: this.page, search: this.search }); if (this.statusFilter) params.append('status', this.statusFilter); const r = await fetch(`/api/v1/debts?${params}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); const d = await r.json(); this.debts = d.data; this.pagination = d.meta; } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchSuppliers() { const r = await fetch('/api/v1/suppliers', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } }); this.suppliers = (await r.json()).data || []; },
        init() { this.fetch(); this.fetchSuppliers(); this.$watch('search', () => { this.page = 1; this.fetch(); }); this.$watch('statusFilter', () => { this.page = 1; this.fetch(); }); }
    }">
        <x-page-header title="Hutang & Piutang" description="Kelola hutang dan piutang usaha">
            <x-slot:action>
                <button @click="openCreateModal()" class="btn btn-primary btn-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Hutang
                </button>
            </x-slot:action>
        </x-page-header>

        <!-- Search & Filter -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input x-model.debounce.500ms="search" type="text" class="input pl-9" placeholder="Cari supplier...">
            </div>
            <select x-model="statusFilter" class="input w-auto min-w-32">
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Bayar</option>
                <option value="partial">Sebagian</option>
                <option value="paid">Lunas</option>
            </select>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg border bg-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-muted/50 hover:bg-muted/50">
                        <th class="p-4 text-sm font-semibold text-foreground">Tanggal</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Supplier</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Tipe</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Jumlah</th>
                        <th class="p-4 text-sm font-semibold text-foreground text-right">Sisa</th>
                        <th class="p-4 text-sm font-semibold text-foreground">Status</th>
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
                    <template x-for="d in debts" :key="d.id">
                        <tr class="hover:bg-muted/30">
                            <td class="p-4 text-sm text-muted-foreground" x-text="d.debt_date"></td>
                            <td class="p-4 text-sm font-medium text-foreground" x-text="d.supplier?.name || '-'"></td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="d.debt_type === 'payable' ? 'bg-destructive/10 text-destructive' : 'bg-info/10 text-info'"
                                    x-text="d.debt_type === 'payable' ? 'Hutang' : 'Piutang'"
                                ></span>
                            </td>
                            <td class="p-4 text-right font-medium text-foreground" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(d.amount)"></td>
                            <td class="p-4 text-right font-bold" :class="d.remaining_amount > 0 ? 'text-destructive' : 'text-success'" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(d.remaining_amount)"></td>
                            <td class="p-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-destructive/10 text-destructive': d.status === 'unpaid',
                                        'bg-warning/10 text-warning': d.status === 'partial',
                                        'bg-success/10 text-success': d.status === 'paid'
                                    }"
                                    x-text="d.status === 'unpaid' ? 'Belum Bayar' : d.status === 'partial' ? 'Sebagian' : 'Lunas'"
                                ></span>
                            </td>
                            <td class="p-4 text-right">
                                <button x-show="d.status !== 'paid'" @click="openPaymentModal(d)" class="btn btn-sm btn-primary">+ Bayar</button>
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
            <div @click.away="showModal = false" x-show="showModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground">Tambah Hutang/Piutang</h3>
                    <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="save()" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Supplier</label>
                        <select x-model="form.supplier_id" class="input">
                            <option value="">Tanpa Supplier</option>
                            <template x-for="s in suppliers" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Tipe</label>
                        <select x-model="form.debt_type" class="input" required>
                            <option value="payable">Hutang</option>
                            <option value="receivable">Piutang</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Tanggal</label>
                            <input type="date" x-model="form.debt_date" class="input" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Jatuh Tempo</label>
                            <input type="date" x-model="form.due_date" class="input">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Jumlah</label>
                        <input type="text" x-money="form.amount" class="input" placeholder="0" required>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading">Tambah</span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div @click.away="showPaymentModal = false" x-show="showPaymentModal" x-transition class="bg-card rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <h3 class="text-lg font-bold text-foreground">Catat Pembayaran</h3>
                    <button @click="showPaymentModal = false" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="addPayment()" class="p-6 space-y-4">
                    <div class="bg-muted/50 rounded-lg p-4 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Total Hutang:</span>
                            <span class="font-medium text-foreground" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selected?.amount || 0)"></span>
                        </div>
                        <div class="flex justify-between text-sm mt-2">
                            <span class="text-muted-foreground">Sisa:</span>
                            <span class="font-bold text-destructive" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selected?.remaining_amount || 0)"></span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Tanggal Bayar</label>
                        <input type="date" x-model="paymentForm.payment_date" class="input" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Jumlah</label>
                        <input type="text" x-money="paymentForm.amount" class="input" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea x-model="paymentForm.notes" rows="2" class="input resize-none" placeholder="Catatan pembayaran..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showPaymentModal = false" class="btn btn-outline btn-md">Batal</button>
                        <button type="submit" :disabled="formLoading" class="btn btn-primary btn-md">
                            <span x-show="!formLoading">Bayar</span>
                            <svg x-show="formLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
