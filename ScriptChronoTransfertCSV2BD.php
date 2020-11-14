<?php

/*
 * ChronoTransfertCSV2BD.php
 */


try {

    // SOURCE  DU FICHIER CSV
    $fileName = "villes_france.csv";
    $fileSourceURL = "https://sql.sh/736-base-donnees-villes-francaises";
    echo "Fichier CSV téléchargé : " . $fileName;
    echo "<br>" . "Source URL du fichier téléchargé : " . $fileSourceURL;

    /*
     * OUVERTURE FICHIER DE TYPE CSV
     */

    $fichier = "villes_france.csv";
    $separator = ",";
    $contenu = "";
    // Ouverture pour lecture
    $canal = fopen($fichier, "r");

    /*
     * Connexion BD
     */
    $connection = new PDO("mysql:host=127.0.0.1;port=3306;dbname=communes_de_france;", "root", "");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ATTRIBUTS DE TRANSACTION
    $connection->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
    // ATTRIBUTS DE COMMUNICATION ENTRE SCRIPT ET BD
    $connection->exec("SET NAMES 'UTF8'");
    
    $columns = "ville_id, ville_departement, ville_slug, ville_nom, ville_nom_simple, ville_nom_reel, ville_nom_soundex, ville_nom_metaphone, ville_code_postal, ville_commune, ville_code_commune, ville_arrondissement, ville_canton, ville_amdi, ville_population_2010, ville_population_1999, ville_population_2012, ville_densite_2010, ville_surface, ville_longitude_deg, ville_latitude_deg, ville_longitude_grd, ville_latitude_grd, ville_longitude_dms, ville_latitude_dms, ville_zmin, ville_zmax";

    //Nombre de colonnes du tableau qui recevra les enregistrements
    $countColumnsInInsert = count(explode(",", $columns));
    //echo"<br>Nombre de colonnes du tableau : $countColumnsInInsert";

    //Création des paramètres de la requête SQL
    $parameters = "";
    for($i=1; $i <= $countColumnsInInsert; $i++){
        $parameters .= "?,";
    }
    $parameters = substr($parameters, 0, -1);
    
    // Requête SQL
    $sql = "INSERT INTO communes($columns) VALUES($parameters)";
    $statement = $connection->prepare($sql);

    // TRANSACTION
    $bTX = $connection->beginTransaction();
    $inserts = 0;

    // On stop le timestamp
    $start = time();

    // Test jusqu'a la fin du fichier
    while (!feof($canal)) {
        // Lecture d'une ligne jusqu'au RC compris
        $line = fgets($canal);
        $tLine = explode($separator, $line);
        if (count($tLine) == $countColumnsInInsert) {
            $statement->execute($tLine);
            $inserts++;
        }
        if ($inserts % 100 === 0) {
            // COMMIT
            $connection->commit();
            $bTX = $connection->beginTransaction();
        }
    }

    echo "<hr>Nombre d'enregistrements effectués du fichier CSV vers la BD : $inserts";

    // On stop le timestamp
    $end = time();

    // Calcul du timestamp 
    $duration = $end - $start;

    echo "<br>Temps nécessaire pour effectuer l'opération : ".$duration . " secondes";

    $connection = null;

    // Fermeture du fichier
    fclose($canal);
} catch (PDOException $e) {
    $message = $e->getMessage();
    //Affichage du msg d'erreur reçu dans le catch
    echo "<br>" . $message;
}
?>
