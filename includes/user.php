<?php

	/**
	* Représente une entitée joueur. Permet de récupérer dplusieur niveaux d'information
	* et de changer certaines données. 
	*
	*	Public:
	*		LoadBasicInformations
	*		GetCurrentGames
	*		LogoutUser
	*/ 
	class User {

		/*---------------
		|	Propriétés 	|
		---------------*/
		private $_db;
		private $_userId;


		/*---------------
		|	Methodes 	|
		---------------*/
		/**
		* Constructeur
		* 
		* @param: $bdd_ressource => une reference sur la ressource MySQL ouverte
		* @param: $id => identifiant de l'utilisateur
		* @return void 
		*/
		public function 	__construct($bdd_ressource, $id) {
			$this->_db = $bdd_ressource;
			$this->_userId = $id;
		}

		/**
		*	Charge des infos basiques en $_SESSION et dans smarty si ce n'est pas deja fait, de maniere presque intelligente
		*	@param:
		*	@return: True si tout c'est bien passe, false sinon
		*/
		public function 	LoadBasicInformations() {
			
			global $smt; // THE smarty

			// Si on a pas toutes les infos du user (genre il vient de se connecter), on les recup
			if (!isset($_SESSION['user-nick'])) {
				// On récupère les infos
				$infos = $this->_db->GetRows("SELECT * FROM `players` WHERE `players`.`player_id` = '$this->_userId'");
				if (is_null($infos))
					return (false);
				
				// On set les variable de session
				$_SESSION['user-nick'] = $infos[0]['player_nick'];
				$_SESSION['user-score'] = $infos[0]['player_score'];
				$_SESSION['user-notif'] = $infos[0]['player_notification'];
				$_SESSION['user-availability'] = $infos[0]['player_available'];
				$_SESSION['user-email'] = $infos[0]['player_mail'];
			}

			// Ensuite on copie ces donnees dans Smarty. Lui en a besoin tout le temps :/
			$smt->assign('User_id', $_SESSION['user-id']);
			$smt->assign('User_nick', $_SESSION['user-nick']);
			$smt->assign('User_score', $_SESSION['user-score']);
			$smt->assign('User_notif', $_SESSION['user-notif']);
			$smt->assign('User_availability', $_SESSION['user-availability']);
			$smt->assign('User_email', $_SESSION['user-email']);
			$smt->assign('User_pic', self::GetUserPicture($_SESSION['user-id']));

			return (true);
		}

		/**
		*	Charge des infos basiques en $_SESSION et dans smarty si ce n'est pas deja fait, de maniere presque intelligente
		*	@param:
		*	@return: True si tout c'est bien passe, false sinon
		*/
		public function 	GetCurrentGames() {
			$res = array();
			
			// Récupération des parties en cours du joueur
			$games = $this->_db->GetRows("SELECT `games`.`game_id`, `games`.`game_current_player`, UNIX_TIMESTAMP(`games`.`game_start_date`) AS `Date` FROM `games` JOIN `players_in_games` ON `players_in_games`.`pig_game` = `games`.`game_id` WHERE `games`.`game_status` = 'pending' AND `players_in_games`.`pig_player` = '$this->_userId' AND `players_in_games`.`pig_player_status` = 'alive'");
			
			if (is_null($games))
				return (null);

			foreach ($games as $g) {
				$game = array('id' => $g['game_id'], 'current' => $g['game_current_player'], 'started' => FormatDate($g['Date']));

				// On recupere les noms des opposants pour les integrer dans l'UI
				$query = "SELECT `players`.`player_nick` AS `Nick`, `players`.`player_id` AS `ID`, `players_in_games`.`pig_color` AS `Color`, `players_in_games`.`pig_player_status` AS `Status` FROM `players_in_games` JOIN `players` ON `players`.`player_id` = `players_in_games`.`pig_player` WHERE `players_in_games`.`pig_game` = '$g[game_id]' ORDER BY `players_in_games`.`pig_order` ASC";
				$opponents = $this->_db->GetRows($query);
				$op_str = '';
				if (!is_null($opponents)) {
					foreach ($opponents as &$op) {
						if ($op['ID'] !== $this->_userId)
							$op_str .= (($op_str === '') ? '': ', ') . $op['Nick'];

						$op['Pic'] = self::GetUserPicture($op['ID']);
					}
				}

				$game['opponents'] = $op_str;
				$game['players'] = $opponents;

				// Ajout de la partie dans la liste des parties
				$res[] = $game;
			}

			return ($res);
		}

		/**
		*	Délogue l'utilisateur courant
		*	@return: void
		*/
		public function 	LogoutUser() {
			unset($_SESSION['user-id']);
			
			// Si on a toute les infos, on vire tout
			if (isset($_SESSION['user-nick'])) {
				unset($_SESSION['user-nick']);
				unset($_SESSION['user-score']);
				unset($_SESSION['user-notif']);
				unset($_SESSION['user-availability']);
			}
		}

		/**
		*	Modifie les preferences de notification de l'utilisateur
		*	@param: {String} $value: 0 ou 1
		*	@return: {Boolean} True si le changement s'est effectue, false sinon
		*/
		public function 	UpdateNotification($value) {
			if ($value !== '0' && $value !== '1')
				return (false);

			if ($this->_db->Execute("UPDATE `players` SET `player_notification` = '$value' WHERE `players`.`player_id` = '$this->_userId'")) {
				$_SESSION['user-notif'] = $value;
				return (true);
			}
			return (false);
		}

		/**
		*	Modifie la disponibilitée du joueur
		*	@param: {String} 	$value: 0 si indisponible, ou 1 si dispo
		*	@return: {Boolean} 	True si le changement s'est effectue, false sinon
		*/
		public function 	UpdateAvailability($value) {
			if ($value !== '0' && $value !== '1')
				return (false);

			if ($this->_db->Execute("UPDATE `players` SET `player_available` = '$value' WHERE `players`.`player_id` = '$this->_userId'")) {
				$_SESSION['user-availability'] = $value;
				return (true);
			}
			return (false);
		}

		/*
		*	Met a jour l'adresse email de l'utilisateur
		*	@param: {String} $email: Le nouvel email
		*	@return: {Boolean} True si le changement s'est effectue, false sinon
		*/
		public function 	UpdateEmail($email) {
			
			$email = $this->_db->SecureInput($email);

			if ($this->_db->Execute("UPDATE `players` SET `player_mail` = '$email' WHERE `players`.`player_id` = '$this->_userId'")) {
				$_SESSION['user-email'] = $email;
				return (true);
			}
			return (false);
		}



		/*
		*	Fonction statique qui retourne la photo de l'utilisateur
		*	@param: {Int} ou {String} $userID: L'ide de l'utilisateur
		*	@return: {String} Le chemin RELATIF vers la photo de l'utilisateur
		*/
		public static function 	GetUserPicture($userID) {
			if (file_exists('images/users/' . $userID . '.jpg') || file_exists('../images/users/' . $userID . '.jpg'))
				return ('images/users/' . $userID . '.jpg');
			else
				return ('images/users/noset.jpg');
		}
	}
?>