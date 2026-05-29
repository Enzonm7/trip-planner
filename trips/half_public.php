<?php
session_start();
require_once '../config/db.php';

// The user must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch restricted trips accessible to this user
$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.total_distance, t.share_token, u.username
    FROM trips t
    JOIN users u ON t.user_id = u.id
    JOIN trip_access ta ON ta.trip_id = t.id
    WHERE t.visibility = 'restricted'
    AND ta.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$trips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restricted Trips</title>
</head>
<body>
    <nav>
        <a href="public.php">Public</a>
        <a href="half_public.php">Restricted</a>
        <a href="private.php">My Trips</a>
        <a href="../index.php">Back</a>
    </nav>

    <h1>Restricted Trips</h1>

    <?php if (empty($trips)): ?>
        <p>No restricted trips shared with you.</p>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
            <div>
                <h3><?=$trip['name'] ?></h3>
                <p>By: <?= $trip['username'] ?></p>
                <p>Distance: <?= $trip['total_distance'] ? round($trip['total_distance'], 2) . ' km' : 'Not computed' ?></p>
                <a href="view_trip.php?token=<?= $trip['share_token'] ?>">View</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>