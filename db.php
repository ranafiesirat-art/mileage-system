<?php
$host = "localhost";
$user = "root";           // default XAMPP
$pass = "";               // default kosong
$dbname = "parking_system";   // GANTI dengan nama database parking boss (contoh: db_parking, parking_system dll)

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Sambungan database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>