<?php
declare(strict_types=1);

require 'C:\Users\user\Documents\Box_Certif\epreuve-finale\travel_page\strategy\pythonStrategy.php';
require 'C:\Users\user\Documents\Box_Certif\epreuve-finale\config\db.php';
session_reset();
session_start();


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

        $data = json_encode([
            "data" => array_map("formatBoxstock", $_SESSION['boxstock']),
            "nombre" => $_SESSION['count']
        ]);
        $strategy = new PythonStrategy();
        $result = $strategy->run($data);

        foreach($result['data'] as $row){
            
            try {
                $userId=1;
                if(is_array($row)) {

                    // Cas associatif
                    if (isset($row['name']) && isset($row['distance'])) {
                        $name = $row['name'];
                        $distance = $row['distance'];
                    }

                    // Cas indexé
                    elseif (isset($row[0]) && isset($row[1])) {
                        $name = $row[0];
                        $distance = $row[1];
                    }

                    else {
                        throw new Exception("Structure de row invalide");
                    }

                } else {
                    throw new Exception("Row non exploitable (pas un tableau)");
                }
                
                $sql = "
                    INSERT INTO trips
                    (name, share_token, total_distance, visibility, user_id)
                    VALUES
                    (:name, :token, :distance, :visibility, :user_id)
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':name' => $name,
                    ':token' => uniqid('trip_', true),
                    ':distance' => $distance,
                    ':visibility' => 'private',
                    ':user_id' => $userId
                ]);

                echo "Insertion réussie<br>";

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
    <input type="text" name="city" placeholder="City">
    <button type="submit" name="add-btn">Add</button>
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
<h2>Résultat Python :</h2>
<pre>
<?= json_encode($result, JSON_PRETTY_PRINT) ?>
</pre>
<?php endif; ?>

</body>
</html>