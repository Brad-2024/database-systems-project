<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


// Only logged-in users should be able to view this page.
requireLogin();
requireRole(['athlete', 'trainer']);

$userID = $_SESSION['user_id'] ?? -1;
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';
$roleID = $_SESSION['role_id'] ?? -1;

$connection = getDatabaseConnection();

if ($userRole === 'athlete') {
    $query_injuries = "SELECT id, first_name, last_name, type, occurence_date, injury_id FROM ActiveInjuries WHERE id = '$roleID'";
    $result_injuries = mysqli_query($connection, $query_injuries);
    $injuries = mysqli_fetch_all($result_injuries, MYSQLI_ASSOC);
}

if ($userRole === 'trainer') {
    $query_injuries = "SELECT id, first_name, last_name, type, occurence_date, injury_id FROM ActiveInjuries";
    $result_injuries = mysqli_query($connection, $query_injuries);
    $injuries = mysqli_fetch_all($result_injuries, MYSQLI_ASSOC);

    $query_athlete_list = "SELECT Athlete.id, first_name, last_name FROM Athlete JOIN Users ON Athlete.user_id = Users.id WHERE role NOT IN ('coach', 'trainer') ORDER BY last_name";
    $athlete_list = mysqli_query($connection, $query_athlete_list);
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Injuries</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/nav.php'; ?>
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

    <?php if ($userRole === 'athlete'): ?>
        <!-- Active Injuries -->
        <div class="chart-panel">
            <h3>Active injuries</h3>
            <p class="chart-desc">Athletes currently under injury restriction</p>

            <?php if (count($injuries) === 0): ?>
                <p style="color:#888;font-size:0.875rem;">No active injuries — great news!</p>
            <?php else: ?>
                <table class="injury-table">
                    <thead>
                    <tr>
                        <th>Athlete</th>
                        <th>Injury type</th>
                        <th>Since</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($injuries as $injury): ?>
                        <tr>
                            <td><?= htmlspecialchars($injury['last_name'] . ', ' . $injury['first_name']) ?></td>
                            <td><?= htmlspecialchars($injury['type']) ?></td>
                            <td><?= htmlspecialchars(date('M j, Y', strtotime($injury['occurence_date']))) ?></td>
                            <td><button type="button" data-id="<?= $injury['injury_id'] ?>" class="injury-badge">Active</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div>
            <p>Add Treatment:</p>
            <form method="POST" action="add-treatment.php">

                <label for="type">Treatment Type:</label>
                <select name="type" id="type" required>
                    <option value="">-- Select Treatment Type --</option>
                    <option value="scrape">scrape</option>
                    <option value="medication">ice</option>
                </select>

                <label for="treatment_date">Treatment Date:</label>
                <input type="date" name="treatment_date" id="treatment_date" required>

                <select name="injury_id" id="injury_id">
                    <option value="">No Injury</option>

                    <?php foreach ($injuries as $injury): ?>
                        <option value="<?php echo escape($injury['injury_id']); ?>">
                            <?php echo escape($injury['type'] . ' (since ' . date('M j, Y', strtotime($injury['occurence_date'])) . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">
                    Add
                </button>
            </form>
        </div>
    <?php elseif ($userRole === 'trainer'): ?>
        <!-- Active Injuries -->
        <div class="chart-panel">
            <h3>Active injuries</h3>
            <p class="chart-desc">Athletes currently under injury restriction</p>

            <?php if (count($injuries) === 0): ?>
                <p style="color:#888;font-size:0.875rem;">No active injuries — great news!</p>
            <?php else: ?>
                <table class="injury-table">
                    <thead>
                    <tr>
                        <th>Athlete</th>
                        <th>Injury type</th>
                        <th>Since</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($injuries as $injury): ?>
                        <tr>
                            <td><?= htmlspecialchars($injury['last_name'] . ', ' . $injury['first_name']) ?></td>
                            <td><?= htmlspecialchars($injury['type']) ?></td>
                            <td><?= htmlspecialchars(date('M j, Y', strtotime($injury['occurence_date']))) ?></td>
                            <td><button type="button" data-id="<?= $injury['injury_id'] ?>" class="injury-badge">Active</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div>
            <form method="POST" action="add-injury.php">
                <label for="user_id">Add Injury:</label>

                <select name="user_id" id="user_id" required>
                    <option value="">-- Select User --</option>

                    <?php while ($row = mysqli_fetch_assoc($athlete_list)): ?>
                        <?php
                        $first_name = ucwords(strtolower($row['first_name']));
                        $last_name = ucwords(strtolower($row['last_name']));
                        ?>

                        <option value="<?php echo escape($row['id']); ?>">
                            <?php echo escape($first_name . ' ' . $last_name); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div id="add-injury-fields" style="display: none;">
                    <label for="type">Injury Type:</label>
                    <select name="type" id="type" required>
                        <option value="">-- Select Injury Type --</option>
                        <option value="fracture">Fracture</option>
                        <option value="broken bone">Broken Bone</option>
                    </select>

                    <label for="occurence_date">Occurence Date:</label>
                    <input type="date" name="occurence_date" id="occurence_date" required>
                </div>

                <button type="submit">
                    Add
                </button>
            </form>
        </div>
    <?php endif; ?>


</main>
<script>
    const userRole = '<?php echo $userRole; ?>';
    const roleSelect = document.getElementById('user_id');
    const injuryActiveBadge = document.getElementById('injury-active-badge');

    if (userRole === 'trainer') {
        const addInjuryFields = document.getElementById('add-injury-fields');

        function showInjuryAddFields() {
            const choice = roleSelect.value;
            addInjuryFields.style.display = choice !== '' ? 'block' : 'none';
        }
        roleSelect.addEventListener('change', showInjuryAddFields);
        showInjuryAddFields();
        }

    document.querySelectorAll('.injury-badge').forEach(btn => {
        btn.addEventListener('click', () => {
            const injuryId = btn.getAttribute('data-id');

            if (confirm('Mark this injury as resolved?')) {
                fetch(`update-injury.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `injury_id=${encodeURIComponent(injuryId)}`
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    })



</script>
</body>
</html>
