<?php
require 'includes/auth.php';
require 'includes/db.php';
requireRole('employer');

$jobId = intval($_GET['id'] ?? 0);

// Fetch job — must belong to this employer
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $_SESSION['user_id']]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("<h2 style='font-family:Arial;padding:30px;'>Job not found or you don't have permission to edit it. <a href='dashboard.php'>Go Back</a></h2>");
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $company     = trim($_POST['company']);
    $location    = trim($_POST['location']);
    $type        = $_POST['type'];
    $description = trim($_POST['description']);
    $salary      = trim($_POST['salary']);

    if (empty($title) || empty($company) || empty($location) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE jobs SET title=?, company=?, location=?, type=?, description=?, salary=?
            WHERE id=? AND employer_id=?
        ");
        $stmt->execute([$title, $company, $location, $type, $description, $salary, $jobId, $_SESSION['user_id']]);
        $success = "Job updated successfully! <a href='dashboard.php'>Back to dashboard</a>";

        // Refresh job data for the form
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - JobSearch</title>
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
        <h2>Edit Job</h2>
        <p style="color:#777; margin-bottom:20px;">Update the details for: <strong><?= htmlspecialchars($job['title']) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label style="font-size:13px; color:#777;">Job Title *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($job['title']) ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Company Name *</label>
                <input type="text" name="company" required value="<?= htmlspecialchars($job['company']) ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Location *</label>
                <input type="text" name="location" required value="<?= htmlspecialchars($job['location']) ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Job Type</label>
                <select name="type">
                    <?php foreach (['Full-Time','Part-Time','Remote','Internship'] as $t): ?>
                        <option value="<?= $t ?>" <?= $job['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Salary (optional)</label>
                <input type="text" name="salary" value="<?= htmlspecialchars($job['salary']) ?>">
            </div>
            <div>
                <label style="font-size:13px; color:#777;">Job Description *</label>
                <textarea name="description" required><?= htmlspecialchars($job['description']) ?></textarea>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-warning" style="flex:1;">Update Job</button>
                <a href="dashboard.php" class="btn btn-secondary" style="flex:1; text-align:center; padding-top:11px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
