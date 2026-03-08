<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

// jika dah login pergi ke log harian
header("Location: senarai_harian.php");
exit;
?>