<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur et de la partie
	if (!isset($_GET['game']) || !isset($_GET['player']))
		AjaxExit("Manque d'informations pour r&eacute;cup&eacute;rer le nombre de renforts");
	$gameID = $_GET['game'];
	$player = $_GET['player'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on récupère l'infos
	$game = new gameManager($db, $gameID);
	$actions = $game->GetLastAction($player);
	$db->close();

	// Echo reponse
	// echo (json_encode($actions, JSON_FORCE_OBJECT));
	echo (json_encode($actions));
?>