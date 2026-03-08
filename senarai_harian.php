<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

// Ambil parameter filter
$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$filter_kenderaan = trim($_GET['no_kenderaan'] ?? '');
$filter_pemandu = trim($_GET['nama_pemandu'] ?? '');

// Query dengan JOIN
$sql = "
    SELECT mh.*, 
           k.no_pendaftaran_kenderaan AS no_kenderaan_display
    FROM mileage_harian mh
    LEFT JOIN kenderaan k ON mh.kenderaan_id = k.id
";
$where = [];
$params = [];
$types = "";

if ($filter_bulan > 0 && $filter_bulan <= 12) {
    $where[] = "mh.bulan = ?";
    $params[] = $filter_bulan;
    $types .= "i";
}
if ($filter_tahun > 0) {
    $where[] = "mh.tahun = ?";
    $params[] = $filter_tahun;
    $types .= "i";
}
if (!empty($filter_kenderaan)) {
    $where[] = "k.no_pendaftaran_kenderaan LIKE ?";
    $params[] = "%$filter_kenderaan%";
    $types .= "s";
}
if (!empty($filter_pemandu)) {
    $where[] = "mh.nama_pemandu LIKE ?";
    $params[] = "%$filter_pemandu%";
    $types .= "s";
}
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY mh.id DESC";

// Execute
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Query error: " . $conn->error);
}
$total_rekod = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Log Perjalanan Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Style sama seperti asal, tak ubah -->
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --success: #198754;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
            --hover-bg: #e7f1ff;
        }
        body { background: var(--light-bg); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 260px; padding: 3rem 2.5rem; }
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 1.25rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--card-shadow);
        }
        .filter-card {
            background: white;
            border-radius: 1.25rem;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 2.5rem;
            transition: all 0.3s;
        }
        .filter-card:hover { box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
        .table-container {
            background: white;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        .table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
            white-space: nowrap;
            text-align: center;
        }
        .table td { vertical-align: middle; text-align: center; }
        .table tr:hover { background-color: var(--hover-bg); }
        .jumlah-rekod {
            font-size: 0.95rem;
            color: #6c757d;
            padding: 1rem;
            text-align: right;
            background: #f8f9fa;
        }
        .no-data {
            text-align: center;
            padding: 5rem 2rem;
            color: #6c757d;
            font-size: 1.25rem;
            background: white;
            border-radius: 1.25rem;
            box-shadow: var(--card-shadow);
        }
        .action-btn { font-size: 1.1rem; padding: 0.4rem 0.8rem; margin: 0 3px; }
        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 2rem 1.5rem; }
            .page-header { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="main-content">
    <div class="container-fluid">
        <div class="page-header text-center">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-journal-text me-2"></i>Senarai Log Perjalanan Harian
            </h2>
            <p class="lead opacity-90 mb-0">Pantau & urus rekod perjalanan rasmi dengan mudah & profesional</p>
        </div>

        <!-- Filter -->
        <div class="filter-card">
            <h5 class="fw-bold mb-3 text-primary">
                <i class="bi bi-funnel-fill me-2"></i>Carian Lanjutan
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold">Bulan</label>
                    <select name="bulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $filter_bulan == $m ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold">No Kenderaan</label>
                    <input type="text" name="no_kenderaan" class="form-control" value="<?= htmlspecialchars($filter_kenderaan) ?>" placeholder="Contoh: JDA 1234">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold">Nama Pemandu</label>
                    <input type="text" name="nama_pemandu" class="form-control" value="<?= htmlspecialchars($filter_pemandu) ?>" placeholder="Contoh: Ali">
                </div>
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary px-5 me-2">Cari</button>
                    <a href="senarai_harian.php" class="btn btn-outline-secondary px-4">Reset</a>
                </div>
            </form>
        </div>

        <!-- Senarai -->
        <?php if ($result->num_rows > 0): ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>No Siri Buku</th>
                                <th>Tahun</th>
                                <th>Bulan</th>
                                <th>Tarikh</th>
                                <th>No Kenderaan</th>
                                <th>Nama Pemandu</th>
                                <th>Nama Pengguna</th>
                                <th>Jabatan</th>
                                <th>Keterangan Tugasan</th>
                                <th>Odo Mula (KM)</th>
                                <th>Odo Akhir (KM)</th>
                                <th>Jumlah Jarak (KM)</th>
                                <th>Masa Pergi</th>
                                <th>Masa Pulang</th>
                                <th>Catatan</th>
                                <th>Action</th>
                                <th>Dicipta Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-center"><?= htmlspecialchars($row['id'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['no_siri_buku'] ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['tahun'] ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['bulan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['tarikh'] ?? '-') ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['no_kenderaan_display'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nama_pemandu'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nama_pengguna'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['keterangan_tugasan'] ?? '-') ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($row['odo_mula'] ?? 0, 1) ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($row['odo_akhir'] ?? 0, 1) ?></td>
                                    <td class="text-end fw-bold text-primary"><?= number_format($row['jumlah_jarak'] ?? 0, 1) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['masa_pergi'] ?: '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['masa_pulang'] ?: '-') ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['catatan'] ?: '-')) ?></td>
                                    <td>
                                        <a href="edit_harian.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning action-btn" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete_harian.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger action-btn" title="Padam" onclick="return confirm('Pastikan padam rekod ini?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($row['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="jumlah-rekod bg-light p-3 text-end">
                    Menunjukkan <?= $total_rekod ?> rekod
                </div>
            </div>
        <?php else: ?>
            <div class="no-data bg-white rounded-3 shadow p-5 text-center">
                <i class="bi bi-exclamation-circle-fill fs-1 text-warning mb-3 d-block"></i>
                <h5 class="text-muted mb-2">Tiada rekod ditemui</h5>
                <small>Cuba ubah carian atau tambah log baru.</small>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>