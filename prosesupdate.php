<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];

    $sql = "UPDATE tbsiswa SET nama='$nama', kelas='$kelas' WHERE id='$id'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: pageview.php");
        exit();
    } else {
        echo "Update gagal: " . mysqli_error($conn);
    }
}
?>