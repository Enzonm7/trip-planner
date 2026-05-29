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

// Change trip visibility
if (isset($_POST['change_visibility_trip_id'])) {
    $allowed = ['public', 'private', 'restricted'];
    $new_visibility = $_POST['new_visibility'];

    if (in_array($new_visibility, $allowed)) {
        $stmt = $pdo->prepare("UPDATE trips SET visibility = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_visibility, $_POST['change_visibility_trip_id'], $_SESSION['id']]);

        // If switching back to private, remove all trip_access entries
        if ($new_visibility === 'private') {
            $pdo->prepare("DELETE FROM trip_access WHERE trip_id = ?")
                ->execute([$_POST['change_visibility_trip_id']]);
        }
    }
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
        <p></p>
        <a href="half_public.php">Restricted</a>
        <p></p>
        <a href="private.php">My Trips</a>
        <p></p>
        <a href="user_home.php">Back</a>
    </nav>

    <h1>My Trips</h1>

    <?php if (empty($trips)): ?>
        <p>No trips yet. <a href="../travel_page/trip.php">Create one here</a></p>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
            <div>
                <h3><?=$trip['name'] ?></h3>
                <p>Visibility: <?= $trip['visibility'] ?></p>
                <p>Distance: <?= $trip['total_distance'] ? round($trip['total_distance'], 2) . ' km' : 'Not computed' ?></p>

                <a href="view_trip.php?token=<?= $trip['share_token'] ?>">View</a>
                <p></p>
                <a href="edit_trip.php?id=<?= $trip['id'] ?>">Edit</a>
                <p></p>
                <a href="grant_access.php?trip_id=<?= $trip['id'] ?>">Grant access</a>

                <!-- Change visibility -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="change_visibility_trip_id" value="<?= $trip['id'] ?>">
                    <select name="new_visibility">
                        <option value="public" <?= $trip['visibility']=== 'public' ? 'selected' : '' ?>>Public</option>
                        <option value="restricted" <?= $trip['visibility'] ==='restricted' ? 'selected' : '' ?>>Restricted</option>
                        <option value="private" <?= $trip['visibility'] ==='private' ? 'selected' : '' ?>>Private</option>
                    </select>
                    <button type="submit">Update</button>
                </form>

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