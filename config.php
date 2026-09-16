<?php
$host = "localhost";
$user = "dev";
$pass = "DevPass130509@!"; // Samakan dengan password yang Query OK tadi
$db   = "tbsiswa";         // Sesuaikan dengan nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>