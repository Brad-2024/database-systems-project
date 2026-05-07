<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['athlete']);

$meal_date = $_POST['date'] ?? '';
$meal_type = $_POST['meal_type'] ?? '';
$athlete_id = $_SESSION['role_id'] ?? -1;
$calories = $_POST['calories'] ?? '';
$fats = $_POST['fats'] ?? '';
$carbs = $_POST['carbs'] ?? '';
$sugars = $_POST['sugars'] ?? '';
$proteins = $_POST['protein'] ?? '';

$connection = getDatabaseConnection();

$sql = "INSERT INTO meal (athlete_id, date, meal_type, calories, fats, carbohydrates, sugar, protein) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    setFlashData('error', 'Database error: ' . mysqli_error($connection));
    redirect('team-dashboard.php');
}

mysqli_stmt_bind_param($stmt, "issiiiii", $athlete_id, $meal_date, $meal_type, $calories, $fats, $carbs, $sugars, $proteins);

if (mysqli_stmt_execute($stmt)) {
    setFlashData('success', 'Meal added successfully.');
} else {
    setFlashData('error', 'Failed to add meal: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);

redirect('team-dashboard.php');


