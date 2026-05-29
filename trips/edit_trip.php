<?php
session_start();
require_once '../config/db.php';

// Must be logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$trip_id = $_GET['id'] ?? null;
if (!$trip_id) {
    header('Location: private.php');
    exit;
}

// Fetch trip and verify that the user created the trip
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ? AND user_id = ?");
$stmt->execute([$trip_id, $_SESSION['id']]);
$trip = $stmt->fetch();

if (!$trip) {
    die("Access denied.");
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_name = trim($_POST['trip_name']);
    $place_ids   = $_POST['place_ids']   ?? [];
    $place_names = $_POST['place_names'] ?? [];
    $place_lats  = $_POST['place_lats']  ?? [];
    $place_lngs  = $_POST['place_lngs']  ?? [];

    // Update the trip's name
    $pdo->prepare("UPDATE trips SET name = ? WHERE id = ? AND user_id = ?")
        ->execute([$trip_name, $trip_id, $_SESSION['id']]);

    $nb_hotels = max(1, (int)($_POST['nb_hotels'] ?? 1));
    $pdo->prepare("UPDATE trips SET name = ?, nb_hotels = ? WHERE id = ? AND user_id = ?")
        ->execute([$trip_name, $nb_hotels, $trip_id, $_SESSION['id']]);

    // Delete all current places for this trip
    $pdo->prepare("DELETE FROM trip_places WHERE trip_id = ?")
        ->execute([$trip_id]);

    // Insert places in new order and insert new places into the table places if needed
    $stmt_insert_place = $pdo->prepare("
        INSERT INTO places (name, latitude, longitude)
        VALUES (?, ?, ?)
    ");
    $stmt_insert_tp = $pdo->prepare("
        INSERT INTO trip_places (trip_id, place_id, position_order)
        VALUES (?, ?, ?)
    ");

    $final_place_ids = [];

    foreach ($place_ids as $i => $place_id) {
        if ($place_id === 'new') {
            // Add into the table places the new place
            $stmt_insert_place->execute([
                $place_names[$i],
                $place_lats[$i],
                $place_lngs[$i]
            ]);
            $final_place_ids[] = $pdo->lastInsertId();
        } else {
            $final_place_ids[] = (int)$place_id;
        }
    }

    foreach ($final_place_ids as $order => $pid) {
        $stmt_insert_tp->execute([$trip_id, $pid, $order + 1]);
    }

    // Add return to first place at the end
    if (!empty($final_place_ids)) {
        $stmt_insert_tp->execute([$trip_id, $final_place_ids[0], count($final_place_ids) + 1]);
    }

    header('Location: private.php');
    exit;
}

// Fetch current places in order
$stmt = $pdo->prepare("
    SELECT tp.place_id, tp.position_order, p.name, p.latitude, p.longitude
    FROM trip_places tp
    JOIN places p ON p.id = tp.place_id
    WHERE tp.trip_id = ?
    ORDER BY tp.position_order ASC
");
$stmt->execute([$trip_id]);
$all_places = $stmt->fetchAll();

// Remove last place because of the return to start
if (count($all_places) > 1) {
    array_pop($all_places);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Trip</title>
</head>
<body>
    <a href="private.php">Back</a>
    <h1>Edit Trip <?= $trip['name'] ?></h1>

    <form method="POST" id="edit-form">

        <label>Trip name:</label>
        <input type="text" name="trip_name" value="<?= $trip['name'] ?>" required>

        <label>Number of hotels:</label>
        <input type="number" name="nb_hotels" 
            value="<?= $trip['nb_hotels'] ?? 1 ?>" 
            min="1" 
            max="<?= count($all_places) ?>">
        <p>Maximum: number of places in your trip</p>

        <h3>Places</h3>

        <div id="places-list">
            <?php foreach ($all_places as $i => $place): ?>
                <div class="place-row">
                    <span><?= $i + 1 ?>. <?= $place['name']?> (<?= $place['latitude'] ?>, <?= $place['longitude'] ?>)</span>
                    <input type="hidden" name="place_ids[]" value="<?= $place['place_id'] ?>">
                    <input type="hidden" name="place_names[]" value="<?= $place['name'] ?>">
                    <input type="hidden" name="place_lats[]" value="<?= $place['latitude'] ?>">
                    <input type="hidden" name="place_lngs[]" value="<?= $place['longitude'] ?>">
                    <button type="button" onclick="moveUp(this)">Up</button>
                    <button type="button" onclick="moveDown(this)">Down</button>
                    <button type="button" onclick="removePlace(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <p><strong>Return to start:</strong> <span id="return-to-start"><?= !empty($all_places) ? $all_places[0]['name'] : 'None' ?></span></p>

        <!-- Add a new place -->
        <h3>Add a place</h3>
        <input type="text" id="search-input" placeholder="Search a city">
        <button type="button" onclick="searchPlace()">Search</button>

        <div id="search-results"></div>

        <button type="submit">Save changes</button>
    </form>

    <script>
        function getRows() {
            return document.querySelectorAll('#places-list .place-row');
        }

        function refresh() {
            const rows = getRows();
            rows.forEach((row, i) => {
                const span= row.querySelector('span');
                const name= row.querySelector('input[name="place_names[]"]').value;
                const lat= row.querySelector('input[name="place_lats[]"]').value;
                const lng = row.querySelector('input[name="place_lngs[]"]').value;
                span.textContent = (i + 1) + '. ' + name + ' (' + lat + ', ' + lng + ')';
            });
            const first = document.querySelector('input[name="place_names[]"]');
            document.getElementById('return-to-start').textContent = first ? first.value : 'None';
        }

        function moveUp(btn) {
            const row = btn.closest('.place-row');
            const prev = row.previousElementSibling;
            if (prev) {
                row.parentNode.insertBefore(row, prev);
                refresh();
            }
        }

        function moveDown(btn) {
            const row = btn.closest('.place-row');
            const next = row.nextElementSibling;
            if (next) {
                row.parentNode.insertBefore(next, row);
                refresh();
            }
        }

        function removePlace(btn) {
            const rows = getRows();
            if (rows.length <= 2) {
                alert('A trip must have at least 2 places.');
                return;
            }
            btn.closest('.place-row').remove();
            refresh();
        }

        // Search place via Nominatim
        function searchPlace() {
            const query = document.getElementById('search-input').value.trim();
            if (!query) return;

            const url = 'https://nominatim.openstreetmap.org/search?q=' 
                      + encodeURIComponent(query) 
                      + '&format=json&limit=5&addressdetails=1';

            fetch(url, {
                headers: { 'Accept-Language': 'en' }
            })
            .then(res => res.json())
            .then(results => {
                const container = document.getElementById('search-results');
                container.innerHTML = '';

                if (results.length === 0) {
                    container.innerHTML = '<p>No results found.</p>';
                    return;
                }

                results.forEach(r => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = r.display_name;
                    btn.onclick = () => addPlace(r.display_name.split(',')[0].trim(), r.lat, r.lon);
                    container.appendChild(btn);
                    container.appendChild(document.createElement('br'));
                });
            })
            .catch(() => {
                document.getElementById('search-results').innerHTML = '<p>Error reaching Nominatim API.</p>';
            });
        }

        function addPlace(name, lat, lng) {
            const list = document.getElementById('places-list');
            const rows = getRows();
            const index = rows.length;

            const div = document.createElement('div');
            div.className = 'place-row';
            div.innerHTML = `
                <span>${index + 1}. ${name} (${lat}, ${lng})</span>
                <input type="hidden" name="place_ids[]" value="new">
                <input type="hidden" name="place_names[]" value="${name}">
                <input type="hidden" name="place_lats[]" value="${lat}">
                <input type="hidden" name="place_lngs[]" value="${lng}">
                <button type="button" onclick="moveUp(this)">Up</button>
                <button type="button" onclick="moveDown(this)">Down</button>
                <button type="button" onclick="removePlace(this)">Remove</button>
            `;
            list.appendChild(div);

            // Clear search
            document.getElementById('search-input').value = '';
            document.getElementById('search-results').innerHTML = '';

            refresh();
        }
    </script>
</body>
</html>