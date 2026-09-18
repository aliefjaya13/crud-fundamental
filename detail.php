<?php
require __DIR__ . '/koneksi.php';

if (!PUBLIC_DEMO_MODE && empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM siswa WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Siswa | SMEXAPRO</title>
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
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-red-200 bg-white p-1 shadow-md dark:border-red-500/30 dark:bg-slate-900">
                    <img src="assets/img/logo.png" alt="Logo sekolah" class="h-10 w-10 object-contain">
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-red-500 dark:text-red-300">Detail Siswa</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">SMEXAPRO</h1>
                </div>
            </div>
            <a href="index.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.1fr_2fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-premium dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-2xl font-bold text-white">
                        <?php echo htmlspecialchars(strtoupper(substr($siswa['nama'], 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold"><?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <span class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset <?php echo ($siswa['status'] === 'Aktif') ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-red-100 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-300'; ?>">
                        <?php echo htmlspecialchars($siswa['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-premium dark:border-slate-800 dark:bg-slate-900">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">ID Siswa</p>
                        <p class="mt-2 text-xl font-bold">#<?php echo (int) $siswa['id']; ?></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Kelas</p>
                        <p class="mt-2 text-xl font-bold"><?php echo htmlspecialchars($siswa['kelas'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70 md:col-span-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Status Keaktifan</p>
                        <p class="mt-2 text-xl font-bold"><?php echo htmlspecialchars($siswa['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70 md:col-span-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Tanggal Ditambahkan</p>
                        <p class="mt-2 text-xl font-bold"><?php echo date('d M Y, H:i', strtotime($siswa['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
