<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['coach']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('manage-athletes.php');
}

$role = $_POST['role'] ?? '';
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$height = trim($_POST['height'] ?? '');
$weight = trim($_POST['weight'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$sex = $_POST['sex'] ?? '';
$gradYear = trim($_POST['grad_year'] ?? '');
$event = $_POST['event'] ?? '';
$tffrs_url = $_POST['tffrs_url'] ?? '';

$phoneNumber = trim($_POST['phone_number'] ?? '');

$errors = [];

$allowedRoles = ['coach', 'athlete', 'trainer'];

if (!in_array($role, $allowedRoles)) {
    $errors['role'] = 'Select a valid role.';
}

if ($firstName === '') {
    $errors['first_name'] = 'First name is required.';
}

if ($lastName === '') {
    $errors['last_name'] = 'Last name is required.';
}

if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Enter a valid email.';
}

if ($password === '') {
    $errors['password'] = 'Password is required.';
}

if ($role === 'athlete') {
    if ($height === '') $errors['height'] = 'Height is required.';
    if ($weight === '') $errors['weight'] = 'Weight is required.';
    if ($dob === '') $errors['dob'] = 'Date of birth is required.';

    if (!in_array($sex, ['M', 'F', 'U'])) {
        $errors['sex'] = 'Select a valid sex.';
    }

    if ($gradYear === '') {
        $errors['grad_year'] = 'Graduation year is required.';
    }

    if ($event === '') {
        $errors['event'] = 'Event is required.';
    }

    if ($tffrs_url === '') {
        $errors['tffrs_url'] = 'Event is required.';
    }
}

if ($role === 'coach' || $role === 'trainer') {
    if ($phoneNumber === '') {
        $errors['phone_number'] = 'Phone number is required.';
    }
}

if (!empty($errors)) {
    setFlashData('errors', $errors);
    setFlashData('old_input', $_POST);
    redirect('manage-athletes.php');
}

$connection = getDatabaseConnection();

mysqli_begin_transaction($connection);

try {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $query = "
        INSERT INTO users 
        (first_name, last_name, email, password_hash, role)
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($connection, $query);

    if (!$stmt) {
        throw new Exception('Unable to prepare user insert.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssss',
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $role
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Unable to create user. Email may already exist.');
    }

    $userId = mysqli_insert_id($connection);

    mysqli_stmt_close($stmt);

    if ($role === 'athlete') {
        $query = "
            INSERT INTO athlete
            (user_id, height, weight, dob, sex, grad_year, event, tffrs_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($connection, $query);

        if (!$stmt) {
            throw new Exception('Unable to prepare athlete insert.');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'isisssss',
            $userId,
            $height,
            $weight,
            $dob,
            $sex,
            $gradYear,
            $event,
            $tffrs_url
        );

    } elseif ($role === 'coach') {
        $query = "
            INSERT INTO coach
            (user_id, phone_number)
            VALUES (?, ?)
        ";

        $stmt = mysqli_prepare($connection, $query);

        if (!$stmt) {
            throw new Exception('Unable to prepare coach insert.');
        }

        mysqli_stmt_bind_param($stmt, 'is', $userId, $phoneNumber);

    } elseif ($role === 'trainer') {
        $query = "
            INSERT INTO trainer
            (user_id, phone_number)
            VALUES (?, ?)
        ";

        $stmt = mysqli_prepare($connection, $query);

        if (!$stmt) {
            throw new Exception('Unable to prepare trainer insert.');
        }

        mysqli_stmt_bind_param($stmt, 'is', $userId, $phoneNumber);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Unable to create role-specific profile.');
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($connection);

    setFlashData('success', 'User added successfully.');
    redirect('manage-athletes.php');

} catch (Exception $e) {
    mysqli_rollback($connection);

    setFlashData('error', $e->getMessage());
    redirect('manage-athletes.php');
}