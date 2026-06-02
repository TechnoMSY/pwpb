<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login Sistem Informasi Madrasah (SIM) | MTs Al-Hikmah Keo Tengah</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0B1523",
                        "primary-light": "#17263D",
                        "accent": "#2563EB",
                        "accent-hover": "#1D4ED8",
                        "bg-base": "#F8FAFC",
                        "bg-surface": "#FFFFFF",
                        "text-main": "#0F172A",
                        "text-muted": "#64748B"
                    },
                    fontFamily: {
                        heading: ["Plus Jakarta Sans", "sans-serif"],
                        sans: ["Plus Jakarta Sans", "sans-serif"],
                    }
                },
            },
        }
    </script>
</head>

<body class="bg-bg-base text-text-main antialiased min-h-screen flex items-center justify-center p-4 sm:p-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-primary/10 mb-4">
                <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
            </div>
            <h2 class="text-2xl font-extrabold text-primary tracking-tight font-heading">Sistem Informasi Manajemen</h2>
            <p class="text-xs text-text-muted mt-1.5 font-normal">MTs Al-Hikmah Keo Tengah • Panel Administrator</p>
        </div>

        <div class="bg-bg-surface p-8 sm:p-10 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40">
            
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-100 text-red-800 p-4 rounded-xl text-xs flex items-start gap-2.5 animate-fade-in">
                    <span class="material-symbols-outlined text-base text-red-600 shrink-0 mt-0.5">error</span>
                    <div class="font-normal"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['message'])): ?>
                <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-800 p-4 rounded-xl text-xs flex items-start gap-2.5 animate-fade-in">
                    <span class="material-symbols-outlined text-base text-emerald-600 shrink-0 mt-0.5">check_circle</span>
                    <div class="font-normal"><?php echo htmlspecialchars(urldecode($_GET['message'])); ?></div>
                </div>
            <?php endif; ?>

            <form action="proses-login.php" method="POST" class="space-y-5 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5" for="username">Nama Pengguna / Username</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
                        <input type="text" id="username" name="username" required placeholder="Masukkan username Anda..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent/10 focus:border-accent transition-all text-sm font-normal text-primary">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block font-bold text-slate-700" for="password">Kata Sandi / Password</label>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent/10 focus:border-accent transition-all text-sm font-normal text-primary">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors flex items-center justify-center focus:outline-none" title="Lihat Sandi">
                            <span id="password-icon" class="material-symbols-outlined text-lg">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-primary text-white py-3.5 rounded-xl font-bold hover:bg-primary-light transition-all duration-300 shadow-md flex items-center justify-center gap-2 cursor-pointer uppercase tracking-wider text-xs">
                        <span class="material-symbols-outlined text-base">login</span> Masuk ke Sistem
                    </button>
                </div>
            </form>

            <div class="mt-10 pt-6 border-t border-slate-50 text-center">
                <p class="text-xs text-text-muted font-normal">
                    Lupa akses login atau akun terkunci? <br class="sm:hidden">
                    <a href="index.php#kontak" class="text-accent font-bold hover:underline inline-flex items-center gap-0.5 mt-1">
                        Hubungi Operator Madrasah <span class="material-symbols-outlined text-xs">arrow_outward</span>
                    </a>
                </p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="index.php" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-text-muted hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Beranda Utama
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const passwordIcon = document.getElementById("password-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.textContent = "visibility_off";
            } else {
                passwordInput.type = "password";
                passwordIcon.textContent = "visibility";
            }
        }
    </script>

</body>
</html>