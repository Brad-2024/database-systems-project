<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Create a user with role coach, name "Root User", and email "root@example.edu". The password will be "password123".
$connection = getDatabaseConnection();
$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

$query_user = "
    INSERT INTO users 
    (first_name, last_name, email, password_hash, role)
    VALUES ('Root', 'User', 'root@example.edu', ?, 'coach')
";


$stmt_user = mysqli_prepare($connection, $query_user);

if (!$stmt_user) {
    exit('Unable to prepare user insert.');
}

mysqli_stmt_bind_param($stmt_user, 's', $passwordHash);

if (!mysqli_stmt_execute($stmt_user)) {
    exit('Unable to create root user. Email may already exist.');
}

$user_id = mysqli_stmt_insert_id($stmt_user);

$query_coach = "INSERT INTO coach (user_id, phone_number) VALUES (?, '555-1234')";

$stmt_coach = mysqli_prepare($connection, $query_coach);

if (!$stmt_coach) {
    exit('Unable to prepare coach insert.');
}

mysqli_stmt_bind_param($stmt_coach, 'i', $user_id);

if (!mysqli_stmt_execute($stmt_coach)) {
    exit('Unable to create root user coach record.');
}

echo "Root user created successfully created";
