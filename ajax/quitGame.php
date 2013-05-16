<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur
	$player = $_SESSION['user-id'];

	// Check si on a bien une liste d'au moins un joueur
	if (!isset($_GET['game']))
		AjaxExit("Impossible de quitter le jeu. Erreur 0");

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');
	
	// On crée un object GameManager 
	$game = new GameManager($db, $db->SecureInput($_GET['game']));

	// On se casse !
	$withoutLoosePoints = $game->PlayerStopDemand($player);
	$db->close();

	// Creation du message de retour
	$response = array();

	if ($withoutLoosePoints)
		$response['free'] = "Vous avez quitt&eacute; la partie sans perdre de points";
	else
		$response['lossePoints'] = "Vous avez quitt&eacute; une partie en cours. 3 points vous ont &eacute;t&eacute; amput&eacute;s !";

	echo (json_encode((object)$response));
?>