<?php
require 'includes/auth.php';
require 'includes/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "This email is already registered. Try logging in.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, $role]);
            $success = "Account created successfully! You can now <a href='login.php'>login here</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - JobSearch</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <span class="brand">&#128269; JobSearch</span>
    <div>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
    </div>
</nav>

<div class="container">
    <div class="card form-box" style="margin-top:10px;">
        <h2>Create an Account</h2>
        <p style="color:#777; margin-bottom:20px;">Join JobSearch today — it's free!</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text"     name="name"             placeholder="Full Name"           required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <input type="email"    name="email"            placeholder="Email Address"        required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="password" name="password"         placeholder="Password (min 6 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password"    required>
            <select name="role">
                <option value="seeker"   <?= ($_POST['role'] ?? '') === 'seeker'   ? 'selected' : '' ?>>I am a Job Seeker</option>
                <option value="employer" <?= ($_POST['role'] ?? '') === 'employer' ? 'selected' : '' ?>>I am an Employer</option>
            </select>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <br>
        <p style="text-align:center;">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

</body>
</html>
