<?php

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User home</title>
</head>
<body>
    <h1>User Home</h1>
    <a href="../travel_page/trip.php">Create a trip</a>
    <p></p>
    <a href="private.php">View different trips</a>
    <p></p>
    <a href="../auth/logout.php">Disconnect</a>
</body>
</html>