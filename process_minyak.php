<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari borang
    $pemegang_kad = $_POST['pemegang_kad'] ?? '';
    $jumlah_isian_rm = $_POST['jumlah_isian_rm'] ?? 0;
    $jumlah_isian_liter = $_POST['jumlah_isian_liter'] ?? 0;
    $jenis_minyak = $_POST['jenis_minyak'] ?? '';
    $no_odometer = $_POST['no_odometer'] ?? 0;
    $nama_syarikat = $_POST['nama_syarikat'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $rujukan_resit = $_POST['rujukan_resit'] ?? '';
    $tarikh = $_POST['tarikh'] ?? '';
    $no_pendaftaran = $_POST['no_pendaftaran'] ?? '';

    // Validate data asas
    if (empty($pemegang_kad) || $jumlah_isian_rm <= 0 || $jumlah_isian_liter <= 0 ||
        empty($jenis_minyak) || $no_odometer <= 0 || empty($nama_syarikat) ||
        empty($tarikh) || empty($no_pendaftaran)) {
        $_SESSION['error'] = "Sila isi semua field penting dengan betul.";
        header("Location: minyak_log.php");
        exit;
    }

    // Simpan ke database
    $stmt = $conn->prepare("
        INSERT INTO mileage_minyak (
            pemegang_kad, jumlah_isian_rm, jumlah_isian_liter, jenis_minyak,
            no_odometer, nama_syarikat, lokasi, rujukan_resit, tarikh,
            no_pendaftaran, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $created_by = $_SESSION['user_id'] ?? 1;

    $stmt->bind_param(
        "sddssdssssi",
        $pemegang_kad, $jumlah_isian_rm, $jumlah_isian_liter, $jenis_minyak,
        $no_odometer, $nama_syarikat, $lokasi, $rujukan_resit, $tarikh,
        $no_pendaftaran, $created_by
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Log inden minyak berjaya disimpan!";
    } else {
        $_SESSION['error'] = "Gagal simpan: " . $stmt->error;
    }

    $stmt->close();
}

header("Location: minyak_log.php");
exit;
?>