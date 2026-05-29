<?php
session_start();
require_once '../config/db.php';

// Fetch all public trips
$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.total_distance, t.share_token, u.username
    FROM trips t
    JOIN users u ON t.user_id = u.id
    WHERE t.visibility = 'public'
    ORDER BY t.created_at DESC
");
$stmt->execute();
$trips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Public Trips</title>
</head>
<body>
    <nav>
        <a href="public.php">Public</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="half_public.php">Restricted</a>
            <a href="private.php">My Trips</a>
        <?php endif; ?>
        <a href="../index.php">Back</a>
    </nav>

    <h1>Public Trips</h1>

    <?php if (empty($trips)): ?>
        <p>No public trips available.</p>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
            <div>
                <h3><?= $trip['name'] ?></h3>
                <p>By: <?= $trip['username'] ?></p>
                <p>Distance: <?= $trip['total_distance'] ? round($trip['total_distance'], 2) . ' km' : 'Not computed' ?></p>
                <a href="view_trip.php?token=<?= $trip['share_token'] ?>">View</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>