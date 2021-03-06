<?php

	require_once ('includes/config.php');
	require_once ('includes/class.myBDD.php');
	require_once ('includes/login.php');

	// DB instance
	$db = null;

	// Si le joueur est deja connu, redirection home
	if (isset($_SESSION['user-id']))
		header('Location: home.php');
	else
	{
		// Open DB
		$db = new myBDD();
		if (!$db->connect())
			Display('login.tpl', 'Connection');

		if (tryReloginWithCookie($db) === true)
			header('Location: home.php');
	}

	// Si il tente de s'identifier
	if (isset($_POST['password'])) {
		// Ouverture BDD
		if (is_null($db)) {
			$db = new myBDD();
			if (!$db->connect()) {
				SetMessage('Impossible de se connecter &agrave; la BDD.', true);
				Display('login.tpl', 'Connection');
			}
		}

		// Tentative de connection
		$res = tryToLog($db);
		$db->close();
		if ($res !== true) {
			SetMessage($res, true);
			Display('login.tpl', 'Connection');
		}
		else
			header('Location: home.php');
	}
	else
		Display('login.tpl', 'Connection');
?>