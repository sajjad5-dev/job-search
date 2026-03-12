<?php
require 'includes/auth.php';
require 'includes/db.php';
requireLogin();

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

// ── Employer: load their posted jobs ──────────────────────────
if ($role === 'employer') {
    $stmt = $pdo->prepare("
        SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
        FROM jobs j
        WHERE j.employer_id = ?
        ORDER BY j.created_at DESC
    ");
    $stmt->execute([$userId]);
    $myJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ── Seeker: load their applications ───────────────────────────
if ($role === 'seeker') {
    $stmt = $pdo->prepare("
        SELECT a.*, j.title, j.company, j.location, j.type
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.seeker_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$userId]);
    $myApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JobSearch</title>
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

    <h2 style="margin-bottom:4px;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
    <p style="color:#777; margin-bottom:28px;">
        Logged in as: <strong><?= ucfirst($role) ?></strong>
    </p>

    <!-- ══════════════ EMPLOYER DASHBOARD ══════════════ -->
    <?php if ($role === 'employer'): ?>

        <div class="page-header">
            <h3>Your Job Postings (<?= count($myJobs) ?>)</h3>
            <a href="add_job.php" class="btn btn-primary">+ Post New Job</a>
        </div>

        <?php if (empty($myJobs)): ?>
            <div class="card">
                <p>You haven't posted any jobs yet. <a href="add_job.php">Post your first job!</a></p>
            </div>
        <?php else: ?>
            <div class="card" style="padding:0; overflow:hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Applications</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myJobs as $job): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                            <td><?= htmlspecialchars($job['location']) ?></td>
                            <td><span class="badge"><?= $job['type'] ?></span></td>
                            <td><?= $job['app_count'] ?> applicant(s)</td>
                            <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                            <td class="actions">
                                <a href="edit_job.php?id=<?= $job['id'] ?>"   class="btn btn-warning" style="padding:6px 14px; font-size:13px;">Edit</a>
                                <a href="delete_job.php?id=<?= $job['id'] ?>" class="btn btn-danger"  style="padding:6px 14px; font-size:13px;"
                                   onclick="return confirm('Are you sure you want to delete this job? All applications will also be removed.')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <!-- ══════════════ SEEKER DASHBOARD ══════════════ -->
    <?php elseif ($role === 'seeker'): ?>

        <div class="page-header">
            <h3>Your Applications (<?= count($myApplications) ?>)</h3>
            <a href="jobs.php" class="btn btn-primary">Browse More Jobs</a>
        </div>

        <?php if (empty($myApplications)): ?>
            <div class="card">
                <p>You haven't applied for any jobs yet. <a href="jobs.php">Start browsing jobs!</a></p>
            </div>
        <?php else: ?>
            <div class="card" style="padding:0; overflow:hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myApplications as $app): ?>
                        <?php
                            $statusColors = [
                                'pending'  => '#f39c12',
                                'reviewed' => '#3498db',
                                'accepted' => '#1abc9c',
                                'rejected' => '#e74c3c',
                            ];
                            $color = $statusColors[$app['status']] ?? '#999';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($app['title']) ?></strong></td>
                            <td><?= htmlspecialchars($app['company']) ?></td>
                            <td><?= htmlspecialchars($app['location']) ?></td>
                            <td><span class="badge"><?= $app['type'] ?></span></td>
                            <td>
                                <span style="color:<?= $color ?>; font-weight:bold;">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
