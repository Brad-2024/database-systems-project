<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


// Only logged-in users should be able to view this page.
requireLogin();
requireRole(['coach']);

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';

$connection = getDatabaseConnection();

$query = "SELECT id, first_name, role FROM users WHERE role != 'coach' ORDER BY role, first_name";
$result = mysqli_query($connection, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Athletes</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <?php if ($userRole === 'coach'): ?>
            <li class="current"><a href="manage-athletes.php">Manage Athletes</a></li>
        <?php endif; ?>
        <li><a href="account_details.php">Profile</a></li>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</nav>
<main class="page">

    <?php
    $errors = getFlashData('errors', []);
    $error = getFlashData('error');
    $success = getFlashData('success');
    ?>

    <?php if ($success): ?>
        <p class="success"><?php echo escape($success); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?php echo escape($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul class="error">
            <?php foreach ($errors as $message): ?>
                <li><?php echo escape($message); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="add-user.php">
        <label for="role">Role:</label>
        <select name="role" id="role" required>
            <option value="">-- Select Role --</option>
            <option value="coach">Coach</option>
            <option value="athlete">Athlete</option>
            <option value="trainer">Trainer</option>
        </select>

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" required>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <!-- Athlete-only fields -->
        <div id="athlete-fields" style="display: none;">
            <label for="height">Height:</label>
            <input type="text" name="height" id="height">

            <label for="weight">Weight:</label>
            <input type="number" name="weight" id="weight">

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob">

            <label for="sex">Sex:</label>
            <select name="sex" id="sex">
                <option value="">-- Select Sex --</option>
                <option value="M">M</option>
                <option value="F">F</option>
                <option value="U">U</option>
            </select>

            <label for="grad_year">Graduation Year:</label>
            <input type="number" name="grad_year" id="grad_year" min="1900" max="2100">

            <label for="event">Event:</label>
            <select name="event" id="event">
                <option value="">-- Select Event --</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="400">400</option>
                <option value="800">800</option>
                <option value="1500">1500</option>
                <option value="1600">1600</option>
                <option value="3200">3200</option>
                <option value="5000">5000</option>
                <option value="100H">100H</option>
                <option value="110H">110H</option>
                <option value="400H">400H</option>
                <option value="HJ">High Jump</option>
                <option value="LJ">Long Jump</option>
                <option value="TJ">Triple Jump</option>
                <option value="PV">Pole Vault</option>
                <option value="SP">Shot Put</option>
                <option value="DT">Discus</option>
                <option value="JT">Javelin</option>
            </select>
        </div>

        <!-- Coach/trainer-only fields -->
        <div id="staff-fields" style="display: none;">
            <label for="phone_number">Phone Number:</label>
            <input type="tel" name="phone_number" id="phone_number">
        </div>

        <button type="submit">Add User</button>
    </form>

    <form method="POST" action="delete-user.php">
        <label for="user_id">Delete user:</label>

        <select name="user_id" id="user_id" required>
            <option value="">-- Select User --</option>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                $name = ucwords(strtolower($row['first_name']));
                $role = ucwords(strtolower($row['role']));
                ?>

                <option value="<?php echo escape($row['id']); ?>">
                    <?php echo escape($name . ' : ' . $role); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" onclick="return confirm('Are you sure you want to delete this user?');">
            Delete
        </button>
    </form>
</main>
<script>
    const roleSelect = document.getElementById('role');
    const athleteFields = document.getElementById('athlete-fields');
    const staffFields = document.getElementById('staff-fields');

    function updateRoleFields() {
        const role = roleSelect.value;

        athleteFields.style.display = role === 'athlete' ? 'block' : 'none';
        staffFields.style.display = role === 'coach' || role === 'trainer' ? 'block' : 'none';
    }

    roleSelect.addEventListener('change', updateRoleFields);
    updateRoleFields();
</script>
</body>
</html>