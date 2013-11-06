<?php
	require_once ('ajax-config.php');
	require_once ('../includes/mail.php');


	// On crée un object GameManager 
	$mailer = new GWMailer();

	$infos['cause'] = $_POST['cause'];
	$infos['importance'] = $_POST['importance'];
	$infos['message'] = $_POST['message'];
	$infos['rapporteur'] = $_POST['rapporteur'];
	$infos['email'] = $_POST['email'];

	if (!$mailer->sendBugReport($infos))
		echo "Envoie du rapport échoué...";
	else
		echo ("Rapport envoyé. Merci de votre participation !");
?>