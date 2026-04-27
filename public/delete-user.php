<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['coach']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$userId = $_POST['user_id'] ?? '';

if ($userId === '' || !ctype_digit($userId)) {
    exit('Invalid user selected.');
}

$connection = getDatabaseConnection();

mysqli_begin_transaction($connection);

try {
    // Get the user's role first
    $stmt = mysqli_prepare($connection, "SELECT role FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $role);

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("User not found.");
    }

    mysqli_stmt_close($stmt);

    // Do not allow deleting coaches
    if ($role === 'coach') {
        throw new Exception("You cannot delete a coach.");
    }

    // Delete from role-specific table
    if ($role === 'athlete') {
        $stmt = mysqli_prepare($connection, "DELETE FROM athletes WHERE user_id = ?");
    } elseif ($role === 'trainer') {
        $stmt = mysqli_prepare($connection, "DELETE FROM trainers WHERE user_id = ?");
    } else {
        throw new Exception("Invalid role.");
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Delete from users table
    $stmt = mysqli_prepare($connection, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($connection);

    setFlashData('success', 'User deleted successfully.');
    redirect('dashboard.php');

} catch (Exception $e) {
    mysqli_rollback($connection);

    setFlashData('error', $e->getMessage());
    redirect('dashboard.php');
}