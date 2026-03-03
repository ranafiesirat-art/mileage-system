<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari borang
    $bulan = $_POST['bulan'] ?? '';
    $tahun = $_POST['tahun'] ?? '';
    $no_pendaftaran = $_POST['no_pendaftaran'] ?? '';
    $jenis_kenderaan = $_POST['jenis_kenderaan'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $tarikh = $_POST['tarikh'] ?? '';
    $masa_pergi = $_POST['masa_pergi'] ?? '';
    $masa_pulang = $_POST['masa_pulang'] ?? '';
    $nama_pemandu = $_POST['nama_pemandu'] ?? '';
    $pegawai_pengguna = $_POST['pegawai_pengguna'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    $odometer_terakhir = $_POST['odometer_terakhir'] ?? 0;
    $odometer_terkini = $_POST['odometer_terkini'] ?? 0;

    // Validate data asas (boleh tambah lebih ketat nanti)
    if (empty($bulan) || empty($tahun) || empty($no_pendaftaran) || empty($tarikh) ||
        empty($nama_pemandu) || empty($pegawai_pengguna) || $odometer_terkini <= $odometer_terakhir) {
        $_SESSION['error'] = "Sila isi semua field penting & pastikan odometer terkini lebih besar.";
        header("Location: harian_log.php");
        exit;
    }

    // Simpan ke database
    $stmt = $conn->prepare("
        INSERT INTO mileage_harian (
            bulan, tahun, no_pendaftaran, jenis_kenderaan, jabatan, tarikh,
            masa_pergi, masa_pulang, nama_pemandu, pegawai_pengguna, catatan,
            odometer_terakhir, odometer_terkini, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $created_by = $_SESSION['user_id'] ?? 1; // Ganti dengan ID user sebenar kalau ada

    $stmt->bind_param(
        "iiissssssssdii",
        $bulan, $tahun, $no_pendaftaran, $jenis_kenderaan, $jabatan, $tarikh,
        $masa_pergi, $masa_pulang, $nama_pemandu, $pegawai_pengguna, $catatan,
        $odometer_terakhir, $odometer_terkini, $created_by
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Log perjalanan harian berjaya disimpan!";
    } else {
        $_SESSION['error'] = "Gagal simpan: " . $stmt->error;
    }

    $stmt->close();
}

header("Location: harian_log.php");
exit;
?>