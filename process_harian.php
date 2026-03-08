<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: harian_log.php");
    exit;
}

$bulan = (int)($_POST['bulan'] ?? 0);
$tahun = (int)($_POST['tahun'] ?? 0);
$no_siri_buku = trim($_POST['no_siri_buku'] ?? '');  // ← Logik ASAL yang dah OK, tak disentuh
$no_kenderaan = trim($_POST['no_kenderaan'] ?? '');
$nama_pemandu = trim($_POST['nama_pemandu'] ?? '');
$nama_pengguna = trim($_POST['nama_pengguna'] ?? '');
$jabatan = trim($_POST['jabatan'] ?? '');
$tarikh = $_POST['tarikh'] ?? date('Y-m-d');  // ← Logik ASAL yang dah OK, tak disentuh
$keterangan_tugasan = trim($_POST['keterangan_tugasan'] ?? '');
$odo_mula = floatval($_POST['odo_mula'] ?? 0);
$odo_akhir = floatval($_POST['odo_akhir'] ?? 0);
$masa_pergi = $_POST['masa_pergi'] ?? null;
$masa_pulang = $_POST['masa_pulang'] ?? null;
$catatan = $_POST['catatan'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$jumlah_jarak = $odo_akhir - $odo_mula;

$id = (int)($_POST['id'] ?? 0);

// Validation (ikut asal boss)
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
    $redirect = $id > 0 ? "edit_harian.php?id=$id" : "harian_log.php";
    header("Location: $redirect");
    exit;
}

// Handle kenderaan_id (penambahbaikan baru, tak sentuh no_siri_buku & tarikh)
$kenderaan_id = null;
if (!empty($no_kenderaan)) {
    $stmt = $conn->prepare("SELECT id FROM kenderaan WHERE no_pendaftaran_kenderaan = ? LIMIT 1");
    $stmt->bind_param("s", $no_kenderaan);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $kenderaan_id = $row['id'];
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO kenderaan (no_pendaftaran_kenderaan, created_at) VALUES (?, NOW())");
        $stmt_insert->bind_param("s", $no_kenderaan);
        if ($stmt_insert->execute()) {
            $kenderaan_id = $conn->insert_id;
        } else {
            $_SESSION['error'] = "Gagal tambah kenderaan baru: " . $stmt_insert->error;
            $redirect = $id > 0 ? "edit_harian.php?id=$id" : "harian_log.php";
            header("Location: $redirect");
            exit;
        }
        $stmt_insert->close();
    }
    $stmt->close();
}

if ($kenderaan_id === null) {
    $_SESSION['error'] = "Tiada maklumat kenderaan yang sah.";
    $redirect = $id > 0 ? "edit_harian.php?id=$id" : "harian_log.php";
    header("Location: $redirect");
    exit;
}

// Simpan / Update
if ($id > 0) {
    $stmt = $conn->prepare("
        UPDATE mileage_harian SET
            bulan = ?, tahun = ?, no_siri_buku = ?,
            kenderaan_id = ?, nama_pemandu = ?, nama_pengguna = ?, jabatan = ?, tarikh = ?,
            keterangan_tugasan = ?, odo_mula = ?, odo_akhir = ?, jumlah_jarak = ?,
            masa_pergi = ?, masa_pulang = ?, catatan = ?
        WHERE id = ?
    ");
    $stmt->bind_param("iisisssssdddsssi",
        $bulan, $tahun, $no_siri_buku,
        $kenderaan_id, $nama_pemandu, $nama_pengguna, $jabatan, $tarikh,
        $keterangan_tugasan,
        $odo_mula, $odo_akhir, $jumlah_jarak,
        $masa_pergi, $masa_pulang, $catatan, $id
    );
} else {
    $stmt = $conn->prepare("
        INSERT INTO mileage_harian
        (bulan, tahun, no_siri_buku, kenderaan_id, nama_pemandu, nama_pengguna, jabatan, tarikh, keterangan_tugasan,
         odo_mula, odo_akhir, jumlah_jarak, masa_pergi, masa_pulang, catatan, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisisssssdddsssi",
        $bulan, $tahun, $no_siri_buku, $kenderaan_id,
        $nama_pemandu, $nama_pengguna, $jabatan, $tarikh,
        $keterangan_tugasan,
        $odo_mula, $odo_akhir, $jumlah_jarak,
        $masa_pergi, $masa_pulang, $catatan, $user_id
    );
}

if ($stmt->execute()) {
    $_SESSION['success'] = $id > 0 ? "Rekod berjaya dikemas kini!" : "Log perjalanan harian berjaya disimpan!";
    header("Location: senarai_harian.php");
    exit;
} else {
    $_SESSION['error'] = ($id > 0 ? "Gagal kemas kini: " : "Gagal simpan: ") . $stmt->error;
    $redirect = $id > 0 ? "edit_harian.php?id=$id" : "harian_log.php";
    header("Location: $redirect");
    exit;
}

$stmt->close();
?>