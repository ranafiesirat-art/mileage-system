<?php
// login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";  // pastikan fail db.php wujud & betul sambungan database

// ===============================
// SSO TOKEN dari Parking System
// ===============================
if (isset($_GET['token'])) {

    $secret = "kulai_81000_strong_secret_81000";

    $decoded = base64_decode($_GET['token']);
    $parts = explode('|', $decoded);

    if (count($parts) === 3) {

        list($username, $timestamp, $hash) = $parts;

        $expected_hash = hash('sha256', $username . '|' . $timestamp . '|' . $secret);

        // Token sah?
        if (hash_equals($expected_hash, $hash)) {

            // Token belum expired (5 minit)
            if (time() - $timestamp < 300) {

                $users = [
                    'admin' => 'Big Boss',
                    'nafie' => 'Mohd. Ranafie',
                    'raime' => 'Mohd. Raime',
                    'tasha' => 'Natasha Nur Afiqah',
                    'putri' => 'Nor Syaputri',
                    'lisa' => 'Marlisa Syahirah'
                ];

                if (isset($users[$username])) {

                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['nama_pegawai'] = $users[$username];
                    $_SESSION['user_id'] = $username;

                    header("Location: index.php");
                    exit;
                }
            }
        }
    }
}

// Jika dah login → terus ke dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

// Senarai pengguna (hardcoded) → nanti boleh pindah ke database
$users = [
    'admin' => [
        'password' => '$2y$10$T4Z5iH9iDhZB/N/dYIfP9.YQ.1ugRU0eqwxBm.dSH75cHiacDkrC2', // GANTI HASH SEBENAR
        'nama'    => 'Big Boss'
    ],
    'nafie' => [
        'password' => '$2y$10$YV4MvJPLDv.l72Datep67exbaYP8dfAW9rOcQJKnOUnNflbvu4Ssm', // GANTI HASH SEBENAR
        'nama'    => 'Mohd. Ranafie'
    ],
    'raime' => [
        'password' => '$2y$10$MHpBUeo7nXJjQf1kv8cLJ..SKu5B9XlJquXtkRKjFqHGRlquYk8Vi', // GANTI
        'nama'    => 'Mohd. Raime'
    ],
    'tasha' => [
        'password' => '$2y$10$7fpZgV7xBHnIRAo1AA9yY.AfpAzbk03ozLoXvcocQ592.hlPUH1wG', // GANTI
        'nama'    => 'Natasha Nur Afiqah'
    ],
    'putri' => [
        'password' => '$2y$10$gI8xyDhsXRwjvcTWjkrUmuXGath2Tqz7Nh/ha0vAXeMG9.3Bw.ydi', // GANTI
        'nama'    => 'Nor Syaputri'
    ],
    'lisa' => [
        'password' => '$2y$10$/a7410z7RLj.hEJlTnrMBuxkKeTGwJHTdmr.UpyVrL.qGkwze3sja', // GANTI
        'nama'    => 'Marlisa Syahirah'
    ],
];

// Proses login bila tekan butang
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Sila isi username dan password!";
    } elseif (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        // Login berjaya
        $_SESSION['loggedin']    = true;
        $_SESSION['username']    = $username;
        $_SESSION['nama_pegawai']= $users[$username]['nama'];
        $_SESSION['user_id']     = $username;   // sementara guna username sebagai ID

        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

// Jika ada token SSO (untuk masa depan)
if (isset($_GET['token'])) {
    $error = "SSO belum disediakan sepenuhnya. Sila login biasa.";
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mileage Kenderaan</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: #0d6efd;
            color: white;
            padding: 2.5rem 1.5rem;
            text-align: center;
        }
        .login-body {
            padding: 2.5rem;
        }
        .btn-login {
            padding: 0.75rem;
            font-size: 1.1rem;
            border-radius: 50px;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
        }
        .form-floating > label {
            color: #6c757d;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h3 class="mb-0 fw-bold">
                <i class="bi bi-speedometer2 me-2"></i>
                Login Mileage JQA2435
            </h3>
            <small>Sistem Log Perjalanan & Inden Minyak</small>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-floating mb-4">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Username" required autofocus>
                    <label for="username">Username</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Password" required>
                    <label for="password">Password</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Log Masuk
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>
</body>
</html>