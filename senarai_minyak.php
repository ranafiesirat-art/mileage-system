<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

// Filter
$bulan_filter = $_GET['bulan'] ?? '';
$tahun_filter = $_GET['tahun'] ?? '';

$sql = "SELECT * FROM mileage_minyak WHERE 1=1";
$params = [];
$types = "";

if ($bulan_filter) {
    $sql .= " AND MONTH(tarikh) = ?";
    $params[] = $bulan_filter;
    $types .= "i";
}
if ($tahun_filter) {
    $sql .= " AND YEAR(tarikh) = ?";
    $params[] = $tahun_filter;
    $types .= "i";
}
$sql .= " ORDER BY tarikh DESC, created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM mileage_minyak ORDER BY tarikh DESC, created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Log Inden Minyak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --success: #2e7d32;
            --success-light: #4caf50;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        body { background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e9 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 260px; padding: 3rem 2.5rem; }
        .table-container { background: white; border-radius: 1.25rem; box-shadow: var(--card-shadow); overflow: hidden; }
        .table thead { background: var(--success); color: white; }
        .table th, .table td { vertical-align: middle; padding: 1rem; }
        .filter-card { background: white; border-radius: 1rem; box-shadow: var(--card-shadow); padding: 1.5rem; margin-bottom: 2rem; }
        .btn-filter { background: var(--success); border: none; transition: all 0.3s; }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(46,125,50,0.3); }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 2rem 1.5rem; } }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-success">
                <i class="bi bi-list-check me-3"></i>
                Senarai Log Inden Minyak
            </h2>
            <a href="minyak_log.php" class="btn btn-success btn-lg">
                <i class="bi bi-plus-lg me-2"></i>Tambah Log Baru
            </a>
        </div>

        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        <option value="">-- Semua Bulan --</option>
                        <?php for($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= $bulan_filter == $m ? 'selected' : '' ?>>
                                <?= date("F", mktime(0,0,0,$m,1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">-- Semua Tahun --</option>
                        <?php for($y = date('Y')-2; $y <= date('Y')+1; $y++): ?>
                            <option value="<?= $y ?>" <?= $tahun_filter == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success btn-filter w-100">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Pemegang Kad</th>
                        <th>Jumlah (RM)</th>
                        <th>Jumlah (Liter)</th>
                        <th>Jenis Minyak</th>
                        <th>Odometer</th>
                        <th>Syarikat</th>
                        <th>Lokasi</th>
                        <th>Resit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tarikh']) ?></td>
                                <td><?= htmlspecialchars($row['pemegang_kad']) ?></td>
                                <td class="fw-bold text-success">RM <?= number_format($row['jumlah_isian_rm'], 2) ?></td>
                                <td><?= number_format($row['jumlah_isian_liter'], 2) ?> L</td>
                                <td><?= htmlspecialchars($row['jenis_minyak']) ?></td>
                                <td><?= number_format($row['no_odometer'], 1) ?> KM</td>
                                <td><?= htmlspecialchars($row['nama_syarikat']) ?></td>
                                <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                <td><?= htmlspecialchars($row['rujukan_resit'] ?: '-') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-exclamation-circle fs-1 d-block mb-3"></i>
                                Tiada rekod inden minyak lagi.<br>
                                Sila tambah log baru.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>