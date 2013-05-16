<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');


	// Recuperation des parametres
	if (!isset($_GET['game']) || !isset($_GET['player']))
		AjaxExit("Manque d'informations pour finir le tour");
	$gameID =	$_GET['game'];
	$player =	$_GET['player'];
	
	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on recupere l'infos
	$game = new gameManager($db, $gameID);
	
	// On verifie que c'est bien a ce joueur de jouer
	if ($game->GetCurrentPlayer() != intval($player))
		AjaxExit("Mais... C'est pas son tour !");

	// Tentative de fin de tour. Si tout est ok, on le notifie
	if ($game->EndTurn($player)) {
		// Si tout c'est bien déroulé jusqu'ici, on récupère le nombre d'unitées updaté
		echo (json_encode((object)(array('ok' => 'fin du tour'))));
		$db->close();
	}
	// Sinon, message d'erreur
	else
		AjaxExit('Ouuuups, petit probleme de fin de tour...');

?>