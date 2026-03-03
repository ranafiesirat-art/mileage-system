<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

// Ambil bulan & tahun semasa
$current_month = date('n');
$current_year = date('Y');
$current_date = date('Y-m-d');

// 1. Jumlah perjalanan hari ini
$today_trips = $conn->query("SELECT COUNT(*) as total FROM mileage_harian WHERE tarikh = '$current_date'")->fetch_assoc()['total'] ?? 0;

// 2. Jumlah jarak bulan ini
$month_distance = $conn->query("SELECT SUM(jumlah_jarak) as total FROM mileage_harian WHERE bulan = $current_month AND tahun = $current_year")->fetch_assoc()['total'] ?? 0;

// 3. Jumlah isian minyak bulan ini
$month_fuel_rm = $conn->query("SELECT SUM(jumlah_isian_rm) as total FROM mileage_minyak WHERE MONTH(tarikh) = $current_month AND YEAR(tarikh) = $current_year")->fetch_assoc()['total'] ?? 0;

// 4. Data mini chart: Jarak mingguan bulan ini (contoh 4 minggu terakhir)
$weekly_distance = [];
for ($w = 0; $w < 4; $w++) {
    $week_start = date('Y-m-d', strtotime("-$w week"));
    $week_end = date('Y-m-d', strtotime("-$w week +6 days"));
    $result = $conn->query("SELECT SUM(jumlah_jarak) as total FROM mileage_harian WHERE tarikh BETWEEN '$week_start' AND '$week_end'");
    $weekly_distance[] = $result->fetch_assoc()['total'] ?? 0;
}

// 5. Pie chart: Jenis minyak bulan ini
$fuel_types = $conn->query("SELECT jenis_minyak, SUM(jumlah_isian_liter) as total FROM mileage_minyak WHERE MONTH(tarikh) = $current_month AND YEAR(tarikh) = $current_year GROUP BY jenis_minyak");
$fuel_labels = [];
$fuel_data = [];
while ($row = $fuel_types->fetch_assoc()) {
    $fuel_labels[] = $row['jenis_minyak'];
    $fuel_data[] = $row['total'];
}
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
        .card-stat {
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            background: white;
        }
        .card-stat:hover { transform: translateY(-8px); }
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
        }
        .chart-card {
            border-radius: 1.25rem;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            background: white;
        }
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--card-shadow);
        }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 2rem 1.5rem; } }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Welcome Banner -->
        <div class="welcome-banner text-center">
            <h2 class="fw-bold mb-2">Selamat Datang kembali, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h2>
            <p class="lead opacity-90 mb-0">Pantau penggunaan kenderaan & bahan api jabatan dengan mudah</p>
        </div>

        <!-- Statistik Real -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="card-stat text-center">
                    <div class="card-body">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <h5 class="text-muted mb-1">Perjalanan Hari Ini</h5>
                        <h2 class="fw-bold text-primary"><?= number_format($today_trips) ?></h2>
                        <small class="text-muted">Kemas kini: <?= date('d M Y H:i') ?></small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card-stat text-center">
                    <div class="card-body">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h5 class="text-muted mb-1">Jumlah Jarak Bulan Ini</h5>
                        <h2 class="fw-bold text-success"><?= number_format($month_distance, 1) ?> KM</h2>
                        <small class="text-muted">Kemas kini: <?= date('d M Y H:i') ?></small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card-stat text-center">
                    <div class="card-body">
                        <div class="stat-icon bg-info-subtle text-info">
                            <i class="bi bi-fuel-pump"></i>
                        </div>
                        <h5 class="text-muted mb-1">Jumlah Isian Minyak Bulan Ini</h5>
                        <h2 class="fw-bold text-info">RM <?= number_format($month_fuel_rm, 2) ?></h2>
                        <small class="text-muted">Kemas kini: <?= date('d M Y H:i') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini Charts -->
        <div class="row g-4">
            <!-- Line Chart: Jarak Mingguan -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5 class="card-title mb-4">Trend Jarak Mingguan (Bulan Ini)</h5>
                    <canvas id="weeklyDistanceChart" height="180"></canvas>
                </div>
            </div>

            <!-- Pie Chart: Jenis Minyak -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5 class="card-title mb-4">Penggunaan Minyak Mengikut Jenis (Bulan Ini)</h5>
                    <canvas id="fuelTypeChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-5">
            <p class="lead text-muted mb-4">Sila tambah log baru atau semak senarai rekod</p>
            <div class="d-flex justify-content-center gap-4">
                <a href="harian_log.php" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="bi bi-journal-plus me-2 fs-4"></i> Log Perjalanan Harian
                </a>
                <a href="minyak_log.php" class="btn btn-success btn-lg px-5 py-3">
                    <i class="bi bi-fuel-pump me-2 fs-4"></i> Log Inden Minyak
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart 1: Jarak Mingguan (Line)
    const ctxWeekly = document.getElementById('weeklyDistanceChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'line',
        data: {
            labels: ['Minggu 4 lalu', 'Minggu 3 lalu', 'Minggu 2 lalu', 'Minggu ini'],
            datasets: [{
                label: 'Jarak (KM)',
                data: <?= json_encode(array_reverse($weekly_distance)) ?>,
                borderColor: 'rgba(13, 71, 161, 1)',
                backgroundColor: 'rgba(13, 71, 161, 0.2)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(13, 71, 161, 1)',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Chart 2: Jenis Minyak (Pie)
    const ctxFuel = document.getElementById('fuelTypeChart').getContext('2d');
    new Chart(ctxFuel, {
        type: 'pie',
        data: {
            labels: <?= json_encode($fuel_labels) ?>,
            datasets: [{
                data: <?= json_encode($fuel_data) ?>,
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1'
                ],
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