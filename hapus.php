<?php
// koneksi database
include 'config.php';

// hapus data
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // hapus data dari database
    $sql = "DELETE FROM tbsiswa WHERE id='$id'";
    $result = mysqli_query($conn, $sql);

    // cek apakah data berhasil dihapus
    if ($result) {
        // redirect ke halaman index.php
        header("Location: pageview.php");
        exit();
    } else {
        echo "Data gagal dihapus: " . mysqli_error($conn, $sql);
    }
}