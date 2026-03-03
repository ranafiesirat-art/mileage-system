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
    <title>Log Inden Minyak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --success: #2e7d32;
            --success-light: #4caf50;
            --success-glow: rgba(46, 125, 50, 0.2);
            --light-bg: #f8f9fa;
            --card-bg: white;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
            --transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #1a1a1a;
        }
        .main-content {
            margin-left: 260px;
            padding: 3.5rem 3rem;
            transition: var(--transition);
        }
        .header-banner {
            background: linear-gradient(135deg, var(--success) 0%, var(--success-light) 100%);
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
            border: 1px solid rgba(46, 125, 50, 0.06);
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
            border: 1px solid #dcedc8;
            transition: var(--transition);
        }
        .form-control-lg:focus, .form-select-lg:focus {
            border-color: var(--success-light);
            box-shadow: 0 0 0 0.3rem var(--success-glow);
            background: #fff;
        }
        .input-group-lg .input-group-text {
            background: rgba(46, 125, 50, 0.08);
            border: none;
            color: var(--success);
            border-radius: 0.75rem 0 0 0.75rem;
        }
        .btn-pro {
            padding: 0.85rem 2rem;
            font-size: 1.15rem;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(46,125,50,0.2);
        }
        .btn-pro:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(46,125,50,0.35);
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
        <div class="header-banner text-center position-relative">
            <div class="position-absolute top-0 end-0 p-4 opacity-50">
                <i class="bi bi-fuel-pump fs-1"></i>
            </div>
            <h2 class="page-title mb-2">Log Inden Minyak</h2>
            <p class="lead opacity-90 mb-0">Rekod pengisian bahan api kenderaan jabatan dengan tepat</p>
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
            <form method="POST" action="process_minyak.php" class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Pemegang Kad Inden</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                        <input type="text" name="pemegang_kad" class="form-control" required placeholder="Nama pemegang kad">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Jumlah Isian (RM)</label>
                    <input type="number" name="jumlah_isian_rm" step="0.01" class="form-control form-control-lg" required min="0" placeholder="Contoh: 120.50">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Jumlah Isian (Liter)</label>
                    <input type="number" name="jumlah_isian_liter" step="0.01" class="form-control form-control-lg" required min="0" placeholder="Contoh: 40.00">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jenis Minyak</label>
                    <select name="jenis_minyak" class="form-select form-select-lg" required>
                        <option value="" disabled selected hidden>-- Pilih Jenis Minyak --</option>
                        <option value="Petrol RON95">Petrol RON95</option>
                        <option value="Petrol RON97">Petrol RON97</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Diesel Euro 5">Diesel Euro 5</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">No. Odometer Semasa (KM)</label>
                    <input type="number" name="no_odometer" step="0.1" class="form-control form-control-lg" required min="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tarikh Isian</label>
                    <input type="date" name="tarikh" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Syarikat Stesen</label>
                    <input type="text" name="nama_syarikat" class="form-control form-control-lg" required placeholder="Contoh: Petronas, Shell, Caltex">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lokasi Stesen</label>
                    <input type="text" name="lokasi" class="form-control form-control-lg" placeholder="Contoh: Stesen Petronas Pontian Kechil">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Rujukan Resit / No Resit</label>
                    <input type="text" name="rujukan_resit" class="form-control form-control-lg" placeholder="Masukkan nombor resit atau catatan tambahan">
                </div>

                <div class="col-md-6">
                    <label class="form-label">No. Pendaftaran Kenderaan</label>
                    <input type="text" name="no_pendaftaran" class="form-control form-control-lg" required 
                           placeholder="Contoh: JDA1234" pattern="[A-Za-z0-9-]+" title="Huruf, nombor & tanda '-' dibenarkan">
                </div>

                <div class="col-12 text-end mt-5">
                    <button type="submit" class="btn btn-success btn-pro px-5 py-3">
                        <i class="bi bi-save-fill me-2 fs-4"></i> Simpan Log Inden Minyak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>