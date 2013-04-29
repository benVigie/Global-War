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

	// Récupération de l'heure
	$lastTurn = $db->GetRows("SELECT `strokes`.`stroke_date` FROM `strokes` WHERE `strokes`.`stroke_game` = '$gameID' AND `strokes`.`stroke_player` = '$player' AND `strokes`.`stroke_type` = 'done' ORDER BY `strokes`.`stroke_date` DESC");
	$time = (is_null($lastTurn)) ? null : $lastTurn[0]['stroke_date'];

	$game = new gameManager($db, $gameID);
	echo (json_encode($game->LoadGame($time)));
	$db->close();
?>