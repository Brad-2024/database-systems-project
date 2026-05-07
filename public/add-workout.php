<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['athlete']);

$workout_date = $_POST['date'] ?? '';
$workout_success = $_POST['workout_success'] ?? '';
$athlete_id = $_SESSION['role_id'] ?? -1;

$set_distances = $_POST['distance'] ?? [];
$set_times = $_POST['time'] ?? [];

$connection = getDatabaseConnection();

$sql = "INSERT INTO workout (athlete_id, date, workout_success) VALUES (?, ?, ?)";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    setFlashData('error', 'Database error: ' . mysqli_error($connection));
    redirect('team-dashboard.php');
}

mysqli_stmt_bind_param($stmt, "iss", $athlete_id, $workout_date, $workout_success);

if (mysqli_stmt_execute($stmt)) {
    $workout_id = mysqli_insert_id($connection);

    for ($i = 0; $i < count($set_distances); $i++) {
        $distance = $set_distances[$i];
        $time = $set_times[$i];

        if ($distance !== '' && $time !== '') {
            $set_sql = "INSERT INTO workout_set (workout_id, distance, time_seconds) VALUES (?, ?, ?)";
            $set_stmt = mysqli_prepare($connection, $set_sql);

            if ($set_stmt) {
                mysqli_stmt_bind_param($set_stmt, "iid", $workout_id, $distance, $time);
                mysqli_stmt_execute($set_stmt);
                mysqli_stmt_close($set_stmt);
            }
        }
    }

    setFlashData('success', 'Workout added successfully.');
} else {
    setFlashData('error', 'Failed to add workout: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);

redirect('team-dashboard.php');
