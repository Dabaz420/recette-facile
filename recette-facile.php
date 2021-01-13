<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Miam miam la BD</title>
  <meta name="description" content="que personne ne fasse la blaque avec la pod'castor 🦫">
</head>
<body>
  <pre>
    <?php

      // séparer ses identifiants et les protéger, une bonne habitude à prendre
      include "recette-facile.dbconf.php";

      try {

        // instancie un objet $connexion à partir de la classe PDO
        $connexion = new PDO(DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_LOGIN, DB_PASS, DB_OPTIONS);

        // Requête de sélection 01
        $requete = "SELECT * FROM `recettes`";
        $prepare = $connexion->prepare($requete);
        $prepare->execute();
        $resultat = $prepare->fetchAll();
        print_r([$requete, $resultat]); // debug & vérification

        //Requête d'insertion
        $requete = "INSERT INTO `recettes` (`recette_titre`, `recette_contenu`, `recette_datetime`)
                    VALUES (:recette_titre, :recette_contenu, :recette_datetime);";
        $prepare = $connexion->prepare($requete);
        $prepare->execute(array(
          ":recette_titre" => "Pizza Mayo",
          ":recette_contenu" => "Tu prend une pizza et tu met 734g de mayo dessus",
          ":recette_datetime" => date("Y-m-d h:i:s"),
        ));
        $resultat = $prepare->rowCount(); // rowCount() nécessite PDO::MYSQL_ATTR_FOUND_ROWS => true
        $lastInsertedRecipeId = $connexion->lastInsertId(); // on récupère l'id automatiquement créé par SQL
        print_r([$requete, $resultat, $lastInsertedRecipeId]); // debug & vérification

        // Requête de modification
        $requete = "UPDATE `recettes`
                    SET `recette_titre` = :recette_titre
                    WHERE `recette_id` = :recette_id;";
        $prepare = $connexion->prepare($requete);
        $prepare->execute(array(
          ":recette_id"   => $lastInsertedRecipeId,
          ":recette_titre" => "😱 Pizza Mayo"
        ));
        $resultat = $prepare->rowCount();
        print_r([$requete, $resultat]); // debug & vérification

        // Requête de suppression
        $requete = "DELETE FROM `recettes`
                    WHERE ((`recette_id` = :recette_id));";
        $prepare = $connexion->prepare($requete);
        $prepare->execute(array($lastInsertedRecipeId)); // on lui passe l'id tout juste créé
        $resultat = $prepare->rowCount();
        print_r([$requete, $resultat, $lastInsertedRecipeId]); // debug & vérification

        //Requête d'insertion du levain dans la table hashtag
        // $requete = "INSERT INTO `hashtags` (`hashtag_nom`)
        //             VALUES (:hashtag_nom);";
        // $prepare = $connexion->prepare($requete);
        // $prepare->execute(array(
        //   ":hashtag_nom" => "levain"));
        // $resultat = $prepare->rowCount(); // rowCount() nécessite PDO::MYSQL_ATTR_FOUND_ROWS => true
        // print_r([$requete, $resultat]); // debug & vérification

        //Requête qui lie le hashtag "levain" à la recette du "pain au levain"
        // $requete = "INSERT INTO `assoc_hashtags_recettes` (`assoc_hr_hashtag_id`, `assoc_hr_recette_id`)
        //             VALUES (:assoc_hr_hashtag_id, :assoc_hr_recette_id);";
        // $prepare = $connexion->prepare($requete);
        // $prepare->execute(array(
        //   ":assoc_hr_hashtag_id" => 4,
        //   ":assoc_hr_recette_id" => 1,
        // ));
        // $resultat = $prepare->rowCount(); // rowCount() nécessite PDO::MYSQL_ATTR_FOUND_ROWS => true
        // print_r([$requete, $resultat]); // debug & vérification

        //Pour aller plus loin
        $requete = "SELECT `recette_titre` FROM `recettes`
                    INNER JOIN `assoc_hashtags_recettes` ON `recette_id` = `assoc_hr_recette_id`
                    WHERE `assoc_hr_hashtag_id` = :assoc_hr_hashtag_id;";
        $prepare = $connexion->prepare($requete);
        $prepare->execute(array(
            ":assoc_hr_hashtag_id" => 1
          ));
        $resultat = $prepare->fetchAll();
        print_r([$requete, $resultat]);

        // Requête de sélection pour afficher le titre des recettes en visant le hashtag 'nourriture'
        $requete = "SELECT recette_titre -- L'élément qu'on souhaite pull
        FROM assoc_hashtags_recettes -- on le pull depuis la table associative grâce aux deux lignes du dessous
        JOIN recettes on recettes.recette_id = assoc_hashtags_recettes.assoc_hr_recette_id -- JOIN pour créer la liaison dans la requête entre la table 'recettes' et la table assoc
        JOIN hashtags on hashtags.hashtag_id = assoc_hashtags_recettes.assoc_hr_hashtag_id -- JOIN pour créer la liaison dans la requête entre la table 'hashtags' et la table assoc
        WHERE hashtags.hashtag_nom = 'nourriture' "; // ici on spécifie qu'on ne veut pull que les entrées recette_titre associées au hashtag 'nourriture'
        $prepare = $connexion->prepare($requete);
        $prepare->execute();
        $resultat = $prepare->fetchAll();
        print_r([$requete, $resultat]); // debug & vérification

      } catch (PDOException $e) {

        // en cas d'erreur, on récup et on affiche, grâce à notre try/catch
        exit("❌🙀💀 OOPS :\n" . $e->getMessage());

      }
    ?>
  </pre>
</body>
</html>
