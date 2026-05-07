<?php
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';
?>


<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <?php if ($userRole === 'coach'): ?>
            <li class="current"><a href="manage-athletes.php">Manage Athletes</a></li>
        <?php endif; ?>
        <?php if ($userRole === 'coach'): ?>
            <li class="current"><a href="team-dashboard.php">Dashboard</a></li>
        <?php endif; ?>
        <?php if ($userRole === 'trainer'): ?>
            <li class="current"><a href="manage-injuries.php">Manage Injuries</a></li>
        <?php endif; ?>
        <?php if ($userRole === 'athlete'): ?>
            <li class="current"><a href="manage-injuries.php">Manage Injuries</a></li>
        <?php endif; ?>
        <li><a href="account-details.php">Profile</a></li>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</nav>