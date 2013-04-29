<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');


	// Recuperation des parametres
	if (!isset($_GET['game']) || !isset($_GET['player']) || !isset($_GET['src_place']) || !isset($_GET['dst_place']) || !isset($_GET['src_nb']) || !isset($_GET['dst_nb']))
		AjaxExit("Manque d'informations pour r&eacute;cup&eacute;rer le nombre d'unit&eacute;s");
	$gameID	= $_GET['game'];
	$player	= $_GET['player'];
	$src 	= $_GET['src_place'];
	$dst 	= $_GET['dst_place'];
	$s_nb	= $_GET['src_nb'];
	$d_nb	= $_GET['dst_nb'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game
	$game = new gameManager($db, $gameID);
	
	// On opère le deéplacement
	if (!$game->Move($player, $src, $dst, $s_nb, $d_nb)) {
		$db->close();
		AjaxExit("Impossible d'opérer le déplacement");
	}

	$res = array('src' => $game->GetPlaceUnitsNumber($src), 'dest' => $game->GetPlaceUnitsNumber($dst));
	$db->close();

	// Echo reponse
	echo (json_encode($res, JSON_FORCE_OBJECT));
?>