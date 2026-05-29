<?php
session_start();
require_once '../config/db.php';

// The user must be logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Delete trip
if (isset($_POST['delete_trip_id'])) {
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['delete_trip_id'], $_SESSION['id']]);
    header('Location: private.php');
    exit;
}


// Fetch only this user's trips
$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.total_distance, t.share_token, t.visibility
    FROM trips t
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['id']]);
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
        <a href="user_home.php">Back</a>
    </nav>

    <h1>My Trips</h1>

    <?php if (empty($trips)): ?>
        <p>No trips yet <a href="../travel_page/trip.php"> Create one here</a></p>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
            <div>
                <h3><?= htmlspecialchars($trip['name']) ?></h3>
                <p>Visibility: <?= $trip['visibility'] ?></p>
                <p>Distance: <?= $trip['total_distance'] ? round($trip['total_distance'], 2) . ' km' : 'Not computed' ?></p>

                <a href="view_trip.php?token=<?= $trip['share_token'] ?>">View</a>

                
                <a href="edit_trip.php?id=<?= $trip['id'] ?>">Edit</a>
                <a href="grant_access.php?trip_id=<?= $trip['id'] ?>">Grant access</a>

                <form method="POST" style="display:inline;" 
                      onsubmit="return confirm('Are you sure you want to delete this trip?')">
                    <input type="hidden" name="delete_trip_id" value="<?= $trip['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>