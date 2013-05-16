<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');


	// Recuperation des parametres
	if (!isset($_GET['game']))
		AjaxExit("Aucune partie associe");
	$gameID =	$_GET['game'];
	
	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game
	$game = new gameManager($db, $gameID);

	// Si setColor est sette, le joueur a choisi une couleur
	if (isset($_GET['setColor'])) {
		$color = '#' . $_GET['setColor'];
		$player = $_SESSION['user-id'];
		
		if ($game->UpdatePlayerColor($player, $color))
			$res = array('colorUpdated' => $color);
		else
			AjaxExit("Impossible de changer la couleur :(");
	}
	// Sinon on requete la liste des couleurs dispo
	else {
		$colors = $game->GetAvailablesColors();
		$res = array('colors' => $colors);
	}

	$db->close();

	// Echo reponse
	echo (json_encode((object)$res));
?>