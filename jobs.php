<?php
require 'includes/auth.php';
require 'includes/db.php';

$search   = trim($_GET['search']   ?? '');
$location = trim($_GET['location'] ?? '');
$type     = trim($_GET['type']     ?? '');

$sql    = "SELECT j.*, u.name AS employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($location) {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}
if ($type) {
    $sql .= " AND j.type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - JobSearch</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <span class="brand">&#128269; JobSearch</span>
    <div>
        <a href="index.php">Home</a>
        <a href="jobs.php">Browse Jobs</a>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h2 style="margin-bottom:20px;">Browse Available Jobs</h2>

    <!-- SEARCH FORM -->
    <div class="card">
        <form method="GET" action="jobs.php">
            <div class="search-bar">
                <input type="text" name="search"
                       placeholder="Job title, company, keyword..."
                       value="<?= htmlspecialchars($search) ?>">
                <input type="text" name="location"
                       placeholder="City or location..."
                       value="<?= htmlspecialchars($location) ?>">
                <select name="type" style="max-width:180px;">
                    <option value="">All Types</option>
                    <?php foreach (['Full-Time','Part-Time','Remote','Internship'] as $t): ?>
                        <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="jobs.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- RESULTS COUNT -->
    <p style="color:#777; margin-bottom:16px;">
        <?= count($jobs) ?> job(s) found
        <?= $search ? " for \"" . htmlspecialchars($search) . "\"" : "" ?>
    </p>

    <!-- JOB CARDS -->
    <?php if (empty($jobs)): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <p style="font-size:18px; color:#aaa;">&#128269; No jobs found. Try a different search.</p>
            <br>
            <a href="jobs.php" class="btn btn-primary">Clear Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($jobs as $job): ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                <div style="flex:1;">
                    <span class="badge"><?= $job['type'] ?></span>
                    <h2 style="font-size:20px;"><?= htmlspecialchars($job['title']) ?></h2>
                    <p style="margin-top:6px;">
                        &#127970; <strong><?= htmlspecialchars($job['company']) ?></strong>
                        &nbsp;&#183;&nbsp;
                        &#128205; <?= htmlspecialchars($job['location']) ?>
                        <?php if ($job['salary']): ?>
                            &nbsp;&#183;&nbsp; &#128176; <?= htmlspecialchars($job['salary']) ?>
                        <?php endif; ?>
                    </p>
                    <p style="margin-top:10px; color:#666; line-height:1.6;">
                        <?= nl2br(htmlspecialchars(substr($job['description'], 0, 200))) ?>...
                    </p>
                </div>
                <div style="text-align:right; white-space:nowrap;">
                    <p style="color:#aaa; font-size:13px; margin-bottom:12px;">
                        <?= date('M d, Y', strtotime($job['created_at'])) ?>
                    </p>
                    <?php if (isLoggedIn() && $_SESSION['role'] === 'seeker'): ?>
                        <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-primary">Apply Now</a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php" class="btn btn-primary">Login to Apply</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
