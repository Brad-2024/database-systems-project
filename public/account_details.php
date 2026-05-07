<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Role';
$userID = $_SESSION['user_id'] ?? -1;

$connection = getDatabaseConnection();

$query_user = "SELECT first_name, last_name, email FROM users WHERE id = '$userID'";
$result_user = mysqli_query($connection, $query_user);
$row1 = mysqli_fetch_assoc($result_user);

if ($userRole == 'trainer') {
    $query_trainer = "SELECT phone_number FROM trainer WHERE user_id = '$userID'";
    $result_trainer = mysqli_query($connection, $query_trainer);
    $row2 = mysqli_fetch_assoc($result_trainer);

}

if ($userRole == 'coach') {
    $query_coach = "SELECT phone_number FROM coach WHERE user_id = '$userID'";
    $result_coach = mysqli_query($connection, $query_coach);
    $row3 = mysqli_fetch_assoc($result_coach);
}

#if role = "athlete"
if ($userRole == 'athlete') {
    $query_athlete = "SELECT height, weight, dob, sex, grad_year, event, tffrs_url FROM athlete WHERE user_id = '$userID'";
    $result_athlete = mysqli_query($connection, $query_athlete);
    $row4 = mysqli_fetch_assoc($result_athlete);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/nav.php'; ?>
    <?php if ($userRole === 'coach'): ?>
        <?php
        $first_name = ucwords(strtolower($row1['first_name']));
        $last_name = ucwords(strtolower($row1['last_name']));
        $email = ucwords(strtolower($row1['email']));
        $phone_number = ucwords(strtolower($row3['phone_number']));
        ?>
        <div class="details_show" id="details_show" style="display: block">
            <p> <?php echo escape('Name : ' . $first_name . ' ' . $last_name); ?> </p>
            <p> <?php echo escape('Email : ' . $email); ?> </p>
            <p> <?php echo escape('Phone # : ' . $phone_number); ?> </p>
            <button id="edit_button" type="button">Edit</button>
        </div>

        <div class="details_edit" id="details_edit" style="display: none;">
            <form method="POST" action="edit_user.php">
                <label for="first_name">First Name:</label>
                <input type="text" placeholder="<?php echo $first_name?>" name="first_name" id="first_name">

                <label for="last_name">Last Name:</label>
                <input type="text" placeholder="<?php echo $last_name?>" name="last_name" id="last_name">

                <label for="email">Email:</label>
                <input type="email" placeholder="<?php echo $email?>" name="email" id="email">

                <label for="password">Password:</label>
                <input type="password" name="password" id="password">

                <label for="phone_number">Phone Number:</label>
                <input type="tel" placeholder="<?php echo $phone_number?>" name="phone_number" id="phone_number">
                <button type="submit" onclick="return confirm('Are you sure you want to save your changes?');">Save Changes</button>
            </form>
            <button id="cancel_button" type="button">Cancel</button>
        </div>
    <?php endif; ?>

    <?php if ($userRole === 'trainer'): ?>
        <?php
        $first_name = ucwords(strtolower($row1['first_name']));
        $last_name = ucwords(strtolower($row1['last_name']));
        $email = ucwords(strtolower($row1['email']));
        $phone_number = ucwords(strtolower($row2['phone_number']));
        ?>
        <div class="details_show" id="details_show" style="display: block">
            <p> <?php echo escape('Name : ' . $first_name . ' ' . $last_name); ?> </p>
            <p> <?php echo escape('Email : ' . $email); ?> </p>
            <p> <?php echo escape('Phone # : ' . $phone_number); ?> </p>
            <button id="edit_button" type="button">Edit</button>
        </div>

        <div class="details_edit" id="details_edit" style="display: none;">
            <form method="POST" action="edit_user.php">
                label for="first_name">First Name:</label>
                <input type="text" placeholder="<?php echo $first_name?>" name="first_name" id="first_name">

                <label for="last_name">Last Name:</label>
                <input type="text" placeholder="<?php echo $last_name?>" name="last_name" id="last_name">

                <label for="email">Email:</label>
                <input type="email" placeholder="<?php echo $email?>" name="email" id="email">

                <label for="password">Password:</label>
                <input type="password" name="password" id="password">

                <label for="phone_number">Phone Number:</label>
                <input type="tel" placeholder="<?php echo $phone_number?>" name="phone_number" id="phone_number">
                <button type="submit" onclick="return confirm('Are you sure you want to save your changes?');">Save Changes</button>
            </form>
            <button id="cancel_button" type="button">Cancel</button>
        </div>
    <?php endif; ?>

    <?php if ($userRole === 'athlete'): ?>
        <?php
        $first_name = ucwords(strtolower($row1['first_name']));
        $last_name = ucwords(strtolower($row1['last_name']));
        $email = ucwords(strtolower($row1['email']));
        $height = ucwords(strtolower($row4['height']));
        $weight = ucwords(strtolower($row4['weight']));
        $dob = ucwords(strtolower($row4['dob']));
        $birthDate = explode("/", $dob);
        $age = calculateAge($dob);
        $sex = ucwords(strtolower($row4['sex']));
        $grad_year = ucwords(strtolower($row4['grad_year']));
        $event = $row4['event'];
        $tffrs_url = $row4['tffrs_url'];
        ?>
        <div class="details_show" id="details_show" style="display: block">
            <p> <?php echo escape('Name : ' . $first_name . ' ' . $last_name); ?> </p>
            <p> <?php echo escape('Email : ' . $email); ?> </p>
            <p> <?php echo escape('Height : ' . $height); ?> </p>
            <p> <?php echo escape('Weight : ' . $weight); ?> </p>
            <p> <?php echo escape('Age : ' . $age); ?> </p>
            <p> <?php echo escape('Sex : ' . $sex); ?> </p>
            <p> <?php echo escape('Graduation Year : ' . $grad_year); ?> </p>
            <p> <?php echo escape('Event : ' . $event); ?> </p>
            <p> <?php echo escape('TFFRS Url : ' . $tffrs_url); ?> </p>
            <button id="edit_button" type="button">Edit</button>
        </div>

        <div class="details_edit" id="details_edit" style="display: none;">
            <form method="POST" action="edit_user.php" id="athlete_form">
                <label for="first_name">First Name:</label>
                <input type="text" placeholder="<?php echo $first_name?>" name="first_name" id="first_name">

                <label for="last_name">Last Name:</label>
                <input type="text" placeholder="<?php echo $last_name?>" name="last_name" id="last_name">

                <label for="email">Email:</label>
                <input type="email" placeholder="<?php echo $email?>" name="email" id="email">

                <label for="password">Password:</label>
                <input type="password" name="password" id="password">

                <label for="height">Height:</label>
                <input type="text" placeholder='<?= htmlspecialchars($height)?>' name="height" id="height">

                <label for="weight">Weight:</label>
                <input type="text" placeholder="<?php echo $weight?> lbs" name="weight" id="weight">

                <label for="dob">Date of Birth:</label>
                <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($dob) ?>">

                <label for="sex">Sex:</label>
                <select name="sex" id="sex">
                    <option value=""><?= $sex ?>(current)</option>
                    <?php
                    $options = ['M', 'F', 'U'];
                    foreach ($options as $option) {
                        if ($option !== $sex) {
                            echo "<option value='$option'>$option</option>";
                        }
                    }
                    ?>
                </select>

                <label for="grad_year">Graduation Year:</label>
                <input type="text" maxlength="4" pattern="\d{4}" inputmode="numeric" placeholder="<?php echo $grad_year?>" name="grad_year" id="grad_year">

                <label for="event">Event:</label>
                <select name="event" id="event">
                    <option value=""><?= $event ?>(current)</option>
                    <?php
                    $options = ['100', '200', '400', '800', '1500', '1600', '3200', '5000', '100H', '110H', '400H', 'HJ', 'LJ', 'TJ', 'PV', 'SP', 'DT', 'JT'];
                    foreach ($options as $option) {
                        if ($option !== $event) {
                            echo "<option value='$option'>$option</option>";
                        }
                    }
                    ?>
                </select>

                <label for="tffrs_url">TFFRS Url:</label>
                <input type="text" placeholder="<?php echo $tffrs_url?>" name="tffrs_url" id="tffrs_url">
                <button type="submit" onclick="return confirm('Are you sure you want to save your changes?');">Save Changes</button>
            </form>
            <button id="cancel_button" type="button">Cancel</button>
        </div>
    <?php endif; ?>
<script>
    const detailsSection = document.getElementById("details_show");
    const editSection = document.getElementById("details_edit");
    const editButton = document.getElementById("edit_button");
    const cancelButton = document.getElementById("cancel_button");
    const gradInput = document.getElementById("grad_year");
    const dobInput = document.getElementById("dob");
    const athleteForm = document.getElementById("athlete_form")


    function displayToEdit() {
        detailsSection.style.display = 'none';
        editSection.style.display = 'block';
    }

    function editToDisplay() {
        detailsSection.style.display = 'block';
        editSection.style.display = 'none';
    }

    if (editButton) {
        editButton.addEventListener('click', displayToEdit);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', editToDisplay);
    }

    if (gradInput) {
        gradInput.addEventListener("input", () => {
            gradInput.value = gradInput.value.replace(/\D/g, '');
        });
    }

    if (dobInput && athleteForm) {
        const originalDOB = dobInput.value;

        athleteForm.addEventListener("submit", () => {
            if (dobInput.value === originalDOB) {
                dobInput.value = "";
            }
        });
    }

</script>
</body>
