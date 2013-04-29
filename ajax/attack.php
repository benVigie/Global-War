<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');


	// Recuperation des parametres
	if (!isset($_GET['game']) || !isset($_GET['player']) || !isset($_GET['Aplace']) || !isset($_GET['Dplace']))
		AjaxExit("Manque d'informations pour attaquer");
	$gameID =	$_GET['game'];
	$player =	$_GET['player'];
	$Aplace = 	$_GET['Aplace'];
	$Dplace = 	$_GET['Dplace'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game
	$game = new gameManager($db, $gameID);
	$res = $game->Attack($player, $Aplace, $Dplace);
	
	$db->close();

	// Echo reponse
	echo (json_encode($res, JSON_FORCE_OBJECT));
?>