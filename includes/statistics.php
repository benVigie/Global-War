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
		private $_cache;
		private $_cacheExpirationTime 	= 864000;	// Cache data for maximum 10 days == 3600 * 24 * 10


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
			
			// inityalyze cache library
			$this->_cache = new phpFastCache();
		}

		/**
		*	Recupere les 3 premieres personnes du classement
		*	
		*	@param:
		*	@return: {Array} Un Tableau contenant les noms et score et des 3 premiers au classement 
		*/
		public function 	GetPodium() {
			// try to retreive datas from cache
			$res = $this->_cache->get('Podium');

			// If don't exist, recalculate the datas
			if ($res == null) {
				$this->updatePodium();

				$res = $this->_cache->get('Podium');
				if ($res == null)
					return (null);
				return ($res);
			}

			return ($res);
		}


		/**
		*	Recupere les differents classement du joueur et prepare les donnees chart.js compliant 
		*	
		*	@param:
		*	@return: {Array} Un Tableau contenant les valeures a fournir a chart.js ainsi que la legende 
		*/
		public function 	GetRankingPie() {
			
			// try to retreive datas from cache
			$res = $this->_cache->get('RankingPiePlayer' . $this->_playerID);

			// If don't exist, recalculate the datas
			if ($res == null) {
				$this->updatePlayerRankingPie($this->_playerID);

				$res = $this->_cache->get('RankingPiePlayer' . $this->_playerID);
				if ($res == null)
					return (null);
				return ($res);
			}

			return ($res);
		}

		/**
		*	Get player favorite color for all ended games.
		*	Note that datas are formated to be display with Chart.js in a pie 
		*	
		*	@param:
		*	@return: {Array} Array of colors formated for Chart.js
		*/
		public function 	GetPlayerFavoriteColorsPie() {
			
			// try to retreive datas from cache
			$res = $this->_cache->get('FavoriteColorsPlayer' . $this->_playerID);

			// If don't exist, recalculate the datas
			if ($res == null) {
				$this->updatePlayerFavoriteColorPie($this->_playerID);

				$res = $this->_cache->get('FavoriteColorsPlayer' . $this->_playerID);
				if ($res == null)
					return (null);
				return ($res);
			}

			return ($res);
		}


		/**
		*	Recupere les 10 derniers match du joueur pour les presenter sous forme de graphe.
		*	
		*	@param:
		*	@return: {Array} Un Tableau 
		*/
		public function 	GetRankingEvolution() {

			// try to retreive datas from cache
			$res = $this->_cache->get('RankingEvolutionPlayer' . $this->_playerID);

			// If don't exist, recalculate the datas
			if ($res == null) {
				$this->updatePlayerRankingEvolution($this->_playerID);

				$res = $this->_cache->get('RankingEvolutionPlayer' . $this->_playerID);
				if ($res == null)
					return (null);
				return ($res);
			}

			return ($res);
		}

		/**
		*	Get ranking table from cache (or compute it if doesn't exist)
		*	
		*	@param:
		*	@return: {Array} Array of players from winner to looser
		*/
		public function 	GetRankingTable() {
			
			// try to retreive datas from cache
			$res = $this->_cache->get('RankingTable');

			// If don't exist, recalculate the datas
			if ($res == null) {
				$this->updateRankingTable();

				$res = $this->_cache->get('RankingTable');
				if ($res == null)
					return (null);
				return ($res);
			}

			return ($res);
		}

		/**
		*	Get global ranking table from cache (or compute it if doesn't exist)
		*	
		*	@param:
		*	@return: {Array} Array of players from winner to looser
		*/
		public function 	GetGlobalRankingTable() {
			
			// try to retreive datas from cache
			$res = $this->_cache->get('GlobalRankingTable');

			// If don't exist, recalculate the datas
			if ($res == null) {
				$this->updateGlobalRankingTable();

				$res = $this->_cache->get('GlobalRankingTable');
				if ($res == null)
					return (null);
				return ($res);
			}

			return ($res);
		}

		/**
		*	Get player of the month !
		*	
		*	@param:
		*	@return: {Array} Array with player id, nick and score
		*/
		public function 	GetPlayerOfTheMonth() {
			
			// try to retreive datas from cache
			$res = $this->_cache->get('PlayerOfTheMonth');

			// If don't exist, recalculate the datas
			if ($res == null) {
				$potm = $this->_db->GetRows("SELECT `game_periods`.`gp_player_points` AS `score`, `players`.`player_id`  AS `ID`, `players`.`player_nick`  AS `nick` FROM `game_periods` JOIN `players` ON `game_periods`.`gp_player_of_the_previous_period` = `players`.`player_id` ORDER BY `game_periods`.`gp_start_date` ASC LIMIT 0, 1");
				if ((is_null($potm)) || ($potm[0]['ID'] == '0'))
					return (null);

				$potm[0]['pic'] = User::GetUserPicture($potm[0]['ID']);
				$this->_cache->set('PlayerOfTheMonth', $potm[0] , $this->_cacheExpirationTime);

				return ($potm);
			}

			return ($res);
		}

		/**
		*	Update statistics for a specified player. Call when a game is finished.
		*	
		*	@param:
		*	@return:
		*/
		public function 	UpdatePlayerStats($player) {
			
			$this->updatePlayerRankingPie($player);
			$this->updatePlayerFavoriteColorPie($player);
			$this->updatePlayerRankingEvolution($player);
		}

		/**
		*	Update ranking table. Call when a game is finished
		*	
		*	@param:
		*	@return:
		*/
		public function 	UpdateRanking() {

			$this->updateRankingTable();
			$this->updateGlobalRankingTable();
			$this->updatePodium();
		}

		/**
		*	Update ranking table. Call when a game is finished
		*	
		*	@param:
		*	@return:
		*/
		public function 	RefreshRankingTable() {

			$this->updateRankingTable();
		}

		/**
		*	Recupere les 3 premieres personnes du classement
		*	
		*	@param:
		*	@return: {Array} Un Tableau contenant les noms et score et des 3 premiers au classement 
		*/
		private function 	updatePodium() {

			$rank = $this->_db->GetRows("SELECT `players`.`player_nick` AS `Nick`, `players`.`player_score` AS `Score`, `players`.`player_id` AS `ID` FROM `players` ORDER BY `players`.`player_score` DESC LIMIT 0, 3");
			if (is_null($rank))
				return (null);

			// On ajoute la petite image qui vas bien
			foreach ($rank as &$r)
				$r['Pic'] = User::GetUserPicture($r['ID']);

			// Cache data
			$this->_cache->set('Podium', $rank , $this->_cacheExpirationTime);
		}

		/**
		*	Update Player ranking pie and store datas in cache
		*	
		*	@param: {Int} 	$player 	ID of the player to update
		*	@return: 
		*/
		private function 	updatePlayerRankingPie($player) {
			$stats = array('winner' => 0, 'two' => 0, 'three' => 0, 'four' => 0);
			$st = '[';
			$legende = '';

			$rank = $this->_db->GetRows("SELECT `players_in_games`.`pig_player_status` FROM `players_in_games` WHERE `players_in_games`.`pig_player` = '$player' AND  `players_in_games`.`pig_player_status` != 'alive'");
			if (is_null($rank))
				return (null);

			// Calcul du classement du joueur pour chaque partie
			foreach ($rank as $r) {
				if ($r['pig_player_status'] != 'giveup')
					$stats[$r['pig_player_status']]++;
			}

			if ($stats['winner'] > 0) {
				$st .= '{"value": ' . $stats['winner'] . ',"color":"#4d84a0"}';
				$legende .= '<span style="background-color:#4d84a0;">&nbsp;</span> Premier ';
			}
			if ($stats['two'] > 0) {
				$st .= (strlen($st) <= 1) ? '' : ',';
				$st .= '{"value": ' . $stats['two'] . ',"color":"#97bbcd"}';
				$legende .= '<span style="background-color:#97bbcd;">&nbsp;</span> Second ';
			}
			if ($stats['three'] > 0) {
				$st .= (strlen($st) <= 1) ? '' : ',';
				$st .= '{"value": ' . $stats['three'] . ',"color":"#c5d9e3"}';
				$legende .= '<span style="background-color:#c5d9e3;">&nbsp;</span> Troisi&egrave;me ';
			}
			if ($stats['four'] > 0) {
				$st .= (strlen($st) <= 1) ? '' : ',';
				$st .= '{"value": ' . $stats['four'] . ',"color":"#ededed"}';
				$legende .= '<span style="background-color:#ededed;">&nbsp;</span> Quatri&egrave;me ';
			}

			$st .= ']';

			$res = array('values' => $st, 'legend' => $legende);

			// Cache data
			$this->_cache->set(('RankingPiePlayer' . $player), $res , $this->_cacheExpirationTime);
		}
		
		/**
		*	Update Player favorite color pie
		*	
		*	@param: {Int} 	$player 	ID of the player to update
		*	@return: 
		*/
		private function 	updatePlayerFavoriteColorPie($player) {
			$st = '[';

			$colors = $this->_db->GetRows("SELECT `players_in_games`.`pig_color` AS `Color`, COUNT(`players_in_games`.`pig_color`) AS `Nb` FROM `players_in_games` WHERE `players_in_games`.`pig_player_status` != 'alive' AND `players_in_games`.`pig_player` = '$player' GROUP BY `players_in_games`.`pig_color`");
			if (is_null($colors))
				return (null);

			// Calcul du classement du joueur pour chaque partie
			foreach ($colors as $c) {
				if (strlen($st) > 1)
					$st .= ',';
				$st .= '{"value": ' . $c['Nb'] . ', "color":"' . $c['Color'] . '"}';
			}
			$st .= ']';

			// Cache data
			$this->_cache->set(('FavoriteColorsPlayer' . $player), $st , $this->_cacheExpirationTime);
		}

		/**
		*	Update last 10 player game evolution and save the result in cache
		*	
		*	@param: {Int} 	$player 	ID of the player to update
		*	@return:
		*/
		private function 	updatePlayerRankingEvolution($player) {
			$totalGames = 0;
			$nbVictories = 0;
			$ret = array('values' => '', 'label' => '');

			// Recuperation des classement du joueur sur les 10 denieres parties
			$rank = $this->_db->GetRows("SELECT `players_in_games`.`pig_player` , `players_in_games`.`pig_player_status`
											FROM `games`
											JOIN `players_in_games` ON `games`.`game_id` = `players_in_games`.`pig_game`
											WHERE `games`.`game_status` = 'ended' AND `players_in_games`.`pig_player` = '$player'
											ORDER BY `games`.`game_end_date` ASC");
			
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
							$ret['label'] .= ($ret['label'] === '') ? '"Victoire"' : ',"Victoire"';
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

			$ret['values'] = '[' . $ret['values'] . ']';
			$ret['label'] =  '[' . $ret['label'] . ']';

			// Cache data
			$this->_cache->set(('RankingEvolutionPlayer' . $player), $ret , $this->_cacheExpirationTime);
		}

		/**
		*	Update ranking table
		*	
		*	@param: {Int} 	$player 	ID of the player to update
		*	@return: 
		*/
		private function 	updateRankingTable() {
			$rqtbl = array();
			$pos = 1;

			// Step 1: get ranking table
			$rank = $this->_db->GetRows("SELECT `players`.`player_nick` AS `Nick`, `players`.`player_score` AS `Score`, `players`.`player_id` AS `ID` FROM `players` ORDER BY `players`.`player_score` DESC");

			// Step 2: Get start period date
			$startCurrentPeriod = $this->_db->GetRows("SELECT UNIX_TIMESTAMP(`game_periods`.`gp_start_date`) AS `Start` FROM `game_periods` ORDER BY `game_periods`.`gp_start_date` DESC LIMIT 0, 1");
			
			if (is_null($rank) || is_null($startCurrentPeriod))
				return;
			$startCurrentPeriod = $startCurrentPeriod[0]['Start'];

			// Step 3: Get extra info for each player
			foreach ($rank as &$r) {
				$pID = $r['ID'];
				
				// Retreive number of games played in this period
				$query = "SELECT COUNT(`games`.`game_id`) AS `NB`
							FROM `games`
							JOIN `players_in_games`
							ON `players_in_games`.`pig_game` = `games`.`game_id`
							WHERE `players_in_games`.`pig_player` = '$pID'
							AND `games`.`game_status` = 'ended'
							AND UNIX_TIMESTAMP(`games`.`game_start_date`) > $startCurrentPeriod";
				$nbGamesEnded = $this->_db->GetRows($query);

				// Retreive number of current games
				$query = "SELECT COUNT(`games`.`game_id`) AS `NB`
							FROM `games`
							JOIN `players_in_games`
							ON `players_in_games`.`pig_game` = `games`.`game_id`
							WHERE `players_in_games`.`pig_player` = '$pID'
							AND `games`.`game_status` = 'pending'";
				$nbGamesPending = $this->_db->GetRows($query);

				if (is_null($nbGamesEnded) || is_null($nbGamesPending))
					return;

				// Adding player if he is playing during this period
				if ($nbGamesEnded[0]['NB'] != '0' || $nbGamesPending[0]['NB'] != '0')
					$rqtbl[] = array('nick' => $r['Nick'], 'id' => $r['ID'], 'pos' => $pos++, 'score' => $r['Score'], 'nbGamesEnded' => $nbGamesEnded[0]['NB'], 'nbGamesPending' => $nbGamesPending[0]['NB']);
			}

			// Cache ranking table
			$this->_cache->set('RankingTable', $rqtbl, $this->_cacheExpirationTime);
		}

		/**
		*	Update global ranking table
		*	
		*	@param:
		*	@return: 
		*/
		private function 	updateGlobalRankingTable() {
			$rqtbl = array();
			$pos = 1;

			// Step 1: get ranking table
			$rank = $this->_db->GetRows("SELECT `players`.`player_nick` AS `Nick`, `players`.`player_global_score` AS `Score`, `players`.`player_id` AS `ID` FROM `players` ORDER BY `players`.`player_global_score` DESC");

			if (is_null($rank))
				return;
			
			// Step 3: Get extra info for each player
			foreach ($rank as &$r) {
				$pID = $r['ID'];
				
				// Retreive number of games played in this period
				$query = "SELECT COUNT(`games`.`game_id`) AS `NB`
							FROM `games`
							JOIN `players_in_games`
							ON `players_in_games`.`pig_game` = `games`.`game_id`
							WHERE `players_in_games`.`pig_player` = '$pID'
							AND `games`.`game_status` = 'ended'";
				$nbGamesPlayed = $this->_db->GetRows($query);

				// Retreive number of current games
				$query = "SELECT COUNT(`players_in_games`.`pig_game`) AS `NB`
							FROM `players_in_games`
							WHERE `players_in_games`.`pig_player` = '$pID'
							AND `players_in_games`.`pig_player_status` = 'winner'";
				$nbGamesWin = $this->_db->GetRows($query);

				if (is_null($nbGamesPlayed) || is_null($nbGamesWin))
					return;

				// Adding player if he is playing at least once
				if ($nbGamesPlayed[0]['NB'] != '0')
					$rqtbl[] = array('nick' => $r['Nick'], 'id' => $r['ID'], 'pos' => $pos++, 'score' => $r['Score'], 'nbGamesEnded' => $nbGamesPlayed[0]['NB'], 'nbGamesWin' => $nbGamesWin[0]['NB']);
			}

			// Cache ranking table
			$this->_cache->set('GlobalRankingTable', $rqtbl, $this->_cacheExpirationTime);
		}

	}
?>