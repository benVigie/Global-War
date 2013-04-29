<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur et de la partie
	if (!isset($_GET['game']) || !isset($_GET['player']))
		AjaxExit("Manque d'informations pour r&eacute;cup&eacute;rer le nombre d'unit&eacute;s");
	$gameID = $_GET['game'];
	$player = $_GET['player'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on recupere l'infos
	$game = new gameManager($db, $gameID);
	$res = array('player' => $player, 'units' => $game->GetUnitsNumber($player));
	$db->close();

	// Echo reponse
	echo (json_encode($res));
?>