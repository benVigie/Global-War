<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur et de la partie
	if (!isset($_GET['game']) || !isset($_GET['player']))
		AjaxExit("Manque d'informations pour r&eacute;cup&eacute;rer le nombre de renforts");
	$gameID = $_GET['game'];
	$player = $_GET['player'];
	// Permet de recuperer le VRAI nombre de renforts (aka les restant pour son tour de jeu) pour le joueur courant
	$renfortsRestants = isset($_GET['playerTurn']);

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on recupere l'infos
	$game = new gameManager($db, $gameID);

	// Selon l'info demandee, on recupere le nombre de renforts restant OU le nombre de renforts total
	$nb = ($renfortsRestants === true) ? $game->GetRenforcementLeft($player) : $game->GetRenforcementNumber($player);
	$res = array('player' => $player, 'renforcements' => $nb);
	$db->close();

	// Echo reponse
	echo (json_encode($res));
?>