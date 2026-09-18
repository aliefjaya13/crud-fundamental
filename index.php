<?php
require __DIR__ . '/koneksi.php';

if (!PUBLIC_DEMO_MODE && empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$kelasFilter = $_GET['kelas'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$perPage = 8;
$page = max(1, (int) ($_GET['page'] ?? 1));

$statsSql = 'SELECT COUNT(*) AS total_siswa, SUM(CASE WHEN status = "Aktif" THEN 1 ELSE 0 END) AS siswa_aktif, COUNT(DISTINCT kelas) AS total_kelas FROM siswa';
$statsStmt = $pdo->query($statsSql);
$stats = $statsStmt->fetch();

$countSql = 'SELECT COUNT(*) AS total FROM siswa WHERE 1=1';
$countParams = [];

if ($search !== '') {
    $countSql .= ' AND nama LIKE :search';
    $countParams[':search'] = '%' . $search . '%';
}

if ($kelasFilter !== '') {
    $countSql .= ' AND kelas = :kelas';
    $countParams[':kelas'] = $kelasFilter;
}

if ($statusFilter !== '') {
    $countSql .= ' AND status = :status';
    $countParams[':status'] = $statusFilter;
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRecords / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = 'SELECT * FROM siswa WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND nama LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

if ($kelasFilter !== '') {
    $sql .= ' AND kelas = :kelas';
    $params[':kelas'] = $kelasFilter;
}

if ($statusFilter !== '') {
    $sql .= ' AND status = :status';
    $params[':status'] = $statusFilter;
}

$sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$siswaList = $stmt->fetchAll();

$kelasList = $pdo->query('SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC')->fetchAll(PDO::FETCH_COLUMN);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" class="scroll-smooth dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMEXAPRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    boxShadow: {
                        premium: '0 20px 45px -20px rgba(15, 23, 42, 0.35)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(248,250,252,0.1),transparent_20%),linear-gradient(180deg,#f8fafc_0%,#eef2ff_100%)] dark:bg-[radial-gradient(circle_at_top,_rgba(248,250,252,0.08),transparent_20%),linear-gradient(180deg,#020617_0%,#0f172a_100%)]">
        <nav class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-red-200 bg-white shadow-md shadow-red-200/50 dark:border-red-500/30 dark:bg-slate-900">
                        <img src="assets/img/logo.png" alt="Logo sekolah" class="h-10 w-10 object-contain">
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-red-600 dark:text-red-300">SMK Negeri 1 Probolinggo</p>
                        <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">SMEXAPRO</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <?php if (!PUBLIC_DEMO_MODE): ?>
                        <div class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 sm:flex">
                            <i class="fa-solid fa-user-shield text-indigo-500"></i>
                            <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                    <button id="themeToggle" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <i class="fa-solid fa-sun mr-2"></i>
                        <span id="themeLabel">Light</span>
                    </button>
                    <?php if (!PUBLIC_DEMO_MODE): ?>
                        <a href="logout.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:border-red-200 hover:text-red-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </a>
                    <?php endif; ?>
                    <?php if (!PUBLIC_DEMO_MODE): ?>
                        <button id="openCreateModal" type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:translate-y-[-1px] hover:shadow-xl">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Siswa
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="relative mb-8 overflow-hidden rounded-[28px] border border-red-200 bg-gradient-to-r from-red-700 via-red-600 to-amber-500 p-6 text-white shadow-2xl shadow-red-900/20 dark:border-red-500/30 dark:from-red-950 dark:via-red-900 dark:to-amber-700">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.18),transparent_16%),radial-gradient(circle_at_80%_20%,rgba(255,255,255,0.12),transparent_18%),radial-gradient(circle_at_50%_80%,rgba(255,255,255,0.08),transparent_22%)]"></div>
                <div class="absolute -right-10 -top-8 opacity-15">
                    <img src="assets/img/logo.png" alt="Watermark logo sekolah" class="h-40 w-40 object-contain sm:h-52 sm:w-52">
                </div>
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.35em] text-red-100">Sistem Manajemen Siswa</p>
                        <h2 class="text-3xl font-black tracking-tight sm:text-4xl">SMEXAPRO</h2>
                        <p class="mt-3 max-w-xl text-sm text-red-50/90 sm:text-base">
                            Dashboard digital untuk pengelolaan data siswa dan kegiatan administrasi sekolah SMK Negeri 1 Probolinggo.
                        </p>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="rounded-3xl border border-white/20 bg-white/10 p-3 shadow-inner backdrop-blur-sm">
                            <img src="assets/img/logo.png" alt="Logo sekolah" class="h-24 w-24 object-contain sm:h-28 sm:w-28">
                        </div>
                    </div>
                </div>
            </section>

            <section class="mb-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-premium backdrop-blur-sm transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900/90">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Siswa</p>
                            <h2 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white"><?php echo (int) ($stats['total_siswa'] ?? 0); ?></h2>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-500/20">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        <span>Data konsisten dan terupdate</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-premium backdrop-blur-sm transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900/90">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Siswa Aktif</p>
                            <h2 class="mt-3 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400"><?php echo (int) ($stats['siswa_aktif'] ?? 0); ?></h2>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-lime-500 text-white shadow-lg shadow-emerald-500/20">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span>Keaktifan siswa terpantau jelas</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-premium backdrop-blur-sm transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900/90">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kelas</p>
                            <h2 class="mt-3 text-3xl font-extrabold text-violet-600 dark:text-violet-400"><?php echo (int) ($stats['total_kelas'] ?? 0); ?></h2>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-500/20">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-violet-600 dark:text-violet-400">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Kelas terorganisir dalam satu dashboard</span>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-premium backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Daftar Siswa</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola data peserta didik secara cepat dan aman.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari nama siswa..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20 sm:w-56">
                            </div>

                            <select name="kelas" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelasList as $kelas): ?>
                                    <option value="<?php echo htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($kelasFilter === $kelas) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20">
                                <option value="">Semua Status</option>
                                <option value="Aktif" <?php echo ($statusFilter === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="Non-Aktif" <?php echo ($statusFilter === 'Non-Aktif') ? 'selected' : ''; ?>>Non-Aktif</option>
                            </select>

                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                                <i class="fa-solid fa-filter mr-2"></i>Filter
                            </button>
                        </form>

                        <a href="index.php" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <i class="fa-solid fa-rotate-right mr-2"></i>Reset
                        </a>

                        <a href="export.php" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg shadow-emerald-500/25 transition hover:translate-y-[-1px] hover:shadow-xl">
                            <i class="fa-solid fa-file-csv mr-2"></i>Export CSV
                        </a>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 shadow-inner dark:border-slate-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">Siswa</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">Kelas</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">Dibuat</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                <?php if (empty($siswaList)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i class="fa-solid fa-box-open text-2xl"></i>
                                                <span>Belum ada data siswa yang sesuai dengan filter.</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($siswaList as $siswa): ?>
                                        <?php
                                            $inisial = strtoupper(substr($siswa['nama'], 0, 2));
                                            $statusClass = ($siswa['status'] === 'Aktif')
                                                ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300'
                                                : 'bg-red-100 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-300';
                                        ?>
                                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white shadow-md">
                                                        <?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">ID: #<?php echo (int) $siswa['id']; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($siswa['kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($siswa['status'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"><?php echo date('d M Y', strtotime($siswa['created_at'])); ?></td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="detail.php?id=<?php echo (int) $siswa['id']; ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" title="Detail">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <?php if (!PUBLIC_DEMO_MODE): ?>
                                                        <button type="button" class="edit-btn inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" data-id="<?php echo (int) $siswa['id']; ?>" data-nama="<?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?>" data-kelas="<?php echo htmlspecialchars($siswa['kelas'], ENT_QUOTES, 'UTF-8'); ?>" data-status="<?php echo htmlspecialchars($siswa['status'], ENT_QUOTES, 'UTF-8'); ?>" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </button>
                                                        <button type="button" class="delete-btn inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300" data-id="<?php echo (int) $siswa['id']; ?>" title="Hapus">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-5 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:flex-row">
                    <p>Menampilkan <?php echo count($siswaList); ?> data dari total <?php echo $totalRecords; ?></p>
                    <div class="flex items-center gap-2">
                        <?php
                            $baseQuery = http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)]));
                            $nextQuery = http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)]));
                        ?>
                        <a href="index.php?<?php echo $baseQuery; ?>" class="rounded-xl border border-slate-200 bg-white px-3 py-2 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 <?php echo ($page <= 1) ? 'pointer-events-none opacity-50' : ''; ?>">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800">Halaman <?php echo $page; ?> / <?php echo $totalPages; ?></span>
                        <a href="index.php?<?php echo $nextQuery; ?>" class="rounded-xl border border-slate-200 bg-white px-3 py-2 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 <?php echo ($page >= $totalPages) ? 'pointer-events-none opacity-50' : ''; ?>">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="modalBackdrop" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_30px_80px_-20px_rgba(15,23,42,0.55)] dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-500 dark:text-red-300">Form Siswa</p>
                    <h3 id="modalTitle" class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">Tambah Siswa</h3>
                </div>
                <button type="button" id="closeModal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:text-slate-700 dark:border-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="siswaForm" method="POST" action="proses.php">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="siswaId" value="">

                <div class="space-y-5">
                    <div>
                        <label for="nama" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Lengkap</label>
                        <input id="nama" name="nama" type="text" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20" placeholder="Masukkan nama siswa">
                    </div>

                    <div>
                        <label for="kelas" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Kelas</label>
                        <input id="kelas" name="kelas" type="text" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20" placeholder="Contoh: XII RPL">
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
                        <select id="status" name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20">
                            <option value="Aktif">Aktif</option>
                            <option value="Non-Aktif">Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" id="cancelModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-red-600 to-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-500/30 transition hover:translate-y-[-1px]">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalTitle = document.getElementById('modalTitle');
        const siswaForm = document.getElementById('siswaForm');
        const formAction = document.getElementById('formAction');
        const siswaId = document.getElementById('siswaId');
        const namaInput = document.getElementById('nama');
        const kelasInput = document.getElementById('kelas');
        const statusInput = document.getElementById('status');

        const openCreateModal = () => {
            modalTitle.textContent = 'Tambah Siswa';
            formAction.value = 'create';
            siswaId.value = '';
            siswaForm.reset();
            statusInput.value = 'Aktif';
            modalBackdrop.classList.remove('hidden');
            modalBackdrop.classList.add('flex');
            namaInput.focus();
        };

        const openEditModal = (button) => {
            modalTitle.textContent = 'Edit Siswa';
            formAction.value = 'update';
            siswaId.value = button.dataset.id;
            namaInput.value = button.dataset.nama;
            kelasInput.value = button.dataset.kelas;
            statusInput.value = button.dataset.status;
            modalBackdrop.classList.remove('hidden');
            modalBackdrop.classList.add('flex');
            namaInput.focus();
        };

        const closeModal = () => {
            modalBackdrop.classList.add('hidden');
            modalBackdrop.classList.remove('flex');
            siswaForm.reset();
        };

        document.getElementById('openCreateModal').addEventListener('click', openCreateModal);
        document.getElementById('closeModal').addEventListener('click', closeModal);
        document.getElementById('cancelModal').addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) closeModal();
        });

        document.querySelectorAll('.edit-btn').forEach((button) => {
            button.addEventListener('click', () => openEditModal(button));
        });

        document.querySelectorAll('.delete-btn').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: 'Data siswa ini akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'proses.php?action=delete&id=' + id;
                    }
                });
            });
        });

        const themeToggle = document.getElementById('themeToggle');
        const root = document.documentElement;

        function applyTheme(theme) {
            const isDark = theme === 'dark';
            root.classList.toggle('dark', isDark);
            const label = document.getElementById('themeLabel');
            if (label) {
                label.textContent = isDark ? 'Light' : 'Dark';
            }
            themeToggle.innerHTML = isDark
                ? '<i class="fa-solid fa-sun mr-2"></i><span id="themeLabel">Light</span>'
                : '<i class="fa-solid fa-moon mr-2"></i><span id="themeLabel">Dark</span>';
            localStorage.setItem('theme', theme);
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            applyTheme('light');
        } else {
            applyTheme('dark');
        }

        themeToggle.addEventListener('click', () => {
            const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
            applyTheme(nextTheme);
        });

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
