<?php

session_start();

if (!isset($_SESSION['boxstock'])) {
    $_SESSION['boxstock'] = [];
}
function formatBoxstock($e) {
    return [$e["city"], $e["long"], $e["lat"]];
}

function nominatim($city){
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
        $coords = nominatim($city);
        $_SESSION['boxstock'][] = [
            "city" => $city,
            "long" => $coords['long'],
            "lat"  => $coords['lat']
        ];
    }
    elseif (isset($_POST['search-btn'])) {
        $data = json_encode(["data" => array_map("formatBoxstock", $_SESSION['boxstock'])]);

        //call cmd 
        

        $descriptors = [0 => ["pipe", "r"],1 => ["pipe", "w"],2 => ["pipe", "w"]];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {

            fwrite($pipes[0], $data);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            fclose($pipes[2]);

            proc_close($process);

            $result = json_decode($output, true);
        }
    }
    elseif (isset($_POST['reset-btn'])) {
        $_SESSION['boxstock'] = [];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Trip Page</title>
</head>

<body>

<h1>Trip Page</h1>

<nav>
    <a href="trip.php">Recherche</a>
</nav>

<form method="POST">
    <input type="text" name="city" placeholder="City">
    <button type="submit" name="add-btn">Add</button>
    <button type="submit" name="search-btn">Search</button>
    <button type="submit" name="reset-btn">Reset</button>
</form>

<h2>Villes ajoutées :</h2>

<pre>
<?php foreach ($_SESSION['boxstock'] as $user) { ?>
    <?php echo $user["city"] . " " . $user["long"] . " (" . $user["lat"] . ")" . PHP_EOL; ?>
<?php } ?>
</pre>

<?php if ($result): ?>
<h2>Résultat Python :</h2>
<pre>
<?php echo json_encode($result, JSON_PRETTY_PRINT); ?>
</pre>
<?php endif; ?>

</body>
</html>