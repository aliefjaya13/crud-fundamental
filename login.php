<?php
require __DIR__ . '/koneksi.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SMEXAPRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.12),transparent_25%),linear-gradient(135deg,#7f1d1d_0%,#991b1b_20%,#111827_55%,#0f172a_100%)] px-4">
        <div class="pointer-events-none absolute inset-0 opacity-30">
            <div class="absolute left-[-5%] top-[-8%] h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="absolute bottom-[-10%] right-[-4%] h-72 w-72 rounded-full bg-amber-400/20 blur-3xl"></div>
        </div>
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-red-400/20 bg-white/5 shadow-2xl shadow-red-900/30 backdrop-blur-xl">
            <div class="border-b border-white/10 bg-gradient-to-r from-red-700/30 to-amber-500/20 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-lg shadow-red-900/20">
                        <img src="assets/img/logo.png" alt="Logo sekolah" class="h-11 w-11 object-contain">
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.3em] text-red-100">Access Portal</p>
                        <h1 class="text-2xl font-black tracking-tight text-white">SMEXAPRO</h1>
                        <p class="text-xs text-red-100/90">SMK Negeri 1 Probolinggo</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-7">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold">Masuk ke Dashboard</h2>
                    <p class="mt-2 text-sm text-slate-300">Gunakan akun admin untuk mengelola data siswa.</p>
                </div>

                <form method="POST" action="proses.php" class="space-y-5">
                    <input type="hidden" name="action" value="login">

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-slate-200">Username</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-solid fa-user"></i></span>
                            <input id="username" name="username" type="text" required class="w-full rounded-xl border border-white/10 bg-slate-900/60 py-3 pl-10 pr-3 text-slate-100 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20" placeholder="admin">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                            <input id="password" name="password" type="password" required class="w-full rounded-xl border border-white/10 bg-slate-900/60 py-3 pl-10 pr-3 text-slate-100 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20" placeholder="admin123">
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-red-600 to-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-red-500/30 transition hover:translate-y-[-1px]">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>
                        Masuk
                    </button>
                </form>

                <div class="mt-6 rounded-2xl border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-100">
                    <p class="font-medium">Demo admin</p>
                    <p class="mt-1 text-red-200/90">Username: admin</p>
                    <p class="text-red-200/90">Password: admin123</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        <?php if ($flash): ?>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: '<?php echo htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8'); ?>',
                    title: '<?php echo htmlspecialchars($flash['title'], ENT_QUOTES, 'UTF-8'); ?>',
                    text: '<?php echo htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2200,
                    timerProgressBar: true,
                });
            });
        <?php endif; ?>
    </script>
</body>
</html>
