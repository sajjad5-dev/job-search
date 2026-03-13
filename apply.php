<?php
require 'desgin/includes/auth.php';
require 'desgin/includes/db.php';
requireRole('seeker');

$jobId = intval($_GET['job_id'] ?? 0);

// Fetch job
$stmt = $pdo->prepare("SELECT j.*, u.name AS employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.id = ?");
$stmt->execute([$jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("<h2 style='font-family:Arial;padding:30px;'>Job not found. <a href='jobs.php'>Browse Jobs</a></h2>");
}

// Check if already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
$stmt->execute([$jobId, $_SESSION['user_id']]);
$alreadyApplied = $stmt->fetch();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyApplied) {
    $coverLetter = trim($_POST['cover_letter']);

    $stmt = $pdo->prepare("INSERT INTO applications (job_id, seeker_id, cover_letter) VALUES (?, ?, ?)");
    $stmt->execute([$jobId, $_SESSION['user_id'], $coverLetter]);

    $success = "Your application has been submitted! <a href='dashboard.php'>View your applications</a>";
    $alreadyApplied = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply - <?= htmlspecialchars($job['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <span class="brand">&#128269; JobSearch</span>
    <div>
        <a href="index.php">Home</a>
        <a href="jobs.php">Browse Jobs</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- JOB DETAILS CARD -->
    <div class="card">
        <span class="badge"><?= $job['type'] ?></span>
        <h2><?= htmlspecialchars($job['title']) ?></h2>
        <p style="margin-top:8px;">
            &#127970; <strong><?= htmlspecialchars($job['company']) ?></strong>
            &nbsp;&#183;&nbsp;
            &#128205; <?= htmlspecialchars($job['location']) ?>
            <?php if ($job['salary']): ?>
                &nbsp;&#183;&nbsp; &#128176; <?= htmlspecialchars($job['salary']) ?>
            <?php endif; ?>
        </p>
        <hr style="margin:16px 0; border:none; border-top:1px solid #eee;">
        <h3 style="margin-bottom:10px;">Job Description</h3>
        <p style="line-height:1.8;"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
    </div>

    <!-- APPLICATION FORM -->
    <div class="card" style="max-width:620px;">
        <h2>Apply for This Job</h2>
        <p style="color:#777; margin-bottom:20px;">Applying as: <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($alreadyApplied): ?>
            <div class="alert alert-error">
                &#9888; You have already applied for this job. 
                <a href="dashboard.php">View your applications</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div>
                    <label style="font-size:13px; color:#777;">Cover Letter (optional)</label>
                    <textarea name="cover_letter"
                              placeholder="Tell the employer why you are a great fit for this role..."
                              style="min-height:160px;"><?= htmlspecialchars($_POST['cover_letter'] ?? '') ?></textarea>
                </div>
                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Submit Application</button>
                    <a href="jobs.php" class="btn btn-secondary" style="flex:1; text-align:center; padding-top:11px;">Back to Jobs</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
