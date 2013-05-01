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
		// Si il y a eu des coups joués entre son dernier coup et maintenant, ça vaut le coup de charger une vieille map
		if ($game->GetLastAction($player) !== null) {
			// Récupération de l'heure du dernier tour du joueur
			$lastTurn = $db->GetRows("SELECT `strokes`.`stroke_date` FROM `strokes` WHERE `strokes`.`stroke_game` = '$gameID' AND `strokes`.`stroke_player` = '$player' ORDER BY `strokes`.`stroke_date` DESC");
			$time = (is_null($lastTurn)) ? null : $lastTurn[0]['stroke_date'];
		}
	}
		
	echo (json_encode($game->LoadGame($time)));
	$db->close();
?>