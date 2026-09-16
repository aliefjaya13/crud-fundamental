<body>

<?php
include 'config.php';

$id = $_GET['id'];

$sql = "SELECT * FROM tbsiswa WHERE id='$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<form action="prosesupdate.php" method="post">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

    <input type="text" name="nama" value="<?php echo $row['nama']; ?>" required>

    <input type="text" name="kelas" value="<?php echo $row['kelas']; ?>" required>

    <input type="submit" value="Update Data">
</form>

</body>