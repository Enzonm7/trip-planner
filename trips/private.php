<?php
session_start();
require_once '../config/db.php';

// The user must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch only this user's trips
$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.total_distance, t.share_token, t.visibility
    FROM trips t
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$trips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Trips</title>
</head>
<body>
    <nav>
        <a href="public.php">Public</a>
        <a href="half_public.php">Restricted</a>
        <a href="private.php">My Trips</a>
        <a href="../user_home/user_home.php">Back</a>
    </nav>

    <h1>My Trips</h1>

    <?php if (empty($trips)): ?>
        <p>You have no trips yet. <a href="../user_home/getMyPath.php">Create one!</a></p>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
            <div>
                <h3><?= $trip['name'] ?></h3>
                <p>Visibility: <?= $trip['visibility'] ?></p>
                <p>Distance: <?= $trip['total_distance'] ? round($trip['total_distance'], 2) . ' km' : 'Not computed' ?></p>
                <a href="view_trip.php?token=<?= $trip['share_token'] ?>">View</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>