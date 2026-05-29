<?php
session_start();

require("../config/db.php");

try {

    $sql = "
        SELECT 
            name,
            total_distance,
            visibility
        FROM trips
        ORDER BY created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste des voyages</title>

    <style>

        body {
            font-family: Arial;
            margin: 40px;
        }

        .trip {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .trip h3 {
            margin: 0;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>

    <h1>Liste des voyages</h1>

    <?php if (!empty($resultats)): ?>

        <?php foreach ($resultats as $row): ?>

            <div class="trip">

                <h3>
                    <?= htmlspecialchars($row['name']) ?>
                </h3>

                <p>
                    <strong>Distance totale :</strong>
                    <?= htmlspecialchars($row['total_distance']) ?> km
                </p>

                <p>
                    <strong>Visibilité :</strong>
                    <?= htmlspecialchars($row['visibility']) ?>
                </p>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>Aucun voyage trouvé.</p>

    <?php endif; ?>

</body>
</html>