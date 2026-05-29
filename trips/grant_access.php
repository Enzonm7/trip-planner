grant_access:

<?php
session_start();
require_once '../config/db.php';

//THe user must be logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$trip_id = $_GET['trip_id'] ?? null;
if (!$trip_id) {
    header('Location: private.php');
    exit;
}

// We verify that the trip belongs to this user
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ? AND user_id = ?");
$stmt->execute([$trip_id, $_SESSION['id']]);
$trip = $stmt->fetch();
if (!$trip) {
    header('Location: private.php');
    exit;
}

// Handle grant access to all users
if (isset($_POST['grant_all'])) {
    $all_users = $pdo->prepare("SELECT id FROM users WHERE id != ?");
    $all_users->execute([$_SESSION['id']]);
    $insert = $pdo->prepare("INSERT IGNORE INTO trip_access (trip_id, user_id) VALUES (?, ?)");
    foreach ($all_users->fetchAll() as $u) {
        $insert->execute([$trip_id, $u['id']]);
    }
    // Update visibility to restricted
    $pdo->prepare("UPDATE trips SET visibility = 'public' WHERE id = ?")->execute([$trip_id]);
    header('Location: private.php');
    exit;
}

// Handle grant access to selected users
if (isset($_POST['grant_users'])) {
    $selected = $_POST['selected_users'] ?? [];
    $insert = $pdo->prepare("INSERT IGNORE INTO trip_access (trip_id, user_id) VALUES (?, ?)");
    foreach ($selected as $uid) {
        $insert->execute([$trip_id, (int)$uid]);
    }
    // Update visibility to restricted
    $pdo->prepare("UPDATE trips SET visibility = 'restricted' WHERE id = ?")->execute([$trip_id]);
    header('Location: private.php');
    exit;
}

// Fetch all the users except the current user and those who already have access
$stmt = $pdo->prepare("
    SELECT u.id, u.username 
    FROM users u
    WHERE u.id != ?
    AND u.id NOT IN (
        SELECT user_id FROM trip_access WHERE trip_id = ?
    )
");
$stmt->execute([$_SESSION['id'], $trip_id]);
$available_users = $stmt->fetchAll();

// Fetch the users who already have access
$stmt = $pdo->prepare("
    SELECT u.id, u.username 
    FROM users u
    JOIN trip_access ta ON ta.user_id = u.id
    WHERE ta.trip_id = ?
");
$stmt->execute([$trip_id]);
$users_with_access = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grant Access</title>
    <script>
        let selectedUsers = [];

        function addUser() {
            const select = document.getElementById('user_select');
            const userId = select.value;
            const username = select.options[select.selectedIndex].text;

            if (!userId) return;

            // Check not already added
            if (selectedUsers.find(u => u.id === userId)) return;

            selectedUsers.push({ id: userId, name: username });
            renderSelected();
        }

        function removeUser(userId) {
            selectedUsers = selectedUsers.filter(u => u.id !== userId);
            renderSelected();
        }

        function renderSelected() {
            const container = document.getElementById('selected_list');
            const hiddenContainer = document.getElementById('hidden_inputs');
            container.innerHTML = '';
            hiddenContainer.innerHTML = '';

            selectedUsers.forEach(u => {
                // Display tag
                const tag = document.createElement('span');
                tag.innerHTML = u.name + ' <button type="button" onclick="removeUser(\'' + u.id + '\')">x</button> ';
                container.appendChild(tag);

                // Hidden input for form submission
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_users[]';
                input.value = u.id;
                hiddenContainer.appendChild(input);
            });
        }

        function confirmGrantAll() {
            return confirm('Are you sure you want to grant access to all users?');
        }
    </script>
</head>
<body>
    <a href="private.php">Back</a>
    <h1>Grant Access <?= $trip['name'] ?></h1>

    <?php 
    //Users who have access
    if (!empty($users_with_access)): 
    ?>
        <h3>Users with access:</h3>
        <ul>
            <?php 
            foreach ($users_with_access as $u): 
                ?>
                <li><?= $u['username'] ?></li>
            <?php endforeach; ?>
        </ul>
    <?php 
    endif; 
    ?>

    <form method="POST" onsubmit="return confirmGrantAll()">
        <button type="submit" name="grant_all" value="1">Grant access to all users</button>
    </form>

    <hr>

    <form method="POST">
        <h3>Add specific users:</h3>

        <select id="user_select">
            <option value="">-- Select a user --</option>
            <?php 
            //Give access to certain users
                foreach ($available_users as $u): 
                ?>
                <option value="<?= $u['id'] ?>"><?=$u['username'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" onclick="addUser()">Add</button>

        <div id="selected_list" style="margin-top:10px;"></div>
        <div id="hidden_inputs"></div>

        <br>
        <button type="submit" name="grant_users" value="1">Validate</button>
    </form>
</body>
</html>