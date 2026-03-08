<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: harian_log.php");
    exit;
}

// Ambil semua data dari form
$bulan          = (int)($_POST['bulan'] ?? 0);
$tahun          = (int)($_POST['tahun'] ?? 0);
$no_siri_buku   = trim($_POST['no_siri_buku'] ?? '');
$no_kenderaan   = trim($_POST['no_kenderaan'] ?? '');
$nama_pemandu   = trim($_POST['nama_pemandu'] ?? '');
$nama_pengguna  = trim($_POST['nama_pengguna'] ?? '');
$jabatan        = trim($_POST['jabatan'] ?? '');
$tarikh         = $_POST['tarikh'] ?? date('Y-m-d');
$tugasan        = trim($_POST['tugasan'] ?? '');          // <-- tambah trim supaya bersih
$odo_mula       = floatval($_POST['odo_mula'] ?? 0);
$odo_akhir      = floatval($_POST['odo_akhir'] ?? 0);
$masa_pergi     = $_POST['masa_pergi'] ?? null;
$masa_pulang    = $_POST['masa_pulang'] ?? null;
$catatan        = $_POST['catatan'] ?? '';
$user_id        = $_SESSION['user_id'] ?? '';

$jumlah_jarak = $odo_akhir - $odo_mula;

// Validation asas
if (
    $bulan < 1 || $bulan > 12 ||
    $tahun < 2000 ||
    empty($no_siri_buku) ||
    empty($no_kenderaan) ||
    empty($nama_pemandu) ||
    empty($nama_pengguna) ||
    empty($jabatan) ||
    empty($tarikh) ||
    $odo_mula <= 0 ||
    $odo_akhir <= 0 ||
    $jumlah_jarak <= 0 ||
    empty($masa_pergi) ||
    empty($masa_pulang)
) {
    $_SESSION['error'] = "Sila isi semua field wajib dengan betul. Pastikan Odometer Akhir lebih besar daripada Odometer Mula.";
    $redirect = isset($_POST['id']) && $_POST['id'] > 0 ? "edit_harian.php?id=" . $_POST['id'] : "harian_log.php";
    header("Location: $redirect");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    // ==================== BAHAGIAN EDIT / UPDATE ====================
    $stmt = $conn->prepare("
        UPDATE mileage_harian SET
            bulan = ?,
            tahun = ?,
            no_siri_buku = ?,
            no_kenderaan = ?,
            nama_pemandu = ?,
            nama_pengguna = ?,
            jabatan = ?,
            tarikh = ?,
            tugasan = ?,
            odo_mula = ?,
            odo_akhir = ?,
            jumlah_jarak = ?,
            masa_pergi = ?,
            masa_pulang = ?,
            catatan = ?
        WHERE id = ?
    ");

    // TAMBAH 's' untuk tugasan → jadi 9 string berturut-turut selepas tahun
    $stmt->bind_param("iisssssssdddssssi",
        $bulan, $tahun, $no_siri_buku, $no_kenderaan,
        $nama_pemandu, $nama_pengguna, $jabatan, $tarikh,
        $tugasan, $odo_mula, $odo_akhir, $jumlah_jarak,
        $masa_pergi, $masa_pulang, $catatan, $id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Rekod berjaya dikemas kini!";
    } else {
        $_SESSION['error'] = "Gagal update: " . $stmt->error;
    }
    $stmt->close();

    header("Location: senarai_harian.php");
    exit;
} else {
    // ==================== BAHAGIAN INSERT (baru) ====================
    $stmt = $conn->prepare("
        INSERT INTO mileage_harian
        (bulan, tahun, no_siri_buku, no_kenderaan, nama_pemandu, nama_pengguna, jabatan, tarikh, tugasan,
         odo_mula, odo_akhir, jumlah_jarak, masa_pergi, masa_pulang, catatan, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // TAMBAH 's' untuk tugasan → jadi 9 string berturut-turut selepas tahun
    $stmt->bind_param("iisssssssdddsssii",
        $bulan, $tahun, $no_siri_buku, $no_kenderaan,
        $nama_pemandu, $nama_pengguna, $jabatan, $tarikh,
        $tugasan, $odo_mula, $odo_akhir, $jumlah_jarak,
        $masa_pergi, $masa_pulang, $catatan, $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Log perjalanan harian berjaya disimpan!";
    } else {
        $_SESSION['error'] = "Gagal simpan: " . $stmt->error;
    }
    $stmt->close();

    header("Location: harian_log.php");
    exit;
}
?>