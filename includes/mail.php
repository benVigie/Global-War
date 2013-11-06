<?php

	require_once ('class.myMail.php');

	/**
	* 	GWMailer Prépare et envoie un mail selon le type d'action demandé. Ca peut etre un mail de nouvelle partie, de fin de tour, etc...
	*
	*	Public Methods:
	*		__construct
	*
	*	Private Methods:
	*
	*/ 
	class GWMailer {

		/*---------------
		|	Propriétés 	|
		---------------*/
		private $_mail;


		/*---------------
		|	Methodes 	|
		---------------*/
		/**
		* Constructeur
		* 
		* @param 
		* @return  
		*/
		public function 	__construct() {
			$this->_mail = new myMail();
		}



		public function sendBugReport($infosArray) {
			$cause = $infosArray['cause'];
			$importance = $infosArray['importance'];
			$message = $infosArray['message'];
			$rapporteur = $infosArray['rapporteur'];
			$email = $infosArray['email'];

			return ($this->_mail->Send(null, 'Global War - Rapport de bug / reclamation', "<h2>$cause</h2>Importance: <strong>$importance</strong><br/><strong>Rapporteur: $rapporteur ($email)</strong><br/><br/>Description de la demande:<br/>$message"));
		}

		public function sendNewGame($infosArray) {
			$creator = $infosArray['creator'];
			$gameID = $infosArray['gameID'];
			$youStart = $infosArray['youStart'];

			$message = "<h2>C'est l'heure de jouer !</h2>";
			$message .= "<strong>$creator</strong> vous d&eacute;fie dans une nouvelle partie.";
			$message .= ($youStart == true) ? " Et en plus, c'est &agrave; vous de jouer !" : '';
			$message .= '<br/><br/><a style="text-decoration: none; color: steelblue" href="http://www.vigie-benjamin.fr/Global-War/game.php?game=' . $gameID . '">Bonne chance !</a><br/>';

			return ($this->_mail->Send($infosArray['to'], 'Nouvelle partie', $message));
		}

		public function sendNewTurn($infosArray) {
			$gameID = $infosArray['gameID'];
			
			$message = "<h2>C'est votre tour de jouer !</h2>";
			$message .= "Il y a eu un peu de casse, mais les troupes sont pr&ecirc;tes.";
			$message .= '<br/><br/><a style="text-decoration: none; color: steelblue" href="http://www.vigie-benjamin.fr/Global-War/game.php?game=' . $gameID . '">Bonne chance !</a><br/>';

			return ($this->_mail->Send($infosArray['to'], "C'est votre tour", $message));
		}

		public function sendEndGame($infosArray) {
			$position = $infosArray['position'];
			$points = $infosArray['points'];
			
			if ($position === 'winner') {
				$message = "<h2>Felicitations !</h2>";
				$message .= "C'&eacute;tait un rude combat, mais vous avez gagn&eacute; soldat. Bravo !<br/>";
				$message .= "Votre victoire vient de vous rapporter " . $points . " points.";
				$message .= '<br/><br/>Une fois remis de vos blessures, n\'h&eacute;sitez pas &agrave; <a style="text-decoration: none; color: steelblue" href="http://www.vigie-benjamin.fr/Global-War/">revenir sur le champ de bataille</a><br/>';
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
				$message .= '<br/><br/>Mais bon, la bonne nouvelle, <a style="text-decoration: none; color: steelblue" href="http://www.vigie-benjamin.fr/Global-War/">c\'est que vous pouvez lancer une nouvelle partie !</a><br/>';
			}

			return ($this->_mail->Send($infosArray['to'], "Partie terminée", $message));
		}

	}
	
?>