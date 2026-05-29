
<?php

$boxstock = array();
function takeinfo($boxstock,$city,$long,$lat){
    $current = [
        "city" => $city,
        "long" => $long,
        "lat" => $lat
    ];
    array_push($boxstock, $current);
}

/*final class Trip{
    private const url='https://nominatim.openstreetmap.org/search';

    public function searching(string $city): array{
        $url = $url  . '?' . -(['q' => $city,'format' => 'json','addressdetails' => 1,'limit' => 1]);
    };
}*/
$result = null;

// Vérification frmulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $city = $_POST['city'] ?? '';
    $long = $_POST['long'] ?? '';
    $lat = $_POST['lat'] ?? 0;

    takeinfo($boxstock,$city,$long,$lat);

    if(isset($_POST['add-btn'])){
        takeinfo($boxstock,$city,$long,$lat);
    }
    elseif(isset($_POST['search-btn'])){
        // JSONPHP-> Python
        $data = json_encode($boxstock);

        $cmd = "py C:/Users/user/Documents/Box_Certif/epreuve-finale/travel_page/test_algo/main.py";

        $descriptors = [0 => ["pipe", "r"],1 => ["pipe", "w"],2 => ["pipe", "w"]];
            //le 0 est stdin, le 1 est stdout et le 2 est stderr

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {

            // envoyer les données à Python
            fwrite($pipes[0], $data);
            fclose($pipes[0]);

            // récupérer sortie
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            //close the err
            fclose($pipes[2]);

            proc_close($process);

            // décoder résult
            $result = json_decode($output, true);
        }
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
    <input type="text" name="long" placeholder="Longitude">
    <input type="text" name="lat" placeholder="Latitude">

    <button type="submit"  id="search-btn">Search</button>
    <button type="submit" id="add-btn">Add</button>
</form>

<h2>Résultat :</h2>

<pre>
<?php foreach ($boxstock as $index => $user) { ?>
    <?php echo $user["city"] . " " . $user["long"] . " (" . $user["lat"] . PHP_EOL; ?>
<?php } ?>
</pre>

</body>
</html>