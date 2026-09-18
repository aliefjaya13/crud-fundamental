<?php
require __DIR__ . '/koneksi.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=siswadesk_data_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Nama', 'Kelas', 'Status', 'Created At']);

$stmt = $pdo->query('SELECT id, nama, kelas, status, created_at FROM siswa ORDER BY created_at DESC');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['nama'],
        $row['kelas'],
        $row['status'],
        $row['created_at'],
    ]);
}

fclose($output);
exit;
