<?php
// logout.php (dalam folder mileage-system)
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

// Redirect ke halaman login PARKING SYSTEM (bukan mileage login)
header("Location: /parking-system/login.php");
exit;
?>