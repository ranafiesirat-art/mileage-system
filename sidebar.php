<!-- sidebar.php -->
<?php
// Optional: kalau nak tarik info tambahan user dari DB parking_system
// Contoh: $user_fullname atau role – uncomment kalau perlu
/*
include "db.php";
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();
*/
?>
<nav class="sidebar bg-dark text-white position-fixed top-0 start-0 h-100 overflow-auto" style="width: 260px; z-index: 1000; transition: all 0.3s;">
    <div class="p-4 text-center border-bottom border-secondary">
        <h4 class="mb-1 fw-bold">Mileage Kenderaan JQA 2435</h4>
        <small class="opacity-75">Sistem Inden & Log Perjalanan</small>
    </div>
   
    <div class="p-3">
        <div class="text-center mb-4">
            <div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle" style="width:80px; height:80px; font-size:2rem;">
                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
            </div>
            <h6 class="mt-3 mb-1"><?= htmlspecialchars($_SESSION['username'] ?? 'Pengguna') ?></h6>
            <!-- <small class="opacity-75"><?= $user_info['fullname'] ?? '' ?></small> -->
        </div>
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center py-3 px-4 rounded <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active bg-primary' : '' ?>" href="index.php">
                    <i class="bi bi-speedometer2 fs-4 me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center py-3 px-4 rounded <?= basename($_SERVER['PHP_SELF']) === 'harian_log.php' ? 'active bg-primary' : '' ?>" href="harian_log.php">
                    <i class="bi bi-journal-plus fs-4 me-3"></i>
                    <span>Log Perjalanan Harian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center py-3 px-4 rounded <?= basename($_SERVER['PHP_SELF']) === 'senarai_harian.php' ? 'active bg-primary' : '' ?>" href="senarai_harian.php">
                    <i class="bi bi-journal-text fs-4 me-3"></i>
                    <span>Senarai Perjalanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center py-3 px-4 rounded <?= basename($_SERVER['PHP_SELF']) === 'minyak_log.php' ? 'active bg-primary' : '' ?>" href="minyak_log.php">
                    <i class="bi bi-fuel-pump fs-4 me-3"></i>
                    <span>Log Inden Minyak</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center py-3 px-4 rounded <?= basename($_SERVER['PHP_SELF']) === 'senarai_minyak.php' ? 'active bg-primary' : '' ?>" href="senarai_minyak.php">
                    <i class="bi bi-list-check fs-4 me-3"></i>
                    <span>Senarai Inden Minyak</span>
                </a>
            </li>

            <!-- Divider -->
            <li class="nav-item my-3"><hr class="border-secondary opacity-50"></li>

            <!-- BARU: Kembali ke Parking System -->
            <li class="nav-item">
                <a class="nav-link text-info d-flex align-items-center py-3 px-4 rounded" href="/parking-system/index.php">
                    <i class="bi bi-arrow-left-circle fs-4 me-3"></i>
                    <span>Kembali ke Parking System</span>
                </a>
            </li>

            <!-- Keluar Sistem -->
            <li class="nav-item">
                <a class="nav-link text-danger d-flex align-items-center py-3 px-4 rounded" href="logout.php">
                    <i class="bi bi-box-arrow-right fs-4 me-3"></i>
                    <span>Keluar Sistem</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
<style>
    .sidebar .nav-link:hover {
        background: rgba(255,255,255,0.1);
    }
    .sidebar .nav-link.active {
        background: var(--primary) !important;
        box-shadow: 0 4px 10px rgba(13,110,253,0.3);
    }
    .avatar-circle {
        font-weight: bold;
        border: 3px solid white;
    }
</style>