<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/strategy/pythonStrategy.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['boxstock'])) {
    $_SESSION['boxstock'] = [];
}

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
}

function formatBoxstock($e): array {
    return [
        $e["city"],
        $e["long"],
        $e["lat"]
    ];
}

function search($city) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $city,
        'format' => 'json',
        'addressdetails' => 1,
        'limit' => 1
    ]);

    $options = [
        'http' => [
            'header' => "User-Agent: TripPage/1.0\r\n"
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data)) {
        return [
            'long' => $data[0]['lon'],
            'lat'  => $data[0]['lat']
        ];
    }

    return ['long' => null, 'lat' => null];
}

$result = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $city = $_POST['city'] ?? '';

    if (isset($_POST['add-btn']) && $city !== '') {
        $coords = search($city);
        $_SESSION['boxstock'][] = [
            "city" => $city,
            "long" => $coords['long'],
            "lat"  => $coords['lat']
        ];
        $_SESSION['count'] = ($_SESSION['count'] ?? 0) + 1;
    }

    elseif (isset($_POST['search-btn'])) {

        $nb_hotels = isset($_POST['nb_hotels']) && $_POST['nb_hotels'] !== ''
            ? (int)$_POST['nb_hotels']
            : null;

        $payload = [
            "data"   => array_map("formatBoxstock", $_SESSION['boxstock']),
            "nombre" => $_SESSION['count'],
        ];

        if ($nb_hotels !== null) {
            $payload["nb_hotels"] = $nb_hotels;
        }

        $data = json_encode($payload);
        $strategy = new PythonStrategy();
        $result = $strategy->run($data);

        foreach ($result['groups'] as $group) {
            try {
                $userId = 1;
                $name     = $group['hotel']['name'];
                $distance = $group['distance_km'];

                $sql = "
                    INSERT INTO trips
                    (name, share_token, total_distance, visibility, user_id)
                    VALUES
                    (:name, :token, :distance, :visibility, :user_id)
                ";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name'       => $name,
                    ':token'      => uniqid('trip_', true),
                    ':distance'   => $distance,
                    ':visibility' => 'private',
                    ':user_id'    => $userId
                ]);

                echo "Insertion réussie pour le groupe hôtel : " . htmlspecialchars($name) . "<br>";

            } catch (Exception $e) {
                echo "Erreur data : " . $e->getMessage() . "<br>";
            } catch (PDOException $e) {
                echo "Erreur SQL : " . $e->getMessage() . "<br>";
            }
        }
    }

    elseif (isset($_POST['reset-btn'])) {
        $_SESSION['boxstock'] = [];
        $_SESSION['count'] = 0;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trip Page</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>

<h1>Trip Page</h1>

<nav>
    <a href="trip.php">Recherche</a> |
    <a href="show.php">Voir les recherches</a>
</nav>

<form method="POST">
    <input type="text" name="city" placeholder="Ville">
    <button type="submit" name="add-btn">Add</button>
    <br><br>
    <input type="number" name="nb_hotels" placeholder="Nombre d'hôtels (optionnel)" min="1">
    <button type="submit" name="search-btn">Search</button>
    <button type="submit" name="reset-btn">Reset</button>
</form>

<h2>Villes ajoutées (<?= $_SESSION['count'] ?>) :</h2>

<pre>
<?php foreach ($_SESSION['boxstock'] as $user) { ?>
<?= $user["city"] . " " . $user["long"] . " (" . $user["lat"] . ")" . PHP_EOL ?>
<?php } ?>
</pre>

<?php if ($result): ?>
<h2>Résultat :</h2>
<p>Nombre d'hôtels suggéré : <?= $result['suggested_nb_hotels'] ?></p>
<p>Distance totale : <?= $result['total_distance_km'] ?> km</p>

<?php foreach ($result['groups'] as $i => $group): ?>
    <h3>Groupe <?= $i + 1 ?> — Hôtel : <?= htmlspecialchars($group['hotel']['name']) ?></h3>
    <p>Circuit : <?= implode(' → ', $group['circuit']) ?></p>
    <p>Distance : <?= $group['distance_km'] ?> km</p>
<?php endforeach; ?>

<h3>Circuit inter-hôtels</h3>
<p><?= implode('->', $result['inter_hotel_circuit']) ?></p>
<?php endif; ?>

</body>
</html>