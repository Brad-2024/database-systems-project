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
<?php require_once __DIR__ . '/../includes/nav.php'; ?>
<main class="page">
    <section class="card">
        <h1>Dashboard</h1>
        <p class="welcome">Welcome, <?php echo escape($userName); ?></p>
    </section>
</main>
</body>
</html>