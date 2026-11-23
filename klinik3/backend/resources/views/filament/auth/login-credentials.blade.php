<div class="mb-4 rounded-xl border border-primary-500/20 bg-primary-500/5 p-4 text-sm dark:bg-primary-500/10 dark:border-primary-500/30">
    <div class="flex items-center justify-between mb-2.5">
        <div class="flex items-center gap-2 font-semibold text-primary-700 dark:text-primary-300">
            <svg class="w-4 h-4 shrink-0 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <span>Kredensial Demo Calon Klien</span>
        </div>
        <button 
            type="button" 
            onclick="fillDemoCredentials()"
            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/60 dark:text-primary-300 dark:hover:bg-primary-900 rounded-lg transition-colors cursor-pointer shadow-xs"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Isi Otomatis
        </button>
    </div>
<!-- 
    <div class="space-y-1.5 text-xs text-gray-700 dark:text-gray-200">
        <div class="flex items-center justify-between py-1.5 px-3 rounded-lg bg-white/70 dark:bg-gray-800/70 border border-gray-200/60 dark:border-gray-700/60 font-mono">
            <span class="text-gray-500 dark:text-gray-400 font-sans">Email:</span>
            <span class="font-semibold select-all text-gray-900 dark:text-gray-100">admin@kliniksenyumsehat.com</span>
        </div>
        <div class="flex items-center justify-between py-1.5 px-3 rounded-lg bg-white/70 dark:bg-gray-800/70 border border-gray-200/60 dark:border-gray-700/60 font-mono">
            <span class="text-gray-500 dark:text-gray-400 font-sans">Password:</span>
            <span class="font-semibold select-all text-gray-900 dark:text-gray-100">admin123</span>
        </div>
    </div>

    <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
        Klik <strong>Isi Otomatis</strong> atau gunakan kredensial di atas untuk masuk ke dashboard admin demo.
    </p> -->

    <script>
        function fillDemoCredentials() {
            const emailInput = document.querySelector('input[type="email"]') || document.querySelector('[name*="email"]');
            const passInput = document.querySelector('input[type="password"]') || document.querySelector('[name*="password"]');

            if (emailInput) {
                emailInput.value = 'admin@kliniksenyumsehat.com';
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (passInput) {
                passInput.value = 'admin123';
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
                passInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    </script>
</div>

