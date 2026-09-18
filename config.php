<?php
$envDemoMode = getenv('PUBLIC_DEMO_MODE');
define('PUBLIC_DEMO_MODE', $envDemoMode !== false ? filter_var($envDemoMode, FILTER_VALIDATE_BOOLEAN) : false);

define('DB_HOST', 'localhost');
define('DB_NAME', 'siswadesk_db');
define('DB_USER', 'dev');
define('DB_PASS', 'DevPass130509@!');

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$db   = DB_NAME;

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}
?>