<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM mileage_minyak WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $error = "Rekod tidak dijumpai.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_kad_minyak    = trim($_POST['no_kad_minyak'] ?? '');
    $pemegang_kad     = trim($_POST['pemegang_kad'] ?? '');
    $jumlah_isian_rm  = floatval($_POST['jumlah_isian_rm'] ?? 0);
    $jumlah_isian_liter = floatval($_POST['jumlah_isian_liter'] ?? 0);
    $jenis_minyak     = trim($_POST['jenis_minyak'] ?? '');
    $no_odometer      = floatval($_POST['no_odometer'] ?? 0);
    $nama_syarikat    = trim($_POST['nama_syarikat'] ?? '');
    $lokasi           = trim($_POST['lokasi'] ?? '');
    $rujukan_resit    = trim($_POST['rujukan_resit'] ?? '');
    $tarikh           = $_POST['tarikh'] ?? date('Y-m-d');

    if (empty($no_kad_minyak) || empty($pemegang_kad) || $jumlah_isian_rm <= 0 || $jumlah_isian_liter <= 0 ||
        empty($jenis_minyak) || $no_odometer <= 0 || empty($nama_syarikat) ||
        empty($lokasi) || empty($rujukan_resit) || empty($tarikh)) {
        $error = "Sila isi semua field wajib dengan betul.";
    } else {
        $stmt = $conn->prepare("
            UPDATE mileage_minyak SET
                tarikh = ?, no_kad_minyak = ?, pemegang_kad = ?, 
                jumlah_isian_rm = ?, jumlah_isian_liter = ?, jenis_minyak = ?,
                no_odometer = ?, nama_syarikat = ?, lokasi = ?, 
                rujukan_resit = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssddsdsssi",
            $tarikh, $no_kad_minyak, $pemegang_kad,
            $jumlah_isian_rm, $jumlah_isian_liter, $jenis_minyak,
            $no_odometer, $nama_syarikat, $lokasi,
            $rujukan_resit, $id
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "Rekod inden minyak berjaya dikemas kini!";
            header("Location: senarai_minyak.php");
            exit;
        } else {
            $error = "Gagal kemas kini: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Log Inden Minyak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 260px; padding: 2.5rem 2rem; }
        .form-card { background: white; border-radius: 1.25rem; box-shadow: 0 8px 20px rgba(0,0,0,0.06); padding: 2.5rem; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 1.5rem; } }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="main-content">
    <div class="container-fluid">
        <h3 class="fw-bold text-primary mb-4">
            <i class="bi bi-pencil-square me-2"></i>Edit Log Inden Minyak
        </h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($row): ?>
            <div class="form-card">
                <form method="POST" enctype="multipart/form-data" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">No Kad MinYak</label>
                        <input type="text" name="no_kad_minyak" class="form-control form-control-lg" required value="<?= htmlspecialchars($row['no_kad_minyak'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemegang Kad</label>
                        <input type="text" name="pemegang_kad" class="form-control form-control-lg" required value="<?= htmlspecialchars($row['pemegang_kad'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jumlah Isian (RM)</label>
                        <input type="number" name="jumlah_isian_rm" step="0.01" class="form-control form-control-lg" required min="0" value="<?= htmlspecialchars($row['jumlah_isian_rm'] ?? 0) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jumlah Isian (Liter)</label>
                        <input type="number" name="jumlah_isian_liter" step="0.01" class="form-control form-control-lg" required min="0" value="<?= htmlspecialchars($row['jumlah_isian_liter'] ?? 0) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis Minyak</label>
                        <select name="jenis_minyak" class="form-select form-select-lg" required>
                            <option value="Petrol RON95" <?= ($row['jenis_minyak'] ?? '') == 'Petrol RON95' ? 'selected' : '' ?>>Petrol RON95</option>
                            <option value="Petrol RON97" <?= ($row['jenis_minyak'] ?? '') == 'Petrol RON97' ? 'selected' : '' ?>>Petrol RON97</option>
                            <option value="Diesel" <?= ($row['jenis_minyak'] ?? '') == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="Diesel Euro 5" <?= ($row['jenis_minyak'] ?? '') == 'Diesel Euro 5' ? 'selected' : '' ?>>Diesel Euro 5</option>
                            <option value="Lain-lain" <?= ($row['jenis_minyak'] ?? '') == 'Lain-lain' ? 'selected' : '' ?>>Lain-lain</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Odometer (KM)</label>
                        <input type="number" name="no_odometer" step="0.1" class="form-control form-control-lg" required min="0" value="<?= htmlspecialchars($row['no_odometer'] ?? 0) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarikh Isian</label>
                        <input type="date" name="tarikh" class="form-control form-control-lg" required value="<?= htmlspecialchars($row['tarikh'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Syarikat</label>
                        <input type="text" name="nama_syarikat" class="form-control form-control-lg" required value="<?= htmlspecialchars($row['nama_syarikat'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lokasi Stesen</label>
                        <input type="text" name="lokasi" class="form-control form-control-lg" value="<?= htmlspecialchars($row['lokasi'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rujukan Resit / No Resit</label>
                        <input type="text" name="rujukan_resit" class="form-control form-control-lg" value="<?= htmlspecialchars($row['rujukan_resit'] ?? '') ?>">
                    </div>
                    <div class="col-12 text-end mt-5">
                        <a href="senarai_minyak.php" class="btn btn-secondary btn-lg px-5 me-2">Batal</a>
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Rekod tidak dijumpai atau ID tidak sah.
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>