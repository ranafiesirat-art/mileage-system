<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = [];
$error = '';
if ($id > 0) {
    $stmt = $conn->prepare("
        SELECT mh.*, k.no_pendaftaran_kenderaan 
        FROM mileage_harian mh 
        LEFT JOIN kenderaan k ON mh.kenderaan_id = k.id 
        WHERE mh.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $error = "Rekod tidak dijumpai untuk ID $id.";
    }
    $stmt->close();
}

// Pastikan tarikh dalam format betul untuk input type="date"
$tarikh_value = $row['tarikh'] ?? '';
if ($tarikh_value === '0000-00-00' || empty($tarikh_value)) {
    $tarikh_value = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Log Perjalanan Harian</title>
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
        #jumlah_jarak {
            background-color: #e9ecef;
            cursor: not-allowed;
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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <div class="page-header text-center">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-journal-check me-2"></i>Edit Log Perjalanan Harian
            </h2>
            <p class="lead opacity-90 mb-0">Kemas kini maklumat perjalanan</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (empty($row) && !$error): ?>
            <div class="alert alert-warning">Tiada rekod dipilih untuk diedit.</div>
        <?php else: ?>
            <div class="form-card">
                <form method="POST" action="process_harian.php" class="row g-4">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select name="bulan" class="form-select form-select-lg" id="bulan" required>
                                <option value="">-- Pilih Bulan --</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= ($row['bulan'] ?? date('n')) == $m ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0,0,0,$m,1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <label for="bulan">Bulan</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select name="tahun" class="form-select form-select-lg" id="tahun" required>
                                <option value="">-- Pilih Tahun --</option>
                                <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($row['tahun'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <label for="tahun">Tahun</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="no_siri_buku" class="form-control form-control-lg" id="no_siri_buku" placeholder="Contoh: SRB/2026/00123" value="<?= htmlspecialchars($row['no_siri_buku'] ?? '') ?>">
                            <label for="no_siri_buku">No Siri Buku</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="no_kenderaan" class="form-control form-control-lg" id="no_kenderaan" required placeholder="Contoh: JDA 1234" value="<?= htmlspecialchars($row['no_pendaftaran_kenderaan'] ?? '') ?>">
                            <label for="no_kenderaan">No Kenderaan</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="nama_pemandu" class="form-control form-control-lg" id="nama_pemandu" required placeholder="Nama pemandu kenderaan" value="<?= htmlspecialchars($row['nama_pemandu'] ?? '') ?>">
                            <label for="nama_pemandu">Nama Pemandu</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="nama_pengguna" class="form-control form-control-lg" id="nama_pengguna" required placeholder="Nama pegawai / pengguna" value="<?= htmlspecialchars($row['nama_pengguna'] ?? '') ?>">
                            <label for="nama_pengguna">Nama Pengguna</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="jabatan" class="form-control form-control-lg" id="jabatan" required placeholder="Contoh: Seksyen Pentadbiran" value="<?= htmlspecialchars($row['jabatan'] ?? '') ?>">
                            <label for="jabatan">Jabatan</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" name="tarikh" class="form-control form-control-lg" id="tarikh" value="<?= htmlspecialchars($tarikh_value) ?>" required>
                            <label for="tarikh">Tarikh</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea name="keterangan_tugasan" class="form-control form-control-lg" id="keterangan_tugasan" rows="5" placeholder="Catat lokasi yang dilawati, nama orang yang ditemui, perkara penting, dll..."><?= htmlspecialchars($row['keterangan_tugasan'] ?? '') ?></textarea>
                            <label for="keterangan_tugasan">Keterangan Tugasan</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" name="odo_mula" step="0.1" class="form-control form-control-lg" id="odo_mula" required min="0" placeholder="Contoh: 12345.0" value="<?= htmlspecialchars($row['odo_mula'] ?? '') ?>">
                            <label for="odo_mula">Odo Meter Mula (KM)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" name="odo_akhir" step="0.1" class="form-control form-control-lg" id="odo_akhir" required min="0" placeholder="Contoh: 12500.0" value="<?= htmlspecialchars($row['odo_akhir'] ?? '') ?>">
                            <label for="odo_akhir">Odo Meter Akhir (KM)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" name="jumlah_jarak" step="0.1" class="form-control form-control-lg" id="jumlah_jarak" required min="0" placeholder="Akan dikira secara automatik" readonly value="<?= htmlspecialchars($row['jumlah_jarak'] ?? ($row['odo_akhir'] - $row['odo_mula'] ?? '')) ?>">
                            <label for="jumlah_jarak">Jumlah Jarak (KM)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="time" name="masa_pergi" class="form-control form-control-lg" id="masa_pergi" required value="<?= htmlspecialchars($row['masa_pergi'] ?? '') ?>">
                            <label for="masa_pergi">Masa Pergi</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="time" name="masa_pulang" class="form-control form-control-lg" id="masa_pulang" required value="<?= htmlspecialchars($row['masa_pulang'] ?? '') ?>">
                            <label for="masa_pulang">Masa Pulang</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea name="catatan" class="form-control form-control-lg" id="catatan" rows="5" placeholder="Catat lokasi yang dilawati, nama orang yang ditemui, perkara penting, dll..."><?= htmlspecialchars($row['catatan'] ?? '') ?></textarea>
                            <label for="catatan">Catatan (Lokasi yang dilawati dll)</label>
                        </div>
                    </div>
                    <div class="col-12 text-end mt-5">
                        <a href="senarai_harian.php" class="btn btn-secondary px-5 me-3">Batal</a>
                        <button type="submit" class="btn btn-success btn-submit px-5">
                            <i class="bi bi-save me-2"></i> Kemas Kini Log
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    const odoMula = document.getElementById('odo_mula');
    const odoAkhir = document.getElementById('odo_akhir');
    const jumlahJarak = document.getElementById('jumlah_jarak');
    function calculateDistance() {
        const mula = parseFloat(odoMula.value) || 0;
        const akhir = parseFloat(odoAkhir.value) || 0;
        const jarak = akhir - mula;
        if (akhir > 0 && mula > 0) {
            jumlahJarak.value = jarak.toFixed(1);
            if (jarak < 0) {
                jumlahJarak.style.color = 'red';
            } else {
                jumlahJarak.style.color = 'inherit';
            }
        } else {
            jumlahJarak.value = '';
        }
    }
    if (odoMula && odoAkhir) {
        odoMula.addEventListener('input', calculateDistance);
        odoAkhir.addEventListener('input', calculateDistance);
        calculateDistance();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>