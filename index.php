<?php
header("Location: login.php");
exit;
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

// Ambil nilai filter
$selected_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;  // 0 = Semua Bulan
$selected_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// 1. Perjalanan hari ini (tidak ikut filter)
$current_date = date('Y-m-d');
$today_trips = $conn->query("SELECT COUNT(*) as total FROM mileage_harian WHERE tarikh = '$current_date'")->fetch_assoc()['total'] ?? 0;

// 2. Jumlah isian minyak (ikut filter bulan/tahun)
$fuel_query = "SELECT SUM(jumlah_isian_rm) as total FROM mileage_minyak WHERE YEAR(tarikh) = ?";
$params_fuel = [$selected_tahun];
$types_fuel = "i";

if ($selected_bulan >= 1 && $selected_bulan <= 12) {
    $fuel_query .= " AND MONTH(tarikh) = ?";
    $params_fuel[] = $selected_bulan;
    $types_fuel .= "i";
}

$stmt_fuel = $conn->prepare($fuel_query);
$stmt_fuel->bind_param($types_fuel, ...$params_fuel);
$stmt_fuel->execute();
$month_fuel_rm = $stmt_fuel->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_fuel->close();

// 3. Jumlah jarak KM (ikut filter: jika bulan=0 → setahun penuh, jika bulan 1-12 → bulan tersebut)
$km_query = "SELECT SUM(jumlah_jarak) as total_km FROM mileage_harian WHERE tahun = ?";
$params_km = [$selected_tahun];
$types_km = "i";

if ($selected_bulan >= 1 && $selected_bulan <= 12) {
    $km_query .= " AND bulan = ?";
    $params_km[] = $selected_bulan;
    $types_km .= "i";
}

$stmt_km = $conn->prepare($km_query);
$stmt_km->bind_param($types_km, ...$params_km);
$stmt_km->execute();
$month_km = $stmt_km->get_result()->fetch_assoc()['total_km'] ?? 0;
$stmt_km->close();

// 4. Pie chart jenis minyak (ikut filter sama seperti fuel)
$fuel_types_query = "SELECT jenis_minyak, SUM(jumlah_isian_liter) as total 
                     FROM mileage_minyak 
                     WHERE YEAR(tarikh) = ?";
$fuel_params = [$selected_tahun];
$fuel_types = "i";

if ($selected_bulan >= 1 && $selected_bulan <= 12) {
    $fuel_types_query .= " AND MONTH(tarikh) = ?";
    $fuel_params[] = $selected_bulan;
    $fuel_types .= "i";
}

$fuel_types_query .= " GROUP BY jenis_minyak";

$stmt_fuel_chart = $conn->prepare($fuel_types_query);
$stmt_fuel_chart->bind_param($fuel_types, ...$fuel_params);
$stmt_fuel_chart->execute();
$result_fuel = $stmt_fuel_chart->get_result();

$fuel_labels = [];
$fuel_data = [];
while ($row = $result_fuel->fetch_assoc()) {
    $fuel_labels[] = $row['jenis_minyak'];
    $fuel_data[] = $row['total'];
}
$stmt_fuel_chart->close();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mileage Kenderaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0d47a1;
            --primary-light: #1565c0;
            --success: #2e7d32;
            --light-bg: #f5f7fa;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e3f2fd 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 260px; padding: 3rem 2.5rem; }
        .card-stat { border-radius: 1.25rem; overflow: hidden; box-shadow: var(--card-shadow); transition: all 0.3s; background: white; }
        .card-stat:hover { transform: translateY(-8px); }
        .stat-icon { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2.5rem; }
        .chart-card { border-radius: 1.25rem; box-shadow: var(--card-shadow); padding: 1.5rem; background: white; }
        .welcome-banner { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: white; border-radius: 1.5rem; padding: 2.5rem; margin-bottom: 2.5rem; box-shadow: var(--card-shadow); }
        .filter-box { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow); margin-bottom: 2rem; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 2rem 1.5rem; } }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="container-fluid">

        <div class="welcome-banner text-center">
            <h2 class="fw-bold mb-2">Selamat Datang kembali, <?= htmlspecialchars($_SESSION['nama_pegawai'] ?? $_SESSION['username'] ?? 'Admin') ?>!</h2>
            <p class="lead opacity-90 mb-0">Pantau penggunaan kenderaan & bahan api jabatan dengan mudah</p>
        </div>

        <!-- Filter Bulan & Tahun -->
        <div class="filter-box">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Bulan</label>
                    <select name="bulan" class="form-select form-select-lg">
                        <option value="0" <?= $selected_bulan == 0 ? 'selected' : '' ?>>Semua Bulan (Setahun Penuh)</option>
                        <?php
                        $bulan_nama = ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];
                        for ($m = 1; $m <= 12; $m++):
                        ?>
                            <option value="<?= $m ?>" <?= $selected_bulan == $m ? 'selected' : '' ?>>
                                <?= $bulan_nama[$m-1] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tahun</label>
                    <select name="tahun" class="form-select form-select-lg">
                        <?php for ($y = 2023; $y <= 2030; $y++): ?>
                            <option value="<?= $y ?>" <?= $selected_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-arrow-repeat me-2"></i> Paparkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Stat Cards -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="card-stat text-center">
                    <div class="card-body">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <h5 class="text-muted mb-1">Perjalanan Hari Ini</h5>
                        <h2 class="fw-bold text-primary"><?= number_format($today_trips) ?></h2>
                        <small class="text-muted">Kemas kini: <?= date('d M Y') ?></small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card-stat text-center">
                    <div class="card-body">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-fuel-pump"></i>
                        </div>
                        <h5 class="text-muted mb-1">Jumlah Isian Minyak (RM)</h5>
                        <h2 class="fw-bold text-success"><?= number_format($month_fuel_rm, 2) ?></h2>
                        <small class="text-muted">
                            <?= $selected_bulan == 0 ? 'Setahun Penuh' : $bulan_nama[$selected_bulan-1] ?> <?= $selected_tahun ?>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card-stat text-center">
                    <div class="card-body">
                        <div class="stat-icon bg-info-subtle text-info">
                            <i class="bi bi-road"></i>
                        </div>
                        <h5 class="text-muted mb-1">Jumlah Jarak (KM)</h5>
                        <h2 class="fw-bold text-info"><?= number_format($month_km, 1) ?></h2>
                        <small class="text-muted">
                            <?= $selected_bulan == 0 ? 'Setahun Penuh' : $bulan_nama[$selected_bulan-1] ?> <?= $selected_tahun ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="chart-card">
                    <h5 class="card-title mb-4">
                        Penggunaan Minyak Mengikut Jenis 
                        (<?= $selected_bulan == 0 ? 'Setahun Penuh' : $bulan_nama[$selected_bulan-1] ?> <?= $selected_tahun ?>)
                    </h5>
                    <canvas id="fuelTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const ctxFuel = document.getElementById('fuelTypeChart').getContext('2d');
    new Chart(ctxFuel, {
        type: 'pie',
        data: {
            labels: <?= json_encode($fuel_labels) ?>,
            datasets: [{
                data: <?= json_encode($fuel_data) ?>,
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} Liter` } }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>