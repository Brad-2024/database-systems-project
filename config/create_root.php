<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Create a user with role coach, name "Root User", and email "root@example.edu". The password will be "password123".
$connection = getDatabaseConnection();
$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

$query = "
    INSERT INTO users 
    (first_name, last_name, email, password_hash, role)
    VALUES ('Root', 'User', 'root@example.edu', ?, 'coach')
";

$stmt = mysqli_prepare($connection, $query);

if (!$stmt) {
    exit('Unable to prepare user insert.');
}

mysqli_stmt_bind_param($stmt, 's', $passwordHash);

if (!mysqli_stmt_execute($stmt)) {
    exit('Unable to create root user. Email may already exist.');
}

echo "Root user created successfully created";
