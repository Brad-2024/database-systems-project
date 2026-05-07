<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


// Only logged-in users should be able to view this page.
requireLogin();
requireRole(['athlete', 'trainer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('manage-injuries.php');
}

$injury_id = $_POST['injury_id'] ?? '';

$connection = getDatabaseConnection();

$sql = "UPDATE injury SET active = 'N' WHERE id = ?";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    setFlashData('error', 'Database error: ' . mysqli_error($connection));
    redirect('manage-injuries.php');
}

mysqli_stmt_bind_param($stmt, "i", $injury_id);

if (mysqli_stmt_execute($stmt)) {
    setFlashData('success', 'Injury marked as resolved.');
} else {
    setFlashData('error', 'Failed to update injury: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);

redirect('manage-injuries.php');
