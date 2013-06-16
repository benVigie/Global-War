<?php
	require_once ('ajax-config.php');
	require_once ('../includes/class.myBDD.php');
	require_once ('../includes/user.php');
	
	// Si personne n'est connecte
	if (!isset($_SESSION['user-id']))
		AjaxExit('Merci de vous deloguer, manger un cheval sur un tricicle et reessayer.');

	// Ouverture de la BDD
	$db = new myBDD();
	if (!$db->connect())
		AjaxExit('Problemes de connexion BDD');

	$user = new User($db, $_SESSION['user-id']);

	// Initialisation de la reponse
	$res = array();

	// Recuperation des parametres
	if (isset($_GET['notification'])) {
		if ($user->UpdateNotification($_GET['notification'])){
			$res['modif'] = 'notif';
			$res['value'] = $_GET['notification'];
		}
		else
			$res['error'] = 'Impossible de changer les preferences de notifications.';
	}
	else if (isset($_GET['availability'])) {
		if ($user->UpdateAvailability($_GET['availability'])){
			$res['modif'] = 'availability';
			$res['value'] = $_GET['availability'];
		}
		else
			$res['error'] = 'Impossible de changer les preferences de notifications.';
	}
	else if (isset($_GET['email'])) {
		if ($user->UpdateEmail($_GET['email'])){
			$res['modif'] = 'email';
			$res['value'] = $_GET['email'];
		}
		else
			$res['error'] = 'Impossible de changer votre adresse email.';
	}
	else {
		$db->close();
		AjaxExit("Manque d'informations");
	}

	$db->close();

	// Echo reponse
	echo (json_encode((object)$res));
?>