<?php

use travel_page\Model\cityModel;
?>
<!DOCTYPE html>

<html>
    <body>
        <h1>Trip Page</h1>
        <p>Welcome to the trip page!</p>
        <div>
            <nav>
                <a href="trip.php">Recherche</a>
            </nav>
        </div>
        <div>
            <input type="text" name="name" placeholder="Users">
            <input type="text" name="long" placeholder="Votes">
            <input type="number" name="lat" placeholder="Taille">
            <button type='submit'>Search</button>
        </div>
    </body>
</html>