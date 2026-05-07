<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Enter a valid email address.';
}

if ($password === '') {
    $errors['password'] = 'Password is required.';
}

if (!empty($errors)) {
    setFlashData('errors', $errors);
    setFlashData('old_input', ['email' => $email]);
    redirect('login.php');
}

$connection = getDatabaseConnection();
$query = 'SELECT id, first_name, password_hash, role FROM users WHERE email = ? LIMIT 1';
$statement = mysqli_prepare($connection, $query);

if (!$statement) {
    exit('Unable to prepare the login query.');
}

mysqli_stmt_bind_param($statement, 's', $email);

if (!mysqli_stmt_execute($statement)) {
    mysqli_stmt_close($statement);
    exit('Unable to run the login query.');
}

mysqli_stmt_bind_result($statement, $userId, $userName, $hashedPassword, $userRole);

if (!mysqli_stmt_fetch($statement)) {
    mysqli_stmt_close($statement);
    setFlashData('login_error', 'Invalid email or password');
    setFlashData('old_input', ['email' => $email]);
    redirect('login.php');
}

mysqli_stmt_close($statement);

if (!password_verify($password, $hashedPassword)) {
    setFlashData('login_error', 'Invalid email or password');
    setFlashData('old_input', ['email' => $email]);
    redirect('login.php');
}

$sql_role = "SELECT id FROM {$userRole} WHERE user_id = ?";
$stmt_role = mysqli_prepare($connection, $sql_role);

if (!$stmt_role) {
    exit('Unable to prepare role query.');
}

mysqli_stmt_bind_param($stmt_role, 'i', $userId);

if (!mysqli_stmt_execute($stmt_role)) {
    mysqli_stmt_close($stmt_role);
    exit('Unable to run role query.');
}

mysqli_stmt_bind_result($stmt_role, $roleId);
mysqli_stmt_fetch($stmt_role);
mysqli_stmt_close($stmt_role);

session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $userName;
$_SESSION['user_role'] = $userRole;
$_SESSION['role_id'] = $roleId;

redirect('index.php');