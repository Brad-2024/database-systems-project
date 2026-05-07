<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


// Only logged-in users should be able to view this page.
requireLogin();
requireRole(['athlete']);

$injury_id = !empty($_POST['injury_id']) ? $_POST['injury_id'] : null;
$treatment_type = $_POST['type'] ?? '';
$treatment_date = $_POST['treatment_date'] ?? '';
$athlete_id = $_SESSION['role_id'] ?? -1;

$connection = getDatabaseConnection();

$sql = "INSERT INTO treatment (athlete_id, injury_id, type, date) VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    setFlashData('error', 'Database error: ' . mysqli_error($connection));
    redirect('manage-injuries.php');
}

mysqli_stmt_bind_param($stmt, "iiss", $athlete_id, $injury_id, $treatment_type, $treatment_date);

if (mysqli_stmt_execute($stmt)) {
    setFlashData('success', 'Treatment added successfully.');
} else {
    setFlashData('error', 'Failed to add treatment: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);

redirect('manage-injuries.php');

