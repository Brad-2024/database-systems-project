<?php

require_once __DIR__ . '/../includes/auth.php';

// Only logged-in users should be able to view this page.
requireLogin();

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<nav>
    <ul>
        <li class="current"><a href="index.php">Home</a></li>
        <?php if ($userRole === 'coach'): ?>
            <li><a href="manage-athletes.php">Manage Athletes</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</nav>
<main class="page">
    <section class="card">
        <h1>Dashboard</h1>
        <p class="welcome">Welcome, <?php echo escape($userName); ?></p>
    </section>
</main>
</body>
</html>