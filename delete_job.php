<?php
require 'desgin/includes/auth.php';
require 'desgin/includes/db.php';
requireRole('employer');

$jobId = intval($_GET['id'] ?? 0);

// Delete only if the job belongs to the logged-in employer
$stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $_SESSION['user_id']]);

header("Location: dashboard.php");
exit();
?>
