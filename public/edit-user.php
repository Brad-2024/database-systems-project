<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('account-details.php');
}

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';
$userID = $_SESSION['user_id'] ?? -1;

$connection = getDatabaseConnection();

mysqli_begin_transaction($connection);

$password = $_POST['password'] ?? '';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$fields_user = [];
$params_user = [];
$types_user = "";

$fields_other = [];
$params_other = [];
$types_other = "";

function updateOther($sql_other, $types_other, $params_other, $userID, $connection) {
        $params_other[] = $userID;
        $types_other .= "i";

        $stmt_other = mysqli_prepare($connection, $sql_other);

        if (!$stmt_other) {
            throw new Exception(mysqli_error($connection));
        }

        mysqli_stmt_bind_param($stmt_other, $types_other, ...$params_other);

        if (!mysqli_stmt_execute($stmt_other)) {
            echo "Role specific data update failed:" . mysqli_stmt_error($stmt_other);
        }

        mysqli_stmt_close($stmt_other);
}

try {

    if (!empty($_POST['first_name'])) {
        $fields_user[] = "first_name = ?";
        $params_user[] = $_POST['first_name'];
        $types_user .= "s";
    }
    if (!empty($_POST['last_name'])) {
        $fields_user[] = "last_name = ?";
        $params_user[] = $_POST['last_name'];
        $types_user .= "s";
    }
    if (!empty($_POST['email'])) {
        $fields_user[] = "email = ?";
        $params_user[] = $_POST['email'];
        $types_user .= "s";
    }
    if (!empty($_POST['password'])) {
        $fields_user[] = "password_hash = ?";
        $params_user[] = $password_hash;
        $types_user .= "s";
    }

    if (!empty($fields_user)) {
        $sql_user = "UPDATE users SET " . implode(', ', $fields_user) . " WHERE id = ?";
        $params_user[] = $userID;
        $types_user .= "i";

        $stmt_user = mysqli_prepare($connection, $sql_user);

        if (!$stmt_user) {
            throw new Exception(mysqli_error($connection));
        }

        mysqli_stmt_bind_param($stmt_user, $types_user, ...$params_user);

        if (!mysqli_stmt_execute($stmt_user)) {
            echo "User update failed:" . mysqli_stmt_error($stmt_user);
        }

        mysqli_stmt_close($stmt_user);
    }

    if ($userRole === 'coach') {
        if (!empty($_POST['phone_number'])) {
            $fields_other[] = "phone_number = ?";
            $params_other[] = $_POST['phone_number'];
            $types_other .= "s";
        }

        if (!empty($fields_other)) {
            $sql_other = "UPDATE coach SET " . implode(', ', $fields_other) . " WHERE user_id = ?";
            updateOther($sql_other, $types_other, $params_other, $userID, $connection);
        }

    } elseif ($userRole === 'athlete') {
        if (!empty($_POST['height'])) {
            $fields_other[] = "height = ?";
            $params_other[] = $_POST['height'];
            $types_other .= "s";
        }
        if (!empty($_POST['weight'])) {
            $fields_other[] = "weight = ?";
            $params_other[] = $_POST['weight'];
            $types_other .= "i";
        }
        if (!empty($_POST['dob'])) {
            $fields_other[] = "dob = ?";
            $params_other[] = $_POST['dob'];
            $types_other .= "s";
        }
        if (!empty($_POST['sex'])) {
            $fields_other[] = "sex = ?";
            $params_other[] = $_POST['sex'];
            $types_other .= "s";
        }
        if (!empty($_POST['grad_year'])) {
            $fields_other[] = "grad_year = ?";
            $params_other[] = $_POST['grad_year'];
            $types_other .= "s";
        }
        if (!empty($_POST['event'])) {
            $fields_other[] = "event = ?";
            $params_other[] = $_POST['event'];
            $types_other .= "s";
        }
        if (!empty($_POST['tffrs_url'])) {
            $fields_other[] = "tffrs_url = ?";
            $params_other[] = $_POST['tffrs_url'];
            $types_other .= "s";
        }

        if (!empty($fields_other)) {
            $sql_other = "UPDATE athlete SET " . implode(', ', $fields_other) . " WHERE user_id = ?";
            updateOther($sql_other, $types_other, $params_other, $userID, $connection);
        }

    } elseif ($userRole === 'trainer') {
        if (!empty($_POST['phone_number'])) {
            $fields_other[] = "phone_number = ?";
            $params_other[] = $_POST['phone_number'];
            $types_other .= "s";
        }

        if (!empty($fields_other)) {
            $sql_other = "UPDATE trainer SET " . implode(', ', $fields_other) . " WHERE user_id = ?";
            updateOther($sql_other, $types_other, $params_other, $userID, $connection);
        }

    } else {
        throw new Exception("Invalid role.");
    }

    mysqli_commit($connection);

    setFlashData('success', 'User Profile Changed successfully.');
    redirect('account-details.php');

} catch (Exception $e) {
    mysqli_rollback($connection);

    setFlashData('error', $e->getMessage());
    redirect('account-details.php');
}