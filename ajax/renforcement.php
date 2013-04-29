<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/gameManager.php');


	// Recuperation des parametres
	if (!isset($_GET['game']) || !isset($_GET['player']) || !isset($_GET['place']) || !isset($_GET['action']))
		AjaxExit("Manque d'informations pour r&eacute;cup&eacute;rer le nombre d'unit&eacute;s");
	$gameID =	$_GET['game'];
	$player =	$_GET['player'];
	$place = 	$_GET['place'];
	$action = 	$_GET['action'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// On instancie la classe Game et on recupere l'infos
	$game = new gameManager($db, $gameID);
	$res = array('place' => $place);

	// Si le territoire appartient bien au joueur, on coninue
	if ($game->CheckTerritoryOwner($player, $place)) {

		// Ajout d'une unitée
		if ($action === 'add') {
			$res['action'] = 'add';
			
			// Appel de la fonction qui va bien
			$update = $game->UpdateRenforcement($player, $place, 1);
			if ($update !== true)
				AjaxExit($update);
		}
		// Retrait d'une unitée
		/*else if ($action === 'remove') {
			$res['action'] = 'remove';
			if (!$game->UpdateTerritoryUnities($place, -1))
				AjaxExit("Impossible d'annuler l'opération");
		}*/
		else
			AjaxExit('Erreur B00B5');
	}
	else
		AjaxExit('Ce territoire ne vous appartient pas');

	// Si tout c'est bien déroulé jusqu'ici, on récupère le nombre d'unitées updaté
	$res['units'] = $game->GetPlaceUnitsNumber($place);
	$db->close();

	// Echo reponse
	echo (json_encode($res, JSON_FORCE_OBJECT));
?>