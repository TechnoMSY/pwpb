<?php
if (!isset($page)) { die("Direct access strictly prohibited."); }
?>
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4 relative overflow-hidden">
    <div class="absolute w-72 h-72 bg-blue-400/10 rounded-full blur-3xl -z-10 animate-pulse"></div>
    
    <div class="relative select-none pointer-events-none">
        <h1 class="font-heading text-9xl font-black text-slate-200/60 tracking-tighter sm:text-[13rem]">404</h1>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="material-symbols-outlined text-7xl text-accent animate-bounce drop-shadow-[0_10px_15px_rgba(37,99,235,0.3)]">
                running_with_errors
            </span>
        </div>
    </div>

    <div class="mt-4 max-w-md space-y-2 relative z-10">
        <h2 class="font-heading text-xl font-bold text-primary">Halaman Belum Tersedia</h2>
        <p class="text-xs text-text-muted font-light leading-relaxed">
            Modul <code class="bg-slate-100 text-accent px-1.5 py-0.5 rounded font-mono text-[11px]"><?php echo htmlspecialchars($page); ?>.php</code> sedang dalam tahap optimalisasi konstruksi arsitektur data atau tautan menu navigasi belum dikonfigurasi sepenuhnya.
        </p>
    </div>

    <div class="mt-8">
        <a href="dashboard.php?page=dashboard" class="inline-flex items-center gap-2 bg-primary text-white text-xs font-bold font-heading uppercase tracking-wider px-5 py-3 rounded-xl shadow-md hover:bg-accent transition-all duration-300">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali Ke Dasbor
        </a>
    </div>
</div>