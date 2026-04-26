<?php

require_once __DIR__ . '/../includes/auth.php';

redirectIfLoggedIn();

$errors = getFlashData('errors', []);
$oldInput = getFlashData('old_input', []);
$loginError = getFlashData('login_error', '');
$emailValue = $oldInput['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<main class="page">
    <section class="card">
        <h1>Login</h1>
        <p class="intro">Sign in with the demo account after you import the sample database.</p>

        <?php if ($loginError !== ''): ?>
            <div class="message error"><?php echo escape($loginError); ?></div>
        <?php endif; ?>

        <form action="process-login.php" method="post" class="form">
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo escape($emailValue); ?>"
                    autocomplete="username"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <p class="field-error"><?php echo escape($errors['email']); ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <p class="field-error"><?php echo escape($errors['password']); ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="button">Log In</button>
        </form>

        <p class="note">Demo login: <strong>demo@example.com</strong> / <strong>password123</strong></p>
    </section>
</main>
</body>
</html>