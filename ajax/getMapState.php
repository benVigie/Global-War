<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur
	$player = $_SESSION['user-id'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');


	// Si on a pas l'ID de la partie, pas la peine 
	if (!isset($_GET['game']))
		AjaxExit('Pas de partie d&eacute;finie');

	$gameID = $_GET['game'];
	$time = null;
	$game = new gameManager($db, $gameID);

	// Si on demande a récupérer le dernier état connu, on va essayer de charger la map telle que le joueur l'a laissée
	// SSI le joueur est encore en mode "renforts" (pour ne pas derouler le scénario a chaque fois qu'il charge le jeu)
	if (isset($_GET['loadLastKnewState'])) {
		
		// Si c'est son premier tour (méme si d'autres ont joué), on charge la map originale
		if ($game->IsPlayerFirstTurn($player)) {
			$firstStroke = $db->GetRows("SELECT `strokes`.`stroke_date` FROM `strokes` WHERE `strokes`.`stroke_game` = '$gameID' ORDER BY `strokes`.`stroke_date` ASC LIMIT 0, 1");
			$time = (is_null($firstStroke)) ? null : $firstStroke[0]['stroke_date'];
		}
		// Sinon, on charge la map telle qu'il l'a laissée
		else {
			// Récupération de l'heure du dernier tour du joueur
			$lastTurn = $db->GetRows("SELECT `strokes`.`stroke_date` FROM `strokes` WHERE `strokes`.`stroke_game` = '$gameID' AND `strokes`.`stroke_player` = '$player' ORDER BY `strokes`.`stroke_date` DESC");
			$time = (is_null($lastTurn)) ? null : $lastTurn[0]['stroke_date'];
		}

	}
	// Sinon on charge la map tel qu'elle est à cet instant precis
	else
		$time = null;
		

	echo (json_encode($game->LoadGame($time)));
	$db->close();
?>