<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

$success_msg = $_SESSION['success'] ?? '';
$error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Perjalanan Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #0d47a1;          /* Biru lebih dalam, korporat */
            --primary-light: #1565c0;
            --primary-glow: rgba(13, 71, 161, 0.2);
            --success: #2e7d32;
            --light-bg: #f5f7fa;
            --card-bg: white;
            --shadow: 0 12px 35px rgba(0,0,0,0.09);
            --transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e3f2fd 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #1a1a1a;
        }
        .main-content {
            margin-left: 260px;
            padding: 3.5rem 3rem;
        }
        .header-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: 1.25rem;
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        .header-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            opacity: 0.6;
        }
        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .form-pro-card {
            background: var(--card-bg);
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            padding: 3rem;
            border: 1px solid rgba(13, 71, 161, 0.06);
        }
        .form-label {
            font-size: 1.1rem;
            font-weight: 600;
            color: #37474f;
            margin-bottom: 0.6rem;
        }
        .form-control-lg, .form-select-lg {
            font-size: 1.1rem;
            padding: 0.85rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #d0d8e0;
            transition: var(--transition);
        }
        .form-control-lg:focus, .form-select-lg:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.3rem var(--primary-glow);
            background: #fff;
        }
        .input-group-lg .input-group-text {
            background: rgba(13, 71, 161, 0.08);
            border: none;
            color: var(--primary);
            border-radius: 0.75rem 0 0 0.75rem;
        }
        .btn-pro {
            padding: 1rem 2.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            border-radius: 0.9rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(13,71,161,0.2);
        }
        .btn-pro:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(13,71,161,0.35);
        }
        .alert-dismissible {
            border-radius: 1rem;
            padding: 1.5rem;
            font-size: 1.1rem;
        }
        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 2rem 1.5rem; }
            .form-pro-card { padding: 2rem; }
            .header-banner { padding: 2rem; }
            .page-title { font-size: 1.9rem; }
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header Banner dengan Gambar -->
        <div class="header-banner text-center position-relative">
            <div class="position-absolute top-0 end-0 p-4 opacity-50">
                <i class="bi bi-car-front-fill fs-1"></i>
            </div>
            <h2 class="page-title mb-2">Log Perjalanan Harian</h2>
            <p class="lead opacity-90 mb-0">Rekod pergerakan kenderaan jabatan dengan tepat & mudah</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-3 fs-4"></i> <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i> <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-pro-card">
            <form method="POST" action="process_harian.php" class="row g-4">
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select form-select-lg" required>
                        <?php for($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= date('n') == $m ? 'selected' : '' ?>>
                                <?= date("F", mktime(0,0,0,$m,1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select form-select-lg" required>
                        <?php for($y = date('Y')-1; $y <= date('Y')+1; $y++): ?>
                            <option value="<?= $y ?>" <?= date('Y') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">No. Pendaftaran Kenderaan</label>
                    <input type="text" name="no_pendaftaran" class="form-control form-control-lg" required 
                           placeholder="Contoh: JDA1234" pattern="[A-Za-z0-9-]+" title="Huruf, nombor & tanda '-' dibenarkan">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jenis Kenderaan</label>
                    <input type="text" name="jenis_kenderaan" class="form-control form-control-lg" required placeholder="Contoh: Proton Saga, Perodua Myvi">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jabatan / Unit</label>
                    <input type="text" name="jabatan" class="form-control form-control-lg" required placeholder="Contoh: Parking Admin Pontian">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tarikh Perjalanan</label>
                    <input type="date" name="tarikh" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Masa Pergi</label>
                    <input type="time" name="masa_pergi" class="form-control form-control-lg" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Masa Pulang</label>
                    <input type="time" name="masa_pulang" class="form-control form-control-lg" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Pemandu</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-primary-subtle border-0"><i class="bi bi-person-fill text-primary"></i></span>
                        <input type="text" name="nama_pemandu" class="form-control" required placeholder="Nama pemandu kenderaan">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pegawai Pengguna</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-primary-subtle border-0"><i class="bi bi-person-badge-fill text-primary"></i></span>
                        <input type="text" name="pegawai_pengguna" class="form-control" required placeholder="Nama pegawai yang menggunakan">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Catatan / Tujuan Perjalanan</label>
                    <textarea name="catatan" class="form-control" rows="4" placeholder="Contoh: Rondaan premis di Pontian Kechil, lawatan tapak, urusan rasmi..."></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Odometer Terakhir (KM)</label>
                    <input type="number" name="odometer_terakhir" id="odometer_terakhir" step="0.1" class="form-control form-control-lg" required min="0" placeholder="Baca odometer sebelum bergerak">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Odometer Terkini (KM)</label>
                    <input type="number" name="odometer_terkini" id="odometer_terkini" step="0.1" class="form-control form-control-lg" required min="0" placeholder="Baca odometer selepas pulang">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jumlah Jarak (KM)</label>
                    <input type="number" name="jumlah_jarak" id="jumlah_jarak" step="0.1" class="form-control form-control-lg bg-light fw-bold" readonly>
                </div>

                <div class="col-12 text-end mt-5">
                    <button type="submit" class="btn btn-primary btn-pro px-5 py-3">
                        <i class="bi bi-save-fill me-2 fs-4"></i> Simpan Log Perjalanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const startInput = document.getElementById('odometer_terakhir');
    const endInput = document.getElementById('odometer_terkini');
    const jarakInput = document.getElementById('jumlah_jarak');

    function calculateDistance() {
        const start = parseFloat(startInput.value) || 0;
        const end = parseFloat(endInput.value) || 0;
        const distance = (end - start).toFixed(1);
        jarakInput.value = distance >= 0 ? distance : '';
    }

    startInput.addEventListener('input', calculateDistance);
    endInput.addEventListener('input', calculateDistance);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>