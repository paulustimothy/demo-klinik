<div class="mb-4 rounded-xl border border-primary-500/20 bg-primary-500/5 p-4 text-sm dark:bg-primary-500/10 dark:border-primary-500/30">
    <div class="flex items-center justify-between mb-2.5">
        <div class="flex items-center gap-2 font-semibold text-primary-700 dark:text-primary-300">
            <svg class="w-4 h-4 shrink-0 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <span>Kredensial Demo Akses</span>
        </div>
        <div class="flex items-center gap-1.5">
            <button 
                type="button" 
                onclick="fillRoleCredentials('admin@kliniksenyumsehat.com', 'admin123')"
                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/60 dark:text-primary-300 dark:hover:bg-primary-900 rounded-lg transition-colors cursor-pointer shadow-xs"
                title="Masuk sebagai Administrator (Semua Akses)"
            >
                👑 Admin
            </button>
            <button 
                type="button" 
                onclick="fillRoleCredentials('resepsionis@kliniksenyumsehat.com', 'admin123')"
                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/60 dark:text-emerald-300 dark:hover:bg-emerald-900 rounded-lg transition-colors cursor-pointer shadow-xs"
                title="Masuk sebagai Resepsionis (Operasional Booking & Pasien)"
            >
                📋 Resepsionis
            </button>
        </div>
    </div>

    <script>
        function fillRoleCredentials(email, pass) {
            const emailInput = document.querySelector('input[type="email"]') || document.querySelector('[name*="email"]');
            const passInput = document.querySelector('input[type="password"]') || document.querySelector('[name*="password"]');

            if (emailInput) {
                emailInput.value = email;
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (passInput) {
                passInput.value = pass;
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
                passInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    </script>
</div>
