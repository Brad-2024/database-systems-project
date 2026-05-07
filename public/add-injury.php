<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['trainer', 'athlete']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('manage-injuries.php');
}

$userID = $_SESSION['user_id'] ?? -1;
$userRole = $_SESSION['user_role'] ?? 'Role';

$athlete_id = $_POST['user_id'] ?? '';
$type = trim($_POST['type'] ?? '');
$occurence_date = trim($_POST['occurence_date'] ?? '');

$errors = [];

if ($userRole === 'trainer') {
    if ($athlete_id === '') {
        $errors['user_id'] = 'Select an athlete.';
    }
}

if ($type === '') {
    $errors['type'] = 'Injury type is required.';
}

if ($occurence_date === '') {
    $errors['occurence_date'] = 'Occurence date is required.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $occurence_date)) {
    $errors['occurence_date'] = 'Enter a valid date (YYYY-MM-DD).';
}

if (!empty($errors)) {
    setFlashData('errors', $errors);
    redirect('manage-injuries.php');
}

$connection = getDatabaseConnection();

try {
    $query = "INSERT INTO injury 
    (athlete_id, type, occurence_date, active) 
    VALUES (?, ?, ?, 'Y')";

    $stmt = mysqli_prepare($connection, $query);

    if (!$stmt) {
        throw new Exception(mysqli_error($connection));
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iss',
        $athlete_id,
        $type,
        $occurence_date
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($connection);

    setFlashData('success', 'Injury added successfully.');
    redirect('manage-injuries.php');

} catch (Exception $e) {
    setFlashData('error', 'Unable to add injury: ' . $e->getMessage());
    redirect('manage-injuries.php');
}
