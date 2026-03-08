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
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --success: #198754;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
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
        .form-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--card-shadow);
            padding: 2.5rem;
            border: 1px solid rgba(13,110,253,0.1);
        }
        .form-floating > label { color: #6c757d; }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label { color: var(--primary); }
        .btn-submit {
            padding: 0.85rem 3rem;
            font-size: 1.15rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(25,135,84,0.3);
        }
        .hint-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
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
                <i class="bi bi-fuel-pump me-2"></i>Log Inden Minyak
            </h2>
            <p class="lead opacity-90 mb-0">Catat isian minyak kenderaan jabatan dengan tepat & mudah</p>
        </div>
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="form-card">
            <form method="POST" action="process_minyak.php" enctype="multipart/form-data" class="row g-4">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="pemegang_kad" class="form-control form-control-lg" id="pemegang_kad" required placeholder="Nama pemegang kad">
                        <label for="pemegang_kad">Pemegang Kad</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="no_kad_minyak" class="form-control form-control-lg" id="no_kad_minyak" required placeholder="Contoh: KM123456 atau 987654">
                        <label for="no_kad_minyak">No Kad MinYak</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" name="jumlah_isian_rm" step="0.01" class="form-control form-control-lg" id="jumlah_isian_rm" required min="0" placeholder="Contoh: 120.50">
                        <label for="jumlah_isian_rm">Jumlah Isian (RM)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" name="jumlah_isian_liter" step="0.01" class="form-control form-control-lg" id="jumlah_isian_liter" required min="0" placeholder="Contoh: 40.00">
                        <label for="jumlah_isian_liter">Jumlah Isian (Liter)</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <select name="jenis_minyak" class="form-select form-select-lg" id="jenis_minyak" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Petrol RON95">Petrol RON95</option>
                            <option value="Petrol RON97">Petrol RON97</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Diesel Euro 5">Diesel Euro 5</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                        <label for="jenis_minyak">Jenis Minyak</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" name="no_odometer" step="0.1" class="form-control form-control-lg" id="no_odometer" required min="0" placeholder="Contoh: 12345.0">
                        <label for="no_odometer">No. Odometer (KM)</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="date" name="tarikh" class="form-control form-control-lg" id="tarikh" value="<?= date('Y-m-d') ?>" required>
                        <label for="tarikh">Tarikh Isian</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="nama_syarikat" class="form-control form-control-lg" id="nama_syarikat" required placeholder="Contoh: Petronas">
                        <label for="nama_syarikat">Nama Syarikat</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="lokasi" class="form-control form-control-lg" id="lokasi" placeholder="Contoh: Stesen Petronas Pontian">
                        <label for="lokasi">Lokasi Stesen</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="rujukan_resit" class="form-control form-control-lg" id="rujukan_resit" placeholder="Nombor resit atau catatan">
                        <label for="rujukan_resit">Rujukan Resit / No Resit</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Upload Gambar Resit (jpg/png) – Optional</label>
                    <input type="file" name="resit_gambar" class="form-control form-control-lg" accept="image/jpeg,image/png">
                    <small class="text-muted hint-text">Saiz maksimum: 2MB. Biarkan kosong jika tiada gambar.</small>
                </div>
                <div class="col-12 text-end mt-5">
                    <button type="submit" class="btn btn-success btn-submit px-5">
                        <i class="bi bi-save me-2"></i> Simpan Log Inden Minyak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>