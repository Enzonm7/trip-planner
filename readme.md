# Travel Page
## Prérequis

- PHP 8+
- Python 3
- MySQL

## Configuration
### 1. Chemin vers l'algorithme Python
Dans `strategy/pythonStrategy.php`, modifie le chemin vers ton script Python selon ton environnement local:
epreuve_final/travel_page/strategy/pythonStrategy.php


### 2. Accès à la base de données

Dans `epreuve_final/config/db.php`, renseigne tes informations de connexion locales :

$host = 'localhost';
$dbname = 'nom_de_ta_bdd';
$user = 'ton_utilisateur';
$password = 'ton_mot_de_passe';

### 3. Structure de la base de données

La table `trips` doit contenir au minimum les colonnes suivantes :

sql
CREATE TABLE trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    share_token VARCHAR(255) UNIQUE,
    total_distance FLOAT,
    visibility ENUM('private', 'public'),
    user_id INT
);

## Utilisation
1. Ajoute des villes via le champ de recherche puis clique sur *Add*
2. Clique sur *Search* pour lancer l'algorithme et enregistrer les résultats en base
3. Clique sur *Reset* pour vider la liste de villes