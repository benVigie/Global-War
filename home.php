<?php

	require_once ('includes/config.php');
	require_once ('includes/class.myBDD.php');
	require_once ('includes/user.php');
	require_once ('includes/statistics.php');


	// Si l'utilisateur est pas logue, on le redirige en page d'accueil (login)
	if (!isset($_SESSION['user-id'])) {
		// header('Location: index.php');
		Display('login.tpl', 'Connection');
	}

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect()) {
		SetMessage('Impossible de se connecter &agrave; la BDD.', true);
		Display('home.tpl', 'Home');
	}

	// On recupere l'instance du joueur
	$currentUser = new User($db, $_SESSION['user-id']);

	// Dans le cas ou il demande de se deconnecter
	if (isset($_GET['logout'])) {
		$currentUser->LogoutUser();
		$db->close();
		// header('Location: index.php');
		Display('login.tpl', 'Connection');
	}

	// Si un changement de photo est requis...
	if (isset($_FILES['newPic'])) {
		CreateVignette($_FILES['newPic'], ('images/users/' . $_SESSION['user-id'] . '.jpg'), 75, 75);
		header('Location: home.php');
	}

	// On instancie notre classe de stats
	$stats = new Statistics($db, $_SESSION['user-id']);

	// Recuperation de la liste des parties
	$games = $currentUser->GetCurrentGames();
	if (!is_null($games))
		$smt->assign('Games', $games);

	// Recuperation du classement general
	$podium = $stats->GetPodium();
	if (!is_null($podium))
		$smt->assign('Ranking', $podium);
	$pos = $stats->GetPlayerRank();
	if (!is_null($pos))
		$smt->assign('OutOfRank', $pos);

	// Recuperation du classement du joueur
	$rankingPie = $stats->GetRankingPie();
	if (!is_null($rankingPie))
		$smt->assign('RankingPie', $rankingPie);

	$rankingEvolution = $stats->GetRankingEvolution();
	if (!is_null($rankingEvolution))
		$smt->assign('RankingEvolution', $rankingEvolution);


	// Chargement d'informatiosn basiques
	$currentUser->LoadBasicInformations();
	$db->close();
	Display('home.tpl', 'Home');
?>