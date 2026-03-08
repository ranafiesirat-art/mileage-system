<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("DELETE FROM mileage_harian WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Rekod perjalanan berjaya dipadam!";
    } else {
        $_SESSION['error'] = "Gagal padam rekod: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "ID rekod tidak sah.";
}

header("Location: senarai_harian.php");
exit;
?>