<!-- Loading Overlay Component -->
<div x-show="$store.loading.show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm"
     style="display: none;">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl p-6 flex flex-col items-center gap-4 min-w-[200px]">
        <div class="relative">
            <div class="w-12 h-12 rounded-full border-4 border-gray-200 dark:border-gray-700"></div>
            <div class="w-12 h-12 rounded-full border-4 border-primary border-t-transparent animate-spin absolute top-0 left-0"></div>
        </div>
        <p class="text-text-main-light dark:text-text-main-dark font-medium" x-text="$store.loading.message || 'Menyimpan data...'"></p>
    </div>
</div>
