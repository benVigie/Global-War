<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur
	$player = $_SESSION['user-id'];

	// Si on a pas l'ID de la partie, pas la peine 
	if (!isset($_GET['game']))
		AjaxExit('Pas de partie d&eacute;finie');
	$gameID = $_GET['game'];
	
	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on recupere les infos
	$game = new gameManager($db, $gameID);
	$players = $game->GetPlayers();
	$current = $game->GetCurrentPlayer();
	$db->close();

	$res = array('playerList' => $players, 'currentPlayer' => $current);
	echo (json_encode((object)$res));
?>		