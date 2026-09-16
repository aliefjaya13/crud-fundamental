<?php
$host = "localhost";
$user = "dev";
$pass = "DevPass130509!";
$db   = "tbsiswa"; // Sesuaikan nama database Anda

$sconn = mysqli_connect($host, $user, $pass, $db);

if ($sconn) {
    echo "Koneksi Berhasil!";
} else {
    echo "Koneksi Gagal: " . mysqli_connect_error();
}
?>