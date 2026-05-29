<?php
session_start();
require_once '../config/db.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    header('Location: public.php');
    exit;
}

// Fetch the trip by token
$stmt = $pdo->prepare("SELECT t.*, u.username FROM trips t JOIN users u ON t.user_id = u.id WHERE t.share_token = ?");
$stmt->execute([$token]);
$trip = $stmt->fetch();

if (!$trip) {
    die("Trip not found.");
}

// Check access rights
if ($trip['visibility'] === 'private') {
    if (!isset($_SESSION['id']) || $_SESSION['id'] !== $trip['user_id']) {
        header('Location: ../auth/login.php');
        exit;
    }
}

if ($trip['visibility'] === 'restricted') {
    if (!isset($_SESSION['id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    // Check if the user has access
    $stmt = $pdo->prepare("SELECT * FROM trip_access WHERE trip_id = ? AND user_id = ?");
    $stmt->execute([$trip['id'], $_SESSION['id']]);
    $hasAccess = $stmt->fetch();
    if (!$hasAccess && $_SESSION['id'] !== $trip['user_id']) {
        die("Access denied.");
    }
}

// Fetch the places of this trip in order
$stmt = $pdo->prepare("
    SELECT p.name, p.latitude, p.longitude, tp.position_order
    FROM trip_places tp
    JOIN places p ON p.id = tp.place_id
    WHERE tp.trip_id = ?
    ORDER BY tp.position_order ASC
");
$stmt->execute([$trip['id']]);
$places = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $trip['name'] ?></title>
</head>
<body>
    <a href="javascript:history.back()">Back</a>

    <h1><?= $trip['name'] ?></h1>
    <p>By: <?= $trip['username'] ?></p>
    <p>Visibility: <?= $trip['visibility'] ?></p>
    <p>Total distance: <?= $trip['total_distance'] ? round($trip['total_distance'], 2) . ' km' : 'Not computed' ?></p>

    <h2>Tour order</h2>
    <?php if (empty($places)): ?>
        <p>No places in this trip.</p>
    <?php else: ?>
        <ol>
            <?php 
            foreach ($places as $place): 
            ?>
                <li>
                    <?= $place['name'] ?>
                    (<?= $place['latitude'] ?>, <?= $place['longitude'] ?>)
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</body>
</html>