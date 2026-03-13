<?php
require 'desgin/includes/auth.php';
require 'desgin/includes/db.php';

$totalJobs      = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$totalUsers     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalEmployers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetchColumn();
$totalApps      = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

$latestJobs = $pdo->query("
    SELECT j.*, u.name AS employer_name
    FROM jobs j
    JOIN users u ON j.employer_id = u.id
    ORDER BY j.created_at DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$typeCounts = $pdo->query("
    SELECT type, COUNT(*) AS total FROM jobs GROUP BY type
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobSearch - Find Your Dream Job</title>
    <link rel="stylesheet" href="desgin/css/style.css">
</head>
<body>

<nav>
    <span class="brand">&#128269; JobSearch</span>
    <div>
        <a href="index.php">Home</a>
        <a href="jobs.php">Browse Jobs</a>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <h1>Find the Job , you need</h1>
    <p>
        <?php if ($totalJobs > 0): ?>
            Browse <strong><?= $totalJobs ?></strong> live job<?= $totalJobs != 1 ? 's' : '' ?> from <strong><?= $totalEmployers ?></strong> companies
        <?php else: ?>
            Thousands of jobs waiting for you. Start your search now.
        <?php endif; ?>
    </p>
    <form action="jobs.php" method="GET">
        <div class="hero-search">
            <input type="text" name="search"   placeholder="&#128269; Job title, skill, or company...">
            <input type="text" name="location" placeholder="&#128205; City or location..." style="max-width:200px;">
            <button type="submit">Search Jobs</button>
        </div>
    </form>
</div>

<div class="container">

    <div class="stats-row">
        <div class="stat-box">
            <div class="num"><?= $totalJobs ?></div>
            <div class="lbl">&#128188; Jobs Posted</div>
        </div>
        <div class="stat-box">
            <div class="num"><?= $totalEmployers ?></div>
            <div class="lbl">&#127970; Companies</div>
        </div>
        <div class="stat-box">
            <div class="num"><?= $totalUsers ?></div>
            <div class="lbl">&#128100; Registered Users</div>
        </div>
        <div class="stat-box">
            <div class="num"><?= $totalApps ?></div>
            <div class="lbl">&#128228; Applications Sent</div>
        </div>
    </div>

    <div class="section-header">
        <h2>&#128293; Latest Job Listings</h2>
        <a href="jobs.php" class="btn btn-primary">View All Jobs &rarr;</a>
    </div>

    <?php if (!empty($typeCounts)): ?>
    <div class="type-filters">
        <a href="jobs.php" class="type-filter">All Jobs <span><?= $totalJobs ?></span></a>
        <?php foreach ($typeCounts as $type => $count): ?>
        <a href="jobs.php?type=<?= urlencode($type) ?>" class="type-filter">
            <?= $type ?> <span><?= $count ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($latestJobs)): ?>
        <div class="empty-state">
            <div style="font-size:50px; margin-bottom:16px;">&#128188;</div>
            <p>No jobs have been posted yet.</p>
            <?php if (isLoggedIn() && $_SESSION['role'] === 'employer'): ?>
                <a href="add_job.php" class="btn btn-primary">Post the First Job</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Register as Employer to Post Jobs</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="job-grid">
            <?php foreach ($latestJobs as $job): ?>
            <div class="job-card">
                <span class="badge"><?= $job['type'] ?></span>
                <h3><?= htmlspecialchars($job['title']) ?></h3>
                <div class="company">&#127970; <?= htmlspecialchars($job['company']) ?></div>
                <div class="meta">&#128205; <?= htmlspecialchars($job['location']) ?></div>
                <div class="desc"><?= htmlspecialchars(substr($job['description'], 0, 110)) ?>...</div>
                <div class="footer">
                    <span class="salary-tag">
                        <?= $job['salary'] ? '&#128176; ' . htmlspecialchars($job['salary']) : '&#128176; Negotiable' ?>
                    </span>
                    <small style="color:#bbb;"><?= date('M d', strtotime($job['created_at'])) ?></small>
                </div>
                <?php if (isLoggedIn() && $_SESSION['role'] === 'seeker'): ?>
                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-primary" style="width:100%; text-align:center; margin-top:4px;">Apply Now</a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-primary" style="width:100%; text-align:center; margin-top:4px;">Login to Apply</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-secondary" style="width:100%; text-align:center; margin-top:4px;">Manage Jobs</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalJobs > 6): ?>
        <div style="text-align:center; margin-bottom:30px;">
            <a href="jobs.php" class="btn btn-primary" style="padding:14px 40px; font-size:16px;">
                View All <?= $totalJobs ?> Jobs &rarr;
            </a>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <h2 style="color:#2c3e50; margin-bottom:20px;">&#128161; How It Works</h2>
    <div class="steps" style="margin-bottom:30px;">
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-icon">&#128221;</div>
            <h3>Create Account</h3>
            <p>Sign up for free as a Job Seeker or Employer in under a minute.</p>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-icon">&#128269;</div>
            <h3>Search Jobs</h3>
            <p>Browse and filter job listings by keyword, location, or type.</p>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-icon">&#128228;</div>
            <h3>Apply Instantly</h3>
            <p>Submit your application with a cover letter directly to the employer.</p>
        </div>
        <div class="step">
            <div class="step-num">4</div>
            <div class="step-icon">&#127881;</div>
            <h3>Get Hired</h3>
            <p>Track your application status and land your dream job.</p>
        </div>
    </div>

    <?php if (!isLoggedIn()): ?>
    <div class="cta-banner">
        <h2>Ready to Get Started?</h2>
        <p>Join <?= $totalUsers ?> users already using JobSearch.</p>
        <a href="register.php" class="btn-white">&#128100; Find a Job</a>
        <a href="register.php" class="btn-outline">&#127970; Post a Job</a>
    </div>
    <?php elseif ($_SESSION['role'] === 'employer'): ?>
    <div class="cta-banner">
        <h2>Looking to Hire?</h2>
        <p>Post a new job and reach qualified job seekers today.</p>
        <a href="add_job.php" class="btn-white">+ Post a New Job</a>
    </div>
    <?php endif; ?>

</div>

</body>
</html>