<x-app-layout>
    <div x-data="{
        currentTab: 'sales',
        loading: false,
        dateFrom: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        dateTo: new Date().toISOString().split('T')[0],

        // Report Data
        salesData: null,
        purchasesData: null,
        productionsData: null,
        inventoryData: null,
        profitLossData: null,

        formatCurrency(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v || 0); },

        async fetchSales() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/reports/sales?date_from=${this.dateFrom}&date_to=${this.dateTo}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const d = await r.json();
                this.salesData = d.data;
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchPurchases() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/reports/purchases?date_from=${this.dateFrom}&date_to=${this.dateTo}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const d = await r.json();
                this.purchasesData = d.data;
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchProductions() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/reports/productions?date_from=${this.dateFrom}&date_to=${this.dateTo}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const d = await r.json();
                this.productionsData = d.data;
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchInventory() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/reports/inventory`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const d = await r.json();
                this.inventoryData = d.data;
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async fetchProfitLoss() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/reports/profit-loss?date_from=${this.dateFrom}&date_to=${this.dateTo}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
                const d = await r.json();
                this.profitLossData = d.data;
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        loadReport() {
            if (this.currentTab === 'sales') this.fetchSales();
            else if (this.currentTab === 'purchases') this.fetchPurchases();
            else if (this.currentTab === 'productions') this.fetchProductions();
            else if (this.currentTab === 'inventory') this.fetchInventory();
            else if (this.currentTab === 'profitloss') this.fetchProfitLoss();
        },

        init() {
            this.loadReport();
            this.$watch('currentTab', () => this.loadReport());
        }
    }">
        <div class='flex flex-col md:flex-row justify-between items-start md:items-end gap-6 pb-8'>
            <div class='flex flex-col gap-2 max-w-2xl'><h1 class='text-text-main-light dark:text-text-main-dark text-4xl font-black leading-tight tracking-[-0.033em]'>Reports</h1><p class='text-text-sub-light dark:text-text-sub-dark text-base'>View business reports and analytics.</p></div>
        </div>

        <!-- Tabs -->
        <div class='flex flex-wrap gap-2 mb-6 border-b border-gray-200 dark:border-gray-800'>
            <button @click="currentTab = 'sales'" :class="currentTab === 'sales' ? 'border-b-2 border-primary text-primary' : 'text-text-sub-light hover:text-primary'" class='px-4 py-3 text-sm font-medium transition-colors'>Sales</button>
            <button @click="currentTab = 'purchases'" :class="currentTab === 'purchases' ? 'border-b-2 border-primary text-primary' : 'text-text-sub-light hover:text-primary'" class='px-4 py-3 text-sm font-medium transition-colors'>Purchases</button>
            <button @click="currentTab = 'productions'" :class="currentTab === 'productions' ? 'border-b-2 border-primary text-primary' : 'text-text-sub-light hover:text-primary'" class='px-4 py-3 text-sm font-medium transition-colors'>Production</button>
            <button @click="currentTab = 'inventory'" :class="currentTab === 'inventory' ? 'border-b-2 border-primary text-primary' : 'text-text-sub-light hover:text-primary'" class='px-4 py-3 text-sm font-medium transition-colors'>Inventory</button>
            <button @click="currentTab = 'profitloss'" :class="currentTab === 'profitloss' ? 'border-b-2 border-primary text-primary' : 'text-text-sub-light hover:text-primary'" class='px-4 py-3 text-sm font-medium transition-colors'>Profit/Loss</button>
        </div>

        <!-- Date Filter -->
        <div x-show="currentTab !== 'inventory'" class='flex flex-wrap gap-4 mb-6 p-4 bg-card-light dark:bg-card-dark rounded-xl border border-gray-200 dark:border-gray-800'>
            <div class='flex items-center gap-2'><label class='text-sm font-medium text-text-sub-light'>From:</label><input type='date' x-model='dateFrom' class='px-3 py-2 rounded-lg bg-background-light dark:bg-background-dark border border-gray-200 dark:border-gray-700 text-sm text-text-main-light dark:text-text-main-dark'></div>
            <div class='flex items-center gap-2'><label class='text-sm font-medium text-text-sub-light'>To:</label><input type='date' x-model='dateTo' class='px-3 py-2 rounded-lg bg-background-light dark:bg-background-dark border border-gray-200 dark:border-gray-700 text-sm text-text-main-light dark:text-text-main-dark'></div>
            <button @click='loadReport()' class='px-4 py-2 bg-primary hover:bg-emerald-500 text-white rounded-lg text-sm font-medium'>Apply Filter</button>
        </div>

        <!-- Loading -->
        <div x-show='loading' class='text-center py-12 text-text-sub-light'><span class='material-symbols-outlined animate-spin text-4xl'>progress_activity</span><p class='mt-2'>Loading report...</p></div>

        <!-- Sales Report -->
        <div x-show="currentTab === 'sales' && !loading && salesData" class='space-y-6'>
            <div class='grid grid-cols-1 md:grid-cols-4 gap-4'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Total Orders</div><div class='text-2xl font-bold text-text-main-light dark:text-text-main-dark' x-text='salesData?.summary?.total_orders || 0'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Gross Sales</div><div class='text-2xl font-bold text-primary' x-text='formatCurrency(salesData?.summary?.gross_sales)'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Net Sales</div><div class='text-2xl font-bold text-primary' x-text='formatCurrency(salesData?.summary?.net_sales)'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Unpaid</div><div class='text-2xl font-bold text-red-600' x-text='formatCurrency(salesData?.summary?.unpaid_amount)'></div></div>
            </div>
            <div class='grid grid-cols-1 lg:grid-cols-2 gap-6'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>Sales by Period</h3><template x-if="salesData?.chart_data?.length"><div class='space-y-2'><template x-for='item in salesData?.chart_data' :key='item.period'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800'><span class='text-text-sub-light' x-text='item.period'></span><span class='font-medium text-text-main-light dark:text-text-main-dark' x-text='formatCurrency(item.total_sales)'></span></div></template></div></template><template x-if="!salesData?.chart_data?.length"><p class='text-sm text-text-sub-light'>No data available</p></template></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>Top Customers</h3><template x-if="salesData?.top_customers?.length"><div class='space-y-2'><template x-for='(c, i) in salesData?.top_customers' :key='c.id'><div class='flex justify-between items-center text-sm py-2 border-b border-gray-100 dark:border-gray-800'><div class='flex items-center gap-2'><span class='w-6 h-6 rounded-full bg-primary text-white text-xs flex items-center justify-center font-bold' x-text='i+1'></span><span class='text-text-main-light dark:text-text-main-dark' x-text='c.name'></span></div><span class='font-medium text-primary' x-text='formatCurrency(c.total_purchases)'></span></div></template></div></template><template x-if="!salesData?.top_customers?.length"><p class='text-sm text-text-sub-light'>No data available</p></template></div>
            </div>
        </div>

        <!-- Purchases Report -->
        <div x-show="currentTab === 'purchases' && !loading && purchasesData" class='space-y-6'>
            <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Total Purchases</div><div class='text-2xl font-bold text-text-main-light dark:text-text-main-dark' x-text='purchasesData?.summary?.total_purchases || 0'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Total Amount</div><div class='text-2xl font-bold text-amber-600' x-text='formatCurrency(purchasesData?.summary?.total_amount)'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Credit Purchases</div><div class='text-2xl font-bold text-red-600' x-text='formatCurrency(purchasesData?.summary?.credit_purchases)'></div></div>
            </div>
            <div class='grid grid-cols-1 lg:grid-cols-2 gap-6'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>By Supplier</h3><template x-if="purchasesData?.by_supplier?.length"><div class='space-y-2'><template x-for='s in purchasesData?.by_supplier' :key='s.id'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800'><span class='text-text-main-light dark:text-text-main-dark' x-text='s.name'></span><span class='font-medium text-amber-600' x-text='formatCurrency(s.total_amount)'></span></div></template></div></template><template x-if="!purchasesData?.by_supplier?.length"><p class='text-sm text-text-sub-light'>No data available</p></template></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>By Material</h3><template x-if="purchasesData?.by_material?.length"><div class='space-y-2'><template x-for='m in purchasesData?.by_material' :key='m.id'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800'><div><span class='text-text-main-light dark:text-text-main-dark' x-text='m.name'></span><span class='text-text-sub-light text-xs ml-2' x-text="'('+m.total_qty+' qty)'"></span></div><span class='font-medium text-amber-600' x-text='formatCurrency(m.total_amount)'></span></div></template></div></template><template x-if="!purchasesData?.by_material?.length"><p class='text-sm text-text-sub-light'>No data available</p></template></div>
            </div>
        </div>

        <!-- Production Report -->
        <div x-show="currentTab === 'productions' && !loading && productionsData" class='space-y-6'>
            <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Total Produced</div><div class='text-2xl font-bold text-primary' x-text="(productionsData?.summary?.total_produced || 0) + ' units'"></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Total Production Cost</div><div class='text-2xl font-bold text-amber-600' x-text='formatCurrency(productionsData?.summary?.total_cost)'></div></div>
            </div>
            <div class='grid grid-cols-1 lg:grid-cols-2 gap-6'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>By Status</h3><template x-if="productionsData?.by_status?.length"><div class='space-y-2'><template x-for='s in productionsData?.by_status' :key='s.status'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800'><span class='inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium' :class="{'bg-gray-100 text-gray-800': s.status==='draft','bg-blue-100 text-blue-800': s.status==='in_progress','bg-green-100 text-green-800': s.status==='completed'}" x-text='s.status'></span><span class='font-medium text-text-main-light dark:text-text-main-dark' x-text='s.count'></span></div></template></div></template></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>By Product</h3><template x-if="productionsData?.by_product?.length"><div class='space-y-2'><template x-for='p in productionsData?.by_product' :key='p.id'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800'><span class='text-text-main-light dark:text-text-main-dark' x-text='p.name'></span><div class='text-right'><div class='font-medium text-primary' x-text="p.total_produced + ' pcs'"></div><div class='text-xs text-text-sub-light' x-text='formatCurrency(p.total_cost)'></div></div></div></template></div></template><template x-if="!productionsData?.by_product?.length"><p class='text-sm text-text-sub-light'>No data available</p></template></div>
            </div>
        </div>

        <!-- Inventory Report -->
        <div x-show="currentTab === 'inventory' && !loading && inventoryData" class='space-y-6'>
            <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Total Inventory Value</div><div class='text-2xl font-bold text-primary' x-text='formatCurrency(inventoryData?.total_inventory_value)'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Products Value</div><div class='text-2xl font-bold text-blue-600' x-text='formatCurrency(inventoryData?.products?.total_value)'></div></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><div class='text-sm text-text-sub-light mb-1'>Materials Value</div><div class='text-2xl font-bold text-amber-600' x-text='formatCurrency(inventoryData?.materials?.total_value)'></div><template x-if='inventoryData?.materials?.low_stock_count > 0'><div class='text-xs text-red-600 mt-1' x-text="inventoryData?.materials?.low_stock_count + ' items low stock'"></div></template></div>
            </div>
            <div class='grid grid-cols-1 lg:grid-cols-2 gap-6'>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>Products (<span x-text='inventoryData?.products?.total_items || 0'></span>)</h3><template x-if="inventoryData?.products?.items?.length"><div class='max-h-80 overflow-y-auto space-y-2'><template x-for='p in inventoryData?.products?.items' :key='p.id'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800'><div><span class='text-text-main-light dark:text-text-main-dark' x-text='p.name'></span><span class='text-text-sub-light text-xs ml-2' x-text="p.quantity + ' pcs'"></span></div><span class='font-medium text-blue-600' x-text='formatCurrency(p.value)'></span></div></template></div></template><template x-if="!inventoryData?.products?.items?.length"><p class='text-sm text-text-sub-light'>No products in stock</p></template></div>
                <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'><h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>Materials (<span x-text='inventoryData?.materials?.total_items || 0'></span>)</h3><template x-if="inventoryData?.materials?.items?.length"><div class='max-h-80 overflow-y-auto space-y-2'><template x-for='m in inventoryData?.materials?.items' :key='m.id'><div class='flex justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800' :class="m.is_low_stock ? 'bg-red-50 dark:bg-red-900/10 px-2 -mx-2 rounded' : ''"><div><span class='text-text-main-light dark:text-text-main-dark' x-text='m.name'></span><span class='text-text-sub-light text-xs ml-2' x-text="m.quantity + ' ' + m.unit"></span><span x-show='m.is_low_stock' class='text-red-600 text-xs ml-2'>LOW</span></div><span class='font-medium text-amber-600' x-text='formatCurrency(m.value)'></span></div></template></div></template><template x-if="!inventoryData?.materials?.items?.length"><p class='text-sm text-text-sub-light'>No materials in stock</p></template></div>
            </div>
        </div>

        <!-- Profit/Loss Report -->
        <div x-show="currentTab === 'profitloss' && !loading && profitLossData" class='space-y-6'>
            <div class='bg-card-light dark:bg-card-dark rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden'>
                <div class='p-6 space-y-4'>
                    <div class='flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800'><span class='text-lg text-text-main-light dark:text-text-main-dark'>Revenue (Sales)</span><span class='text-lg font-bold text-primary' x-text='formatCurrency(profitLossData?.revenue)'></span></div>
                    <div class='flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800'><span class='text-text-sub-light'>Less: Cost of Goods Sold</span><span class='text-red-600' x-text="'(' + formatCurrency(profitLossData?.cogs) + ')'"></span></div>
                    <div class='flex justify-between items-center py-3 bg-gray-50 dark:bg-gray-800 -mx-6 px-6'><span class='text-lg font-semibold text-text-main-light dark:text-text-main-dark'>Gross Profit</span><span class='text-lg font-bold' :class="profitLossData?.gross_profit >= 0 ? 'text-primary' : 'text-red-600'" x-text='formatCurrency(profitLossData?.gross_profit)'></span></div>
                    <div class='flex justify-between items-center py-3'><span class='text-text-sub-light'>Gross Margin</span><span class='font-medium text-text-main-light dark:text-text-main-dark' x-text="(profitLossData?.gross_margin || 0) + '%'"></span></div>
                </div>
            </div>
            <div class='bg-card-light dark:bg-card-dark rounded-xl p-6 border border-gray-200 dark:border-gray-800'>
                <h3 class='font-semibold text-text-main-light dark:text-text-main-dark mb-4'>Additional Info</h3>
                <div class='flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800'><span class='text-text-sub-light'>Total Purchase Cost (Materials)</span><span class='font-medium text-amber-600' x-text='formatCurrency(profitLossData?.purchase_cost)'></span></div>
            </div>
        </div>
    </div>
</x-app-layout>
