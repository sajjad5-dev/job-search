<?php
require 'includes/auth.php';
require 'includes/db.php';
requireRole('employer');

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $company     = trim($_POST['company']);
    $location    = trim($_POST['location']);
    $type        = $_POST['type'];
    $description = trim($_POST['description']);
    $salary      = trim($_POST['salary']);

    if (empty($title) || empty($company) || empty($location) || empty($description)) {
        $error = "Please fill in all required fields (marked with *).";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO jobs (employer_id, title, company, location, type, description, salary)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $title, $company, $location, $type, $description, $salary]);
        $success = "Job posted successfully! <a href='dashboard.php'>View in dashboard</a> or <a href='add_job.php'>post another</a>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - JobSearch</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <span class="brand">&#128269; JobSearch</span>
    <div>
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card" style="max-width:620px; margin:0 auto;">
        <h2>Post a New Job</h2>
        <p style="color:#777; margin-bottom:20px;">Fill in the details below to advertise your position.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label style="font-size:13px; color:#777;">Job Title *</label>
                <input type="text" name="title" placeholder="e.g. Web Developer" required
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Company Name *</label>
                <input type="text" name="company" placeholder="e.g. Tech Corp" required
                       value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Location *</label>
                <input type="text" name="location" placeholder="e.g. New York or Remote" required
                       value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Job Type</label>
                <select name="type">
                    <?php foreach (['Full-Time','Part-Time','Remote','Internship'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($_POST['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Salary (optional)</label>
                <input type="text" name="salary" placeholder="e.g. $50,000/year or $25/hour"
                       value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Job Description *</label>
                <textarea name="description" placeholder="Describe the role, responsibilities, and requirements..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Post Job</button>
                <a href="dashboard.php" class="btn btn-secondary" style="flex:1; text-align:center; padding-top:11px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
