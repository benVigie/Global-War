<?php

	/**
	* Permet de recuperer les statistiques generales et les statistiques d'un joueur particulier
	*
	*	Public:
	*		
	*/ 
	class Statistics {

		/*---------------
		|	Propriétés 	|
		---------------*/
		private $_db;
		private $_playerID;


		/*---------------
		|	Methodes 	|
		---------------*/
		/**
		* Constructeur
		* 
		* @param: $bdd_ressource => une reference sur la ressource MySQL ouverte
		* @param: $id => identifiant de l'utilisateur pour lequel on veut les stats
		* @return void 
		*/
		public function 	__construct($bdd_ressource, $player) {
			$this->_db = $bdd_ressource;
			$this->_playerID = $player;
		}

		/**
		*	Recupere les 3 premieres personnes du classement
		*	
		*	@param:
		*	@return: {Array} Un Tableau contenant les noms et score et des 3 premiers au classement 
		*/
		public function 	GetPodium() {

			$rank = $this->_db->GetRows("SELECT `players`.`player_nick` AS `Nick`, `players`.`player_score` AS `Score`, `players`.`player_id` AS `ID` FROM `players` ORDER BY `players`.`player_score` DESC LIMIT 0, 3");
			if (is_null($rank))
				return (null);

			// On ajoute la petite image qui vas bien
			foreach ($rank as &$r)
				$r['Pic'] = User::GetUserPicture($r['ID']);

			return ($rank);
		}

		/**
		*	Recupere la position du joueur. SI il est deja dans les 3 premiers ou si une erreur survient, on return NULL
		*	
		*	@param:
		*	@return: {String} La chaine du classement joueur bien formatee. 
		*/
		public function 	GetPlayerRank() {
			$pos;
			$score;

			$rank = $this->_db->GetRows("SELECT *
										FROM (
										SELECT `players`.`player_score` AS `Score`, `players`.`player_id` AS `ID`, @rownum := @rownum + 1 AS rank
										FROM `players`,
										(SELECT @rownum := 0) r
										ORDER BY `players`.`player_score` DESC) ranking
										WHERE `ranking`.`ID` = '$this->_playerID'");
			if (is_null($rank))
				return (null);

			// Si le joueur est dans les 3 premiers, pas la peine de lui donner son classement, il apparaitra deja sur le podium
			$pos = intval($rank[0]['rank']);
			if ($pos <= 3)
				return (null);

			$score = intval($rank[0]['Score']);

			return ($pos . "ème ($score point" . (($score === 0 || $score === 1) ? '' : 's') . ')');
		}

		/**
		*	Recupere les differents classement du joueur et prepare les donnees chart.js compliant 
		*	
		*	@param:
		*	@return: {Array} Un Tableau contenant les valeures a fournir a chart.js ainsi que la legende 
		*/
		public function 	GetRankingPie() {
			$stats = array('winner' => 0, 'two' => 0, 'three' => 0, 'four' => 0);
			$st = '';
			$legende = '';


			$rank = $this->_db->GetRows("SELECT `players_in_games`.`pig_player_status` FROM `players_in_games` WHERE `players_in_games`.`pig_player` = '$this->_playerID' AND  `players_in_games`.`pig_player_status` != 'alive'");
			if (is_null($rank))
				return (null);

			// Calcul du classement du joueur pour chaque partie
			foreach ($rank as $r)
				$stats[$r['pig_player_status']]++;

			if ($stats['winner'] > 0) {
				$st .= '{value: ' . $stats['winner'] . ',color:"#4d84a0"}';
				$legende .= '<span style="background-color:#4d84a0;">&nbsp;</span> Premier ';
			}
			if ($stats['two'] > 0) {
				$st .= ($st == '') ? '' : ',';
				$st .= '{value: ' . $stats['two'] . ',color:"#97bbcd"}';
				$legende .= '<span style="background-color:#97bbcd;">&nbsp;</span> Second ';
			}
			if ($stats['three'] > 0) {
				$st .= ($st == '') ? '' : ',';
				$st .= '{value: ' . $stats['three'] . ',color:"#c5d9e3"}';
				$legende .= '<span style="background-color:#c5d9e3;">&nbsp;</span> Troisi&egrave;me ';
			}
			if ($stats['four'] > 0) {
				$st .= ($st == '') ? '' : ',';
				$st .= '{value: ' . $stats['four'] . ',color:"#ededed"}';
				$legende .= '<span style="background-color:#ededed;">&nbsp;</span> Quatri&egrave;me ';
			}

			$res = array('values' => $st, 'legend' => $legende);

			return ($res);
		}


		/**
		*	Recupere les 10 derniers match du joueur pour les presenter sous forme de graphe.
		*	
		*	@param:
		*	@return: {Array} Un Tableau 
		*/
		public function 	GetRankingEvolution() {
			$totalGames = 0;
			$nbVictories = 0;
			$ret = array('values' => '', 'label' => '');

			// Recuperation des classement du joueur sur les 10 denieres parties
			$rank = $this->_db->GetRows("SELECT `players_in_games`.`pig_player` , `players_in_games`.`pig_player_status`
											FROM `games`
											JOIN `players_in_games` ON `games`.`game_id` = `players_in_games`.`pig_game`
											WHERE `games`.`game_status` = 'ended' AND `players_in_games`.`pig_player` = '$this->_playerID'
											ORDER BY `games`.`game_end_date` ASC");
											// ORDER BY `games`.`game_end_date` DESC
											// LIMIT 0 , 8");
			
			if (is_null($rank) || (count($rank) < 2))
				return (null);


			/*// On traite chaque donnee en partant de la plus ancienne pour remonter vers la derniere partie
			for ($i = (count($rank) - 1); $i >= 0 ; $i--) {*/
			// Pour toutes les parties
			$max = count($rank);
			for ($i = 0; $i < $max ; $i++) {
				// On incremente le total de parties
				$totalGames++;

				// Si il a gagne, on incremente son compteur de victoires
				if ($rank[$i]['pig_player_status'] === 'winner')
					$nbVictories++;

				// Si on est dans les 8 dernieres parties, alors on stock les valeures pour les afficher dans le graph
				if ($i >= ($max - 8)) {
					// Set de son ratio de victoire courant
					$ret['values'] .= ($ret['values'] === '') ? ceil($nbVictories / $totalGames * 100) : ',' . ceil($nbVictories / $totalGames * 100);

					// Ajout du bon libelle
					switch ($rank[$i]['pig_player_status']) {
						case 'winner':
							$ret['label'] .= ($ret['label'] === '') ? "'Victoire'" : ',"Victoire"';
							break;
						case 'two':
							$ret['label'] .= ($ret['label'] === '') ? '"Second"' : ',"Second"';
							break;
						case 'three':
							$ret['label'] .= ($ret['label'] === '') ? '"Troisième"' : ',"Troisième"';
							break;
						case 'four':
							$ret['label'] .= ($ret['label'] === '') ? '"Mega loose"' : ',"Mega loose"';
							break;
					}
				}
			}

			return ($ret);
		}


	}
?>