<?php

	require_once ('includes/config.php');
	require_once ('includes/class.myBDD.php');
	require_once ('includes/user.php');
	require_once ('includes/gameManager.php');


	// Si l'utilisateur est pas logue, on le redirige en page d'accueil (login)
	if (!isset($_SESSION['user-id']))
		header('Location: index.php');
	// Si il a pas set de jeu, go back
	if (!isset($_GET['game']))
		header('Location: home.php');


	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect()) {
		SetMessage('Impossible de se connecter &agrave; la BDD.', true);
		Display('index.tpl', 'Home');
	}

	// TODO: Vérifier que le joueur a acces a cette partie
	$game = new gameManager($db, $_GET['game']);
	// $smt->assign('Players', $game->GetPlayers());
	// $smt->assign('Map', $game->LoadGame());

	// On recupere l'id du joueur
	$currentUser = new User($db, $_SESSION['user-id']);
	$currentUser->LoadBasicInformations();
	$smt->assign('GameID', $_GET['game']);
	
	$db->close();
	Display('game.tpl', 'Game');
?>