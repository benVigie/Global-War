<?php

	require_once ('includes/config.php');
	require_once ('includes/class.myBDD.php');
	require_once ('includes/user.php');
	

	// Si l'utilisateur est pas logue, on le redirige en page d'accueil (login)
	if (!isset($_SESSION['user-id']))
		header('Location: index.php');

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect()) {
		SetMessage('Impossible de se connecter &agrave; la BDD.', true);
		Display('info.tpl', 'Informations');
	}

	// On recupere l'instance du joueur
	// Chargement d'informatiosn basiques
	$currentUser = new User($db, $_SESSION['user-id']);
	$currentUser->LoadBasicInformations();
	$db->close();

	Display('info.tpl', 'Informations');
?>