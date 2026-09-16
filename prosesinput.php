<?php
// 1. Tampilkan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Hubungkan ke database
include "config.php";

// 3. Ambil data dari form (sesuaikan name di form index.php Anda)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama  = $_POST['nama'];
    $kelas = $_POST['kelas'];

    // 4. Query untuk menyimpan data
    $sql = "INSERT INTO tbsiswa (nama, kelas) VALUES ('$nama', '$kelas')";

    if (mysqli_query($conn, $sql)) {
        // 5. Jika berhasil, pindah otomatis ke pageview.php
        header("Location: pageview.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}