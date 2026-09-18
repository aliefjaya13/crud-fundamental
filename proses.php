<?php
require __DIR__ . '/koneksi.php';

function flash(string $type, string $title, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'title' => $title,
        'message' => $message,
    ];
}

$action = $_REQUEST['action'] ?? null;

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        flash('error', 'Login Gagal', 'Username dan password wajib diisi.');
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, username, password_hash, nama_lengkap FROM admin_users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $admin['nama_lengkap'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php');
        exit;
    }

    flash('error', 'Login Gagal', 'Username atau password salah.');
    header('Location: login.php');
    exit;
}

if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!isset($_REQUEST['action'])) {
    header('Location: index.php');
    exit;
}

if (in_array($action, ['create', 'update', 'delete'], true) && !PUBLIC_DEMO_MODE && empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error', 'Akses Ditolak', 'Permintaan tidak valid.');
            header('Location: index.php');
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $status = $_POST['status'] ?? 'Aktif';

        if ($nama === '' || $kelas === '') {
            flash('error', 'Validasi Gagal', 'Nama dan kelas harus diisi.');
            header('Location: index.php');
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO siswa (nama, kelas, status) VALUES (:nama, :kelas, :status)');
        $stmt->execute([
            ':nama' => $nama,
            ':kelas' => $kelas,
            ':status' => in_array($status, ['Aktif', 'Non-Aktif'], true) ? $status : 'Aktif',
        ]);

        flash('success', 'Berhasil', 'Data siswa berhasil ditambahkan.');
        header('Location: index.php');
        exit;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error', 'Akses Ditolak', 'Permintaan tidak valid.');
            header('Location: index.php');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $status = $_POST['status'] ?? 'Aktif';

        if (!$id || $nama === '' || $kelas === '') {
            flash('error', 'Validasi Gagal', 'Data tidak valid untuk diperbarui.');
            header('Location: index.php');
            exit;
        }

        $stmt = $pdo->prepare('UPDATE siswa SET nama = :nama, kelas = :kelas, status = :status WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':nama' => $nama,
            ':kelas' => $kelas,
            ':status' => in_array($status, ['Aktif', 'Non-Aktif'], true) ? $status : 'Aktif',
        ]);

        flash('success', 'Diperbarui', 'Data siswa berhasil diperbarui.');
        header('Location: index.php');
        exit;

    case 'delete':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            flash('error', 'Gagal', 'ID siswa tidak valid.');
            header('Location: index.php');
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM siswa WHERE id = :id');
        $stmt->execute([':id' => $id]);

        flash('success', 'Dihapus', 'Data siswa berhasil dihapus.');
        header('Location: index.php');
        exit;

    default:
        flash('error', 'Akses Ditolak', 'Aksi tidak dikenal.');
        header('Location: index.php');
        exit;
}
