<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');


	// Recuperation des parametres
	if (!isset($_GET['game']) || !isset($_GET['player']))
		AjaxExit("Manque d'informations (statut)");
	$gameID =	$_GET['game'];
	$player =	$_GET['player'];
	
	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on recupere l'infos
	$game = new gameManager($db, $gameID);
	
	// Si tout c'est bien déroulé jusqu'ici, on récupère le nombre d'unitées updaté
	$res['units'] = $game->GetPlaceUnitsNumber($place);
	$db->close();

	// Echo reponse
	echo (json_encode((object)$res));
?>