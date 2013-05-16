<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');

	// Recuperation de l'ID du joueur
	$player = $_SESSION['user-id'];

	// Check si on a bien une liste d'au moins un joueur
	if (!isset($_GET['players']))
		AjaxExit("Impossible de r&eacute;cup&eacute;rer les joueurs. Erreur 0");

	// On se prépare le tableau à la bien
	$playersArray = json_decode($_GET['players']);
	$playersArray[] = $player;

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');
	
	// On crée un object GameManager 
	$game = new GameManager($db);

	// Création du jeu
	$newID = $game->CreateNewGame($playersArray);
	if (!is_int($newID))
		AjaxExit($newID);

	$players = $game->GetPlayers();
	$current = $game->GetCurrentPlayer();

	$response = array('game' => $newID, 'players' => $players, 'current' => $current);
	$db->close();

	echo (json_encode((object)$response));
?>