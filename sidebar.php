<!-- sidebar.php -->
<nav class="sidebar d-none d-lg-block">
    <div class="text-center mb-5 pt-4">
        <div class="d-flex align-items-center justify-content-center">
            <i class="bi bi-speedometer2 fs-3 text-primary me-2"></i>
            <h4 class="fw-bold text-primary mb-0">Mileage Kenderaan</h4>
        </div>
        <small class="text-muted d-block mt-1">Sistem Pengurusan Jabatan</small>
    </div>

    <div class="px-3">
        <ul class="nav flex-column">
            <li class="nav-item mb-1">
                <a href="index.php" class="nav-link d-flex align-items-center <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill me-3 fs-5"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="harian_log.php" class="nav-link d-flex align-items-center <?= basename($_SERVER['PHP_SELF']) == 'harian_log.php' ? 'active' : '' ?>">
                    <i class="bi bi-journal-plus me-3 fs-5"></i>
                    <span>Log Perjalanan Harian</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="minyak_log.php" class="nav-link d-flex align-items-center <?= basename($_SERVER['PHP_SELF']) == 'minyak_log.php' ? 'active' : '' ?>">
                    <i class="bi bi-fuel-pump me-3 fs-5"></i>
                    <span>Log Inden Minyak</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="senarai_harian.php" class="nav-link d-flex align-items-center <?= basename($_SERVER['PHP_SELF']) == 'senarai_harian.php' ? 'active' : '' ?>">
                    <i class="bi bi-list-check me-3 fs-5"></i>
                    <span>Senarai Log Harian</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="senarai_minyak.php" class="nav-link d-flex align-items-center <?= basename($_SERVER['PHP_SELF']) == 'senarai_minyak.php' ? 'active' : '' ?>">
                    <i class="bi bi-list-check me-3 fs-5"></i>
                    <span>Senarai Inden Minyak</span>
                </a>
            </li>

            <!-- Divider -->
            <li class="nav-item my-3">
                <hr class="border-top border-secondary opacity-25 mx-3">
            </li>

            <li class="nav-item mt-auto">
                <a href="logout.php" class="nav-link d-flex align-items-center text-danger fw-bold">
                    <i class="bi bi-box-arrow-right me-3 fs-5"></i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Mobile Sidebar (collapse) -->
<button class="btn btn-primary d-lg-none position-fixed top-0 start-0 m-3 z-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
    <i class="bi bi-list fs-3"></i>
</button>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold text-primary" id="mobileSidebarLabel">
            <i class="bi bi-speedometer2 me-2"></i>Mileage Kenderaan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="harian_log.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'harian_log.php' ? 'active' : '' ?>">
                    <i class="bi bi-journal-plus me-2"></i> Log Perjalanan Harian
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="minyak_log.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'minyak_log.php' ? 'active' : '' ?>">
                    <i class="bi bi-fuel-pump me-2"></i> Log Inden Minyak
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="senarai_harian.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'senarai_harian.php' ? 'active' : '' ?>">
                    <i class="bi bi-list-check me-2"></i> Senarai Log Harian
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="senarai_minyak.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'senarai_minyak.php' ? 'active' : '' ?>">
                    <i class="bi bi-list-check me-2"></i> Senarai Inden Minyak
                </a>
            </li>
            <hr class="my-4">
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-danger fw-bold">
                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                </a>
            </li>
        </ul>
    </div>
</div>