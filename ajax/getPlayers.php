<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');

	// Recuperation de l'ID du joueur
	$player = $_SESSION['user-id'];

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// Faire cette putain de requete
	$playersList = $db->GetRows("SELECT * FROM `players` WHERE `players`.`player_id` != '$player'");
	if (is_null($playersList))
		AjaxExit('Impossible de recuperer les autres joueurs');

	foreach ($playersList as &$pl) {
		if (file_exists('../images/users/' . $pl['player_id'] . '.jpg'))
			$pl['player_picture'] = 'images/users/' . $pl['player_id'] . '.jpg';
		else
			$pl['player_picture'] = 'images/users/noset.jpg';
	}

	echo (json_encode($playersList));
	$db->close();
?>