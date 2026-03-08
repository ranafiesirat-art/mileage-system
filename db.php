<?php
// db.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "parking_system";  // nama DB parking boss – jangan tukar kalau sama

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Sambungan database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>