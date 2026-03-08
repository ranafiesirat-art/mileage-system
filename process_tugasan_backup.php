<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: edit_harian.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$tugasan = trim($_POST['tugasan'] ?? '');

if ($id <= 0) {
    $_SESSION['error'] = "ID tidak sah.";
    header("Location: edit_harian.php?id=$id");
    exit;
}

$stmt = $conn->prepare("UPDATE mileage_harian SET tugasan = ? WHERE id = ?");
$stmt->bind_param("si", $tugasan, $id);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $_SESSION['success'] = "Tugasan berjaya dikemas kini! (Affected: $affected)";
    } else {
        $_SESSION['error'] = "Tiada perubahan – ID tak jumpa atau nilai sama.";
    }
} else {
    $_SESSION['error'] = "Gagal update: " . $stmt->error;
}

$stmt->close();
header("Location: edit_harian.php?id=$id");
exit;
?>