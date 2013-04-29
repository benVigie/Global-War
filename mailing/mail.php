<?php

	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

	require_once ('class.myMail.php');

	// Creation d'un objet mail
	$mail = new myMail();

	if (!isset($_POST['type']))
		die("Manque d'informations");


	// Selon le type d'envoie, on construit un mail different
	switch ($_POST['type']) {
		case 'bugReport':
			if (!sendBugReport($mail))
				echo "Envoie de rapport de bug echoue";
			break;
		case 'newGame':
			if (!sendNewGame($mail))
				echo "Envoie de mail de nouvelle partie echoue";
			break;
		case 'yourTurn':
			if (!sendNewTurn($mail))
				echo "Envoie de mail de nouveau tour echoue";
			break;
		case 'endGame':
			if (!sendEndGame($mail))
				echo "Envoie du mail de fin de partie echoue";
			break;

		default:
			echo "Type de mail mon reconnu.";
			break;
	}

	
	function sendBugReport($mail) {
		if (!isset($_POST['cause']) || !isset($_POST['importance']) || !isset($_POST['message']))
			return (false);

		$cause = $_POST['cause'];
		$importance = $_POST['importance'];
		$message = $_POST['message'];

		return ($mail->Send(null, 'Global War - Rapport de bug / reclamation', "<h2>$cause</h2>Importance: <strong>$importance</strong><br/><br/>Description de la demande:<br/>$message"));
	}

	function sendNewGame($mail) {
		if (!isset($_POST['creator']) || !isset($_POST['gameID']) || !isset($_POST['youStart']) || !isset($_POST['to']))
			return (false);

		$creator = $_POST['creator'];
		$gameID = $_POST['gameID'];
		$youStart = $_POST['youStart'];

		$message = "<h2>C'est l'heure de jouer !</h2>";
		$message .= "<strong>$creator</strong> vous d&eacute;fie dans une nouvelle partie.";
		$message .= ($youStart == true) ? " Et en plus, c'est &agrave; vous de jouer !" : '';
		$message .= '<br/><br/><a style="text-decoration: none; color: steelblue" href="http://htmlengine/old/ben/global-war/game.php?game=' . $gameID . '">Bonne chance !</a><br/>';

		return ($mail->Send($_POST['to'], 'Nouvelle partie', $message));
	}

	function sendNewTurn($mail) {
		if (!isset($_POST['gameID']) || !isset($_POST['to']))
			return (false);

		$gameID = $_POST['gameID'];
		
		$message = "<h2>C'est votre tour de jouer !</h2>";
		$message .= "Il y a eu un peu de casse, mais les troupes sont pr&ecirc;tes.";
		$message .= '<br/><br/><a style="text-decoration: none; color: steelblue" href="http://htmlengine/old/ben/global-war/game.php?game=' . $gameID . '">Bonne chance !</a><br/>';

		return ($mail->Send($_POST['to'], "C'est votre tour", $message));
	}

	function sendEndGame($mail) {
		if (!isset($_POST['position']) || !isset($_POST['points']) || !isset($_POST['to']))
			return (false);

		$position = $_POST['position'];
		$points = $_POST['points'];
		
		if ($position === 'winner') {
			$message = "<h2>F&eacute;licitations !</h2>";
			$message .= "C'&eacute;tait un rude combat, mais vous avez gagn&eacute; soldat. Bravo !<br/>";
			$message .= "Votre victoire vient de vous rapporter " . $points . " points.";
			$message .= '<br/><br/>Une fois remis de vos blessures, n\'h&eacute;sitez pas &agrave; <a style="text-decoration: none; color: steelblue" href="http://htmlengine/old/ben/global-war/">revenir sur le champ de bataille</a><br/>';
		}
		else {
			$message = "<h2>Mauvaise nouvelle...</h2>";
			$message .= "Tout vos soldats sont morts. Vous avez &eacute;t&eacute; d&eacute;fait. En m&ecirc;me temps, ca sentait le sapin depuis quelques tours d&eacute;j&agrave;... :)<br/>Votre  ";
			switch ($position) {
				case 'two':
					$message .= 'deuxi&egrave;me place vous rapporte ' . $points . ' points.';
					break;
				case 'three':
					$message .= 'troisi&egrave;me place vous rapporte ' . $points . ' points.';
					break;
				case 'four':
					$message .= 'derni&egrave;re place (lol) ne vous rapporte aucun point.';
					break;	
			}
			$message .= '<br/><br/>Mais bon, la bonne nouvelle, <a style="text-decoration: none; color: steelblue" href="http://htmlengine/old/ben/global-war/">c\'est que vous pouvez lancer une nouvelle partie !</a><br/>';
		}

		return ($mail->Send($_POST['to'], "Partie terminée", $message));
	}
	
?>