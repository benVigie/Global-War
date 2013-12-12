<?php

	require_once ('includes/config.php');
	require_once ('includes/class.myBDD.php');
	require_once ('includes/user.php');
	require_once ('includes/statistics.php');
	require_once ('includes/login.php');

	// DB instance
	$db = null;

	// If user session has expired, we will try to log the user using cookies
	if (!isset($_SESSION['user-id'])) {

		// Open DB
		$db = new myBDD();
		if (!$db->connect())
			header('Location: index.php');
			
		// Try to reconnect user by using cookies (if exist)
		if (tryReloginWithCookie($db) !== true)
			header('Location: index.php');
	}

	// Ouverture de la BDD
	if (is_null($db)) {
		$db = new myBDD();
		if (!$db->connect()) {
			SetMessage('Impossible de se connecter &agrave; la BDD.', true);
			Display('home.tpl', 'Home');
		}
	}

	// On recupere l'instance du joueur
	$currentUser = new User($db, $_SESSION['user-id']);

	// Dans le cas ou il demande de se deconnecter
	if (isset($_GET['logout'])) {
		$currentUser->LogoutUser();
		$db->close();
		header('Location: index.php');
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

	// Recuperation de la liste des parties
	$gamesLost = $currentUser->GetLostGamesAvailable();
	if (!is_null($gamesLost))
		$smt->assign('GamesLost', $gamesLost);

	// Recuperation du classement general
	$podium = $stats->GetPodium();
	if (!is_null($podium))
		$smt->assign('Ranking', $podium);
	
	// Get Ranking table
	$rqtbl = $stats->GetRankingTable();
	if (!is_null($rqtbl))
		$smt->assign('RankingTable', $rqtbl);
	// Get global ranking table
	$grqtbl = $stats->GetGlobalRankingTable();
	if (!is_null($grqtbl))
		$smt->assign('GlobalRankingTable', $grqtbl);
	// Get player of the month
	$potm = $stats->GetPlayerOfTheMonth();
	if (!is_null($potm))
		$smt->assign('PlayerOfTheMonth', $potm);

	// Recuperation des stats du joueur
	$rankingPie = $stats->GetRankingPie();
	if (!is_null($rankingPie))
		$smt->assign('RankingPie', $rankingPie);

	$rankingEvolution = $stats->GetRankingEvolution();
	if (!is_null($rankingEvolution))
		$smt->assign('RankingEvolution', $rankingEvolution);

	$colorPie = $stats->GetPlayerFavoriteColorsPie();
	if (!is_null($colorPie))
		$smt->assign('ColorPie', $colorPie);


	// Chargement d'informatiosn basiques
	$currentUser->LoadBasicInformations();
	$db->close();
	Display('home.tpl', 'Home');
?>