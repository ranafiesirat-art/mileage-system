<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
include "db.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Optional: padam gambar resit kalau ada
    $stmt = $conn->prepare("SELECT resit_path FROM mileage_minyak WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result && !empty($result['resit_path']) && file_exists($result['resit_path'])) {
        @unlink($result['resit_path']);
    }

    $stmt = $conn->prepare("DELETE FROM mileage_minyak WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Rekod inden minyak berjaya dipadam!";
    } else {
        $_SESSION['error'] = "Gagal padam rekod: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "ID rekod tidak sah.";
}

header("Location: senarai_minyak.php");
exit;
?>