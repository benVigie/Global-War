<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	
	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	// Quand on inscrit un nouveau message
	if (isset($_GET['game']) && isset($_GET['message'])) {
		$player = $_SESSION['user-id'];
		$game = $db->SecureInput($_GET['game']);
		$message = $db->SecureInput($_GET['message']);

		$query = "INSERT INTO `chat` (`chat_id`, `chat_player_id`, `chat_private`, `chat_game_id`, `chat_message`, `chat_date`) VALUES (NULL, '$player', '0', '$game', '$message', CURRENT_TIMESTAMP)";

		$recorded = $db->Execute($query);
		$db->close();

		if ($recorded)
			echo (json_encode((object)(array('ok' => 'message enregistre'))));
		else
			AjaxExit("Impossible d'enregistre le message !");
	}
	// Recuperation des messages
	else if (isset($_GET['game']) && isset($_GET['get'])) {
		$res = array();
		$game = $db->SecureInput($_GET['game']);
		$mess = $db->GetRows("SELECT `chat`.`chat_message`, UNIX_TIMESTAMP(`chat`.`chat_date`) AS `Date`, `chat`.`chat_player_id` FROM `chat`  WHERE `chat`.`chat_game_id` = '$game' ORDER BY `chat`.`chat_date` ASC");

		// On traduit la date a la bien
		foreach ($mess as &$m) {
			$tmp = array();
			$tmp['message'] = $m['chat_message'];
			$tmp['date'] = FormatDate($m['Date']);
			$tmp['player'] = $m['chat_player_id'];
			$res[] = $tmp;
		}
		// var_dump($res);
		
		// Envoie de la liste des messages
		echo (json_encode((object)(array('chat' => $res))));
	}
	// Si il manque des parametres
	else
		AjaxExit("Manque d'informations");
?>