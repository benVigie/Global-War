<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/statistics.php');

	// Recuperation de l'ID du joueur
	if (!isset($_GET['player']))
		AjaxExit('Player id missing');
	
	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Cannot connect to DB');
	
	// On instancie notre classe de stats
	$stats = new Statistics($db, $db->SecureInput($_GET['player']));

	$ret = array();

	// Recuperation des stats du joueur
	$ret['RankingPie'] = $stats->GetRankingPie();
	$ret['RankingEvolution'] = $stats->GetRankingEvolution();
	$ret['ColorPie'] = $stats->GetPlayerFavoriteColorsPie();
	
	// Close DB
	$db->close();

	echo (json_encode($ret));
?>