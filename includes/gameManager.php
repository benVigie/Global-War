<?php

	/**
	* 	GameManager fait tourner le jeu. Il s'occupe de la création, du déroulement et de tout ce qui touche aux mécaniques de jeu.
	* 	C'est le coeur du projet !
	*
	*	Public Methods:
	*		__construct
	*		CreateNewGame
	*		LoadGame
	*		GetPlayers
	*		GetCurrentPlayer
	*		GetLastAction
	*		GetRenforcementNumber
	*		GetUnitsNumber
	*		Attack
	*		CheckTerritoryOwner
	*		UpdateTerritoryUnities
	*		Move
	*		UpdateRenforcement
	*		GetRenforcementLeft
	*		GetPlaceUnitsNumber
	*		EndTurn
	*
	*	Private Methods:
	*		getTerritoryOwner
	*		setRenforcementNumber
	*		getBonusContinent
	*/ 
	class GameManager {

		/*---------------
		|	Propriétés 	|
		---------------*/
		private $_db;
		private $_gameID;


		/*---------------
		|	Methodes 	|
		---------------*/
		/**
		* Constructeur
		* 
		* @param $bdd_ressource => une reference sur la ressource MySQL ouverte
		* @param $id => identifiant du jeu à manipuler (si null, on veut créer un nouveau jeu)
		* @return void 
		*/
		public function 	__construct($bdd_ressource, $id = null) {
			$this->_db = $bdd_ressource;
			$this->_gameID = $id;
		}


		/**
		* Cree une nouvelle partie en BDD. Genere une map, place les joueurs et envoie un mail au premier joueur !
		* 
		* @param {Array} $players => Tableaux des joueurs pour cette partie
		* @return {Int} L'id de la partie nouvellement cree
		*/
		public function 	CreateNewGame($players) {
			
			$colors = $this->GetAvailablesColors();

			// 1 - On melange les joueurs
			shuffle($players);
			$nb_players = count($players);
			$player = $firstOne = $players[0];

			// 2 - On cree le jeu en BDD et on recupere son id
			if (!$this->_db->Execute("INSERT INTO `games` (`game_id`, `game_status`, `game_nb_players`, `game_current_player`, `game_start_date`, `game_end_date`) VALUES (NULL, 'pending', '$nb_players', '$player', CURRENT_TIMESTAMP, '0000-00-00 00:00:00');"))
				return ('Erreur lors de la cr&eacute;ation du jeu (0)');
			$game_id = mysql_insert_id();
			$this->_gameID = $game_id;

			// 3 - On insere les joueurs dans le jeu
			for ($i = 0; $i < count($players); $i++) {
				$place = $i + 1;
				$player = $players[$i];
				if (!$this->_db->Execute("INSERT INTO `players_in_games` (`pig_game`, `pig_player`, `pig_player_status`, `pig_order`, `pig_bonus`, `pig_color`) VALUES ('$game_id', '$player', 'alive', '$place', '0', '$colors[$i]')"))
					return ('Erreur lors de la cr&eacute;ation du jeu (1)');
			}

			// 4 - On cree un tableau de case, on le melange
			$board = range(0, 41);
			shuffle($board);

			// 5 - On creer la map en BDD, en attribuant 3 personnes / case / joueur
			for ($i = 0; $i < 42; $i++) {
				// On passe les joueurs dans l'ordre les uns apres les autres
				$player = $players[($i % $nb_players)];
				$case = $board[$i];

				// Cas special - Dans une partie a 2 joueurs, 8 cases restent neutres
				if ($nb_players === 2 && $i > 27) // Si on est dans les 14 derniere cases d'une partie a 2, player == 0 == neutre
					$player = 0;
				// Cas special 2 - Dans une partie a 4 joueurs, 2 cases restent neutres
				if ($nb_players === 4 && $i > 39) // Si on est dans les 14 derniere cases d'une partie a 2, player == 0 == neutre
					$player = 0;

				if (!$this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$game_id', '$player', '$case', '3', CURRENT_TIMESTAMP);"))
					return ('Erreur lors de la cr&eacute;ation du jeu (2)');
			}

			// 6 - On prepare la partie pour le premier joueur
			$this->setRenforcementNumber($firstOne);

			// 7 - On envoie un mail aux joueurs concernes
			foreach ($players as $p) {
				if ($p != $_SESSION['user-id']) {
					$this->sendPlayerNotification($p, array('type' => 'newGame',
															'creator' => $_SESSION['user-nick'], 
															'gameID' => $game_id,
															'youStart' => (($p == $firstOne) ? true : false)));
				}
			}
			// TODO:	- checker si le joueur 1 est le joueur courant. Dans ce cas pas de mail
			// 			- else: envoyer un mail
			return ($game_id);
		}

		/**
		*	Charge l'etat d'une map a un instant t et retourne cette map sous forme de tableau 
		*	
		*	@param: $timestamp => le timestamp max ou on doit recup
		*	@return: Array La map genre [[ board_place, board_player, board_units ], ...]
		*/
		public function 	LoadGame($timestamp = null) {
			$res = array();

			$limit = ($timestamp === null) ? 'NOW()' : "'" . $timestamp . "'";
			$query = "SELECT `Join`.`board_place`, `Join`.`board_player`, `Join`.`board_units`
						FROM (SELECT *
						FROM `boards`
						WHERE `boards`.`board_date` <= $limit
						AND `boards`.`board_game` = '$this->_gameID' 
						ORDER BY `boards`.`board_id` DESC) AS `Join`
						GROUP BY `Join`.`board_place`";

			$map = $this->_db->GetRows($query);
			for ($i = 0; $i < count($map); $i++) {
				$res[$i] = array();
				$res[$i]['place'] = $map[$i]['board_place'];
				$res[$i]['player'] = $map[$i]['board_player'];
				$res[$i]['units'] = $map[$i]['board_units'];
			}
			return ($res);
		}

		/**
		*	Recupere la liste des joueurs de la partie
		*	
		*	@return: Array La liste des joueurs avec des infos de base (pseudo, couleur, id, statut, photo...)
		*/
		public function 	GetPlayers($onlyAlive = false) {
			$res = array();

			$query = "SELECT * FROM `players_in_games` JOIN `players` ON `players`.`player_id` = `players_in_games`.`pig_player` WHERE `players_in_games`.`pig_game` = '$this->_gameID'";
			
			// Si on ne veut recuperer que les joueurs VIVANTS
			if ($onlyAlive)
				$query .=" AND `players_in_games`.`pig_player_status` = 'alive'";
				
			$query .=" ORDER BY `players_in_games`.`pig_order` ASC";

			$players = $this->_db->GetRows($query);
			for ($i = 0; $i < count($players); $i++) {
				$res[$i] = array();
				$res[$i]['id'] = $players[$i]['pig_player'];
				$res[$i]['nick'] = $players[$i]['player_nick'];
				$res[$i]['status'] = ($players[$i]['pig_player_status'] === 'alive') ? 1 : 0;
				$res[$i]['color'] = $players[$i]['pig_color'];
				if (file_exists('../images/users/' . $res[$i]['id'] . '.jpg'))
					$res[$i]['pic'] = 'images/users/' . $res[$i]['id'] . '.jpg';
				else
					$res[$i]['pic'] = 'images/users/noset.jpg';
			}

			return ($res);
		}

		/**
		*	Recupere l'id du joueur courant
		*
		*	@return: Int ID du joueur courant
		*/
		public function 	GetCurrentPlayer() {
			$player = $this->_db->GetRows("SELECT `games`.`game_current_player` FROM `games` WHERE `games`.`game_id` = '$this->_gameID'");
			if (is_null($player) || is_null($player[0]))
				return (null);
			return (intval($player[0]['game_current_player']));
		}

		/**
		*	Recupere tous les coups joues apres le dernier tour du joueur
		* 
		*	@param 	Int $player l'ID du joueur 
		*	@return Array Un tableaux des differents coups dans l'ordre chronologique
		*/
		public function 	GetLastAction($player) {
			// Requete 1:  Recuperation de l'heure du dernier coup du joueur
			$lastAction = $this->_db->GetRows("SELECT `strokes`.`stroke_date` FROM `strokes` WHERE `strokes`.`stroke_game` = '$this->_gameID' AND `strokes`.`stroke_player` = '$player' ORDER BY `strokes`.`stroke_id` DESC LIMIT 0, 1");

			// requete 2: On recupere tous les coups joue de la partie...
			$query = "SELECT * FROM `strokes` WHERE `strokes`.`stroke_game` = '$this->_gameID' ";

			// ... Si il a deja joue, on recupere tous les coups APRES son dernier ...
			if (!is_null($lastAction)) {
				$last = $lastAction[0]['stroke_date'];
				$query .= "AND `strokes`.`stroke_date` > '$last' ";
			}

			// ... Pour finir on range la requete par date croissante
			$query .= "ORDER BY `strokes`.`stroke_id` ASC";
			$actions = $this->_db->GetRows($query);

			return ($actions);
		}

		/**
		*	Retourne le nombre de renforts que possède actuellement le joueur
		*
		*	@param: {Int} $player Id du joueur a checker
		*	@return: {Int} Nombre de renforts actuels du joueur
		*/
		public function 	GetRenforcementNumber($player) {
			$query = 	"SELECT * FROM (
							SELECT * FROM (
								SELECT `boards`.`board_place`, `boards`.`board_player`
								FROM  `boards` 
								WHERE  `boards`.`board_game` = '$this->_gameID' 
								ORDER BY  `boards`.`board_id` DESC) AS `LATEST`
							GROUP BY `LATEST`.`board_place`) AS `CURRENT_BOARD`
						WHERE `CURRENT_BOARD`.`board_player` = '$player'";
			
			$positions = $this->_db->GetRows($query);
			if (is_null($positions))
				return (0);

			// La regle: total de territoire / 3
			$renfNumber = floor(count($positions) / 3);

			// 3 minimum. Parce que si on en est la, c'est deja assez triste :)
			if ($renfNumber < 3)
				$renfNumber = 3;
			
			// Check si un joueur possede un continent entier et rajouter le bonus
			$renfNumber += $this->getBonusContinent($positions);

			return ($renfNumber);
		}

		/**
		*	Retourne le nombre d'unites du joueur
		*
		*	@param: {Int} $player le joueur pour lequel on demande
		*	@return: {Int} nombre d'unites presentes sur la carte appartenant au joueur
		*/
		public function 	GetUnitsNumber($player) {
			$totalUnits = 0;

			$query = 	"SELECT * FROM (
							SELECT * FROM (
								SELECT `boards`.`board_place`, `boards`.`board_units`, `boards`.`board_player`
								FROM  `boards` 
								WHERE  `boards`.`board_game` = '$this->_gameID' 
								ORDER BY  `boards`.`board_id` DESC) AS `LATEST`
							GROUP BY `LATEST`.`board_place`) AS `CURRENT_BOARD`
						WHERE `CURRENT_BOARD`.`board_player` = '$player'";
			
			$units = $this->_db->GetRows($query);
			if (is_null($units))
				return (0);

			foreach ($units as $u) {
				$totalUnits += $u['board_units'];
			}
			
			return ($totalUnits);
		}

		/**
		*	Le joueur $player attaque le territoire $Dplace
		*
		*	@param Int $player Id du joueur qui attaque
		*	@param Int $Aplace Id du territoire attaquant
		*	@param Int $Dplace Id du territoire attaqué
		*	@return 
		*/
		public function 	Attack($player, $Aplace, $Dplace) {
			$nbA;
			$nbD;
			$totalUnitsA;
			$totalUnitsD;
			$scoreA = array();
			$scoreD = array();
			$attackWinTerritory = false;
			$endGame = false;

			// On récupére l'ID de l'ennemi pour updater le board
			$ennemyID = $this->getTerritoryOwner($Dplace);
			
			// D'abord, on verifie que le territoire attaquant appartient bien au gus
			if (!$this->CheckTerritoryOwner($player, $Aplace))
				return (array('error' => 'Wait... Qui fait quoi la ???'));
			// Et aussi qu'il s'attaque pas lui meme
			if ($this->CheckTerritoryOwner($player, $Dplace))
				return (array('error' => "Ce territoire vous appartient déjà."));

			// Ensuite on check que les territoires sont bien voisins
			// TODO
			
			// On récupère le nombre d'unités engagés
			$totalUnitsA = $this->GetPlaceUnitsNumber($Aplace);
			$totalUnitsD = $this->GetPlaceUnitsNumber($Dplace);
			$nbA = min($totalUnitsA - 1, 3);
			$nbD = ($totalUnitsD >= 2) ? 2 : 1;

			// Si on essaye de nous la faire, on essaye pas :)
			if ($nbA < 1)
				return (array('error' => "Pas assez d'unitées pour attaquer"));

			// On lance les dés en attaque
			for ($i = 0; $i < $nbA; $i++)
				$scoreA[] = rand(1, 6);
			rsort($scoreA);
			// ... La même en défense
			for ($i = 0; $i < $nbD; $i++)
				$scoreD[] = rand(1, 6);
			rsort($scoreD);

			// print_r($scoreA);
			// echo('<br/><br/><br/>');
			// print_r($scoreD);

			// On note le score dans $nbA et $nbD
			$nbA = $nbD = 0;

			$rounds = min(count($scoreA), count($scoreD));
			// Update des unites sur le terrain
			for ($i = 0; $i < $rounds; $i++) {
				if ($scoreA[$i] > $scoreD[$i]) {
					$nbA++;
					$totalUnitsD--;
				}
				else {
					$nbD++;
					$totalUnitsA--;
				}
			}
			// echo('<br/><br/>Attaque: ' . $nbA . ' - ' . $nbD . ' Defense<br/>');
			
			// Soit les 2 camps on encore des unites presentes. Dans ce cas on insert le nouvel etat des 2 cases
			if ($totalUnitsD > 0) {
				// update la place attaquante
				$this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$player', '$Aplace', '$totalUnitsA', CURRENT_TIMESTAMP)");
				// ... puis de la place attaquée
				$this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$ennemyID', '$Dplace', '$totalUnitsD', CURRENT_TIMESTAMP)");

				$attackWinTerritory = false;
			}
			// Soit le territoire attaqué est battu. Dans ce cas on calcul le minimum a mettre sur ce territoire et on update les 2
			else {
				// Calcul des deploiment inherent a l'attaque
				$totalUnitsD = $totalUnitsA - 1;
				$totalUnitsA = 1;

				// Insertion en BDD
				$this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$player', '$Aplace', '$totalUnitsA', CURRENT_TIMESTAMP)");
				$this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$player', '$Dplace', '$totalUnitsD', CURRENT_TIMESTAMP)");

				// On incrémente le bonus du joueur (+1 par territoire gagné)
				$this->_db->Execute("UPDATE `players_in_games` SET `pig_bonus` = `pig_bonus` + 1 WHERE `pig_game` = '$this->_gameID' AND `pig_player` = '$player'");

				// On précise qu'il prend le territoire
				$attackWinTerritory = true;
				
				// On vérifie que la partie n'est pas terminée (aka que les 2 joueurs ont au moins un territoire)
				$endGame = $this->checkEndGame($ennemyID);
			}

			// On insère la partie, le joueur, les places et le score de l'ataque puis de la defense dans les coups joues
			$this->_db->Execute("INSERT INTO `strokes` (`stroke_id`, `stroke_game`, `stroke_player`, `stroke_type`, `stroke_board_src`, `stroke_board_dest`, `stroke_value_src`, `stroke_value_dest`, `stroke_infos_src`, `stroke_infos_dest`, `stroke_date`) 
				VALUES (NULL, '$this->_gameID', '$player', 'attack', '$Aplace', '$Dplace', '$nbA', '$nbD', '" . implode('-', $scoreA) . "', '" . implode('-', $scoreD) . "', CURRENT_TIMESTAMP)");


			// Preparation de la reponse
			$response = array(	'rollsA' => $scoreA, 
								'rollsD' => $scoreD, 
								'Aplace' => $Aplace, 
								'Dplace' => $Dplace, 
								'AplaceUnits' => $totalUnitsA, 
								'DplaceUnits' => $totalUnitsD, 
								'AttackHasWin' => $attackWinTerritory, 
								'endGame' => $endGame, 
								'player' => array(	'id' => $player,
													'units' => $this->GetUnitsNumber($player), 
													'renforcements' => $this->GetRenforcementNumber($player)),
								'ennemy' => array(	'id' => $ennemyID,
													'units' => $this->GetUnitsNumber($ennemyID), 
													'renforcements' => $this->GetRenforcementNumber($ennemyID)),
								);
			return ($response);
		}

		/**
		*	Check si le joueur donne a bien le territoire donne
		*
		*	@param Int $player Id du joueur
		*	@param Int $place Id du territoire a verifier
		*	@return Boolean True si le territoire lui appartient, false sinon
		*/
		public function 	CheckTerritoryOwner($player, $place) {
			$pl = $this->_db->GetRows("SELECT `boards`.`board_player` FROM `boards` WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_place` = '$place' ORDER BY `boards`.`board_id` DESC LIMIT 0, 1");

			if (is_null($pl))
				return (null);

			if ($pl[0]['board_player'] == $player)
				return (true);
			return (false);
		}

		/**
		*	Update le nombre d'unites pour le territoire donne de $nb unites.
		*		/!\ Checker avant que le territoire est a la bonne personne... 
		*
		*	@param Int $place Id du territoire a verifier
		*	@param Int $nb Nombre d'unite a ajouter (peut etre negatif pour enlever)
		*	@return Boolean True si l'update a fonctionne, false sinon
		*/
		public function 	UpdateTerritoryUnities($place, $nb) {
			// Recuperation du nombre d'unites sur ce territoire
			$unitsNumber = $this->_db->GetRows("SELECT `boards`.`board_units`, `boards`.`board_player` FROM `boards` WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_place` = '$place' ORDER BY `boards`.`board_id` DESC LIMIT 0,1");

			if (is_null($unitsNumber))
				return (false);

			$nbOrigin = intval($unitsNumber[0]['board_units']);
			$nb += $nbOrigin;
			$player = $unitsNumber[0]['board_player'];

			// Update BDD
			//  - Pour commencer, on Update (Ou INSERT un nouveau ?) BOARD 
			// $updateBoard = $this->_db->Execute("UPDATE `boards` SET `boards`.`board_units` = '$nb' WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_place` = '$place'");
			$updateBoard = $this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$player', '$place', '$nb', CURRENT_TIMESTAMP)");
			
			//  - Puis on enregistre un nouveau coup pour les replays
			$newStroke = $this->_db->Execute("INSERT INTO `strokes` (`stroke_id`, `stroke_game`, `stroke_player`, `stroke_type`, `stroke_board_src`, `stroke_board_dest`, `stroke_value_src`, `stroke_value_dest`, `stroke_date`) VALUES (NULL, '$this->_gameID', '$player', 'renforcement', '$place', '$place', '$nbOrigin', '$nb', CURRENT_TIMESTAMP)");
			
			if ($updateBoard && $newStroke)
				return (true);

			return (false);
		}

		/**
		*	Opère un déplacement d'unités d'un territoire A vers une territoire B
		*
		*	@param Int $player Id du joueur
		*	@param Int $A Id du territoire source
		*	@param Int $B Id du territoire de destination
		*	@param Int $nbA Nombre d'unite a placer sur le territoire source
		*	@param Int $nbB Nombre d'unite a placer sur le territoire destination
		*	@return Boolean true si tout est bon, false si une couille dans le paté
		*/
		public function 	Move($player, $A, $B, $nbA, $nbB) {
			if (!$this->CheckTerritoryOwner($player, $A) || !$this->CheckTerritoryOwner($player, $B))
				return (false);
				// echo ('probleme 1');

			$total = $this->GetPlaceUnitsNumber($A);
			$total += $this->GetPlaceUnitsNumber($B);

			if ($total !== ($nbA + $nbB)) {
				return (false);
			}
				// echo ($total . '!== ' .$nbA. ' + ' . $nbB);

			// TODO: selon l'état du jeu, laisser au moins 1 ou 3 unités sur le terrain B

			// On insère le coup
			$newStroke = $this->_db->Execute("INSERT INTO `strokes` (`stroke_id`, `stroke_game`, `stroke_player`, `stroke_type`, `stroke_board_src`, `stroke_board_dest`, `stroke_value_src`, `stroke_value_dest`, `stroke_date`) VALUES ('', '$this->_gameID', '$player', 'move', $A, '$B', '$nbA', '$nbB', CURRENT_TIMESTAMP)");

			// Update les emplacements
			$updA = $this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$player', '$A', '$nbA', CURRENT_TIMESTAMP)");
			$updB = $this->_db->Execute("INSERT INTO `boards` (`board_id`, `board_game`, `board_player`, `board_place`, `board_units`, `board_date`) VALUES (NULL, '$this->_gameID', '$player', '$B', '$nbB', CURRENT_TIMESTAMP)");

			if ($newStroke && $updA && $updB)
				return (true);
			return (false);
		}

		/**
		*	Update le nombre de renforts du joueur. Concretement, c'est un add ou remove sur un territoire
		*		/!\ Checker avant que le territoire est a la bonne personne... 
		*
		*	@param Int $player Id du joueur a updater
		*	@param Int $place Id du territoire a updater
		*	@param Int $nb Nombre d'unite a ajouter (generallement 1 ou -1)
		*	@return String message d'erreur, ou TRUE si reussit
		*/
		public function 	UpdateRenforcement($player, $place, $nb) {
			// Recuperation du nombre d'unites sur ce territoire
			$infos = $this->_db->GetRows("SELECT `players_in_games`.`pig_renf_max`, `players_in_games`.`pig_renf_number` FROM `players_in_games` WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'");
			
			if (is_null($infos))
				return ("Impossible de recuperer les infos en BDD");
			
			$max = $infos[0]['pig_renf_max'];
			$unitsLeft = $infos[0]['pig_renf_number'];

			// Dans le cas ou on ajoute un renfort sur un territoire
			if ($nb > 0) {
				if (--$unitsLeft < 0) // Si on n'a plus de renforts mais qu'on essaye quand meme...
					return ('Vieux tricheur...');
				
				// Si la dernière unitée vient d'être attribuée, et si les bonus n'ont pas été consommés, on offre une troupe en cadeau pour le prochain tour
				if ($unitsLeft == 0) {
					if ($this->GetNbBonusTroops($player) > 0)
						$this->_db->Execute("UPDATE `players_in_games` SET `pig_bonus` = `pig_bonus` + 1 WHERE `pig_game` = '$this->_gameID' AND `pig_player` = '$player'");
				}

				// Si l'update fonctionne, on decremente les renforts restants en BDD
				if ($this->UpdateTerritoryUnities($place, $nb))
					return ($this->_db->Execute("UPDATE `players_in_games` SET `players_in_games`.`pig_renf_number` = `players_in_games`.`pig_renf_number` - 1 WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'"));
			}
			// TODO: rajouter l'annulation (aka $nb == -1)
		}

		/**
		*	Recupere le nombre de renforts pour le tour courant (en DB)
		*
		*	@param Int $player Id du joueur a updater
		*	@return Int nombre de renforts 
		*/
		public function 	GetRenforcementLeft($player) {
			// Recuperation du nombre d'unites sur ce territoire
			$infos = $this->_db->GetRows("SELECT `players_in_games`.`pig_renf_number` FROM `players_in_games` WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'");
			if (is_null($infos))
				return ("Impossible de recuperer les infos en BDD");
			$unitsLeft = $infos[0]['pig_renf_number'];

			return (intval($unitsLeft));
		}

		/**
		*	Recupere le nombre de troupes bonus du joueur
		*
		*	@param Int $player Id du joueur a updater
		*	@return Int nombre de troupes en bonus disponibles
		*/
		public function 	GetNbBonusTroops($player) {
			// Recuperation du nombre d'unites sur ce territoire
			$infos = $this->_db->GetRows("SELECT `players_in_games`.`pig_bonus` FROM `players_in_games` WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'");
			
			if (is_null($infos))
				return ("Impossible de recuperer les infos en BDD");
			$bonus = $infos[0]['pig_bonus'];

			return (intval($bonus));
		}

		/**
		*	Retourne le nombre d'unitées présente actuellement sur le territoire $place
		*
		*	@param Int $place Id du territoire
		*	@return Int Nombre d'unitees présentes sur le territoire
		*/
		public function GetPlaceUnitsNumber($place) {
			$unitsNumber = $this->_db->GetRows("SELECT `boards`.`board_units` FROM `boards` WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_place` = '$place' ORDER BY `boards`.`board_id` DESC LIMIT 0,1");

			if (is_null($unitsNumber))
				return (0);

			return (intval($unitsNumber[0]['board_units']));
		}

		/**
		*	Fini le tour et passe la main au joueur suivant
		*
		*	@param {Int} $player L'id du joueur courant
		*	@return {Boolean} True si le changement de joueur est effectif, false sinon
		*/
		public function EndTurn($player) {
			$order = $this->_db->GetRows("SELECT `players_in_games`.`pig_player` FROM `players_in_games` WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player_status` = 'alive' ORDER BY `players_in_games`.`pig_order` ASC");
			
			if (is_null($order))
				return (false);

			$current = $this->_db->GetRows("SELECT `games`.`game_current_player` FROM `games` WHERE `games`.`game_id` = '$this->_gameID'");
			if (is_null($current[0]) || $current[0]['game_current_player'] != $player)
				return (false);

			$nextOne = false;
			$id = 0;
			for ($i = 0; $i < count($order); $i++) { 
				// On recupere l'id courant
				$id = $order[$i]['pig_player'];
				
				// Si c'est notre homme
				if ($nextOne) {
					// On garde l'id du joueur suivant
					$nextPlayer = $id;
					break;
				}
				// Sinon si on est sur le joueur courant, on se met un petit recap pour le futur
				if ($id === $player)
					$nextOne = true;

				// Pour boucler (au cas ou on atteint la fin du tableau alors que le joueur suivant est au debut de celui-ci)
				if ($i === (count($order)) - 1)
					$i = -1;
			}

			if (!isset($nextPlayer))
				return (false);

			// On prepare les renforts du joueur suivant
			$this->setRenforcementNumber($nextPlayer);

			// On envoye un email au nouveau joueur concerne
			$this->sendPlayerNotification($nextPlayer, array('type' => 'yourTurn',
																'gameID' => $this->_gameID));

			return ($this->_db->Execute("UPDATE `games` SET `game_current_player` = '$nextPlayer' WHERE `games`.`game_id` = '$this->_gameID'"));
		}


		/**
		*	Retourne un tableau de toute les couleurs disponibles pour ce jeu (== non prises par les autres joueurs)
		*
		*	@return {Array} Un tableau de String contenant les codes couleurs
		*/
		public function GetAvailablesColors() {
			$colors = array('#da0e00','#176bce','#851de3','#08b207','#F3602C');
			$availables = array();
			$available = true;

			$taken = $this->_db->GetRows("SELECT `players_in_games`.`pig_color` FROM `players_in_games` WHERE `players_in_games`.`pig_game` = '$this->_gameID'");
			if (is_null($taken))
				return ($colors);
			
			foreach ($colors as $color) {
				$available = true;
				foreach ($taken as $user) {
					if ($color == $user['pig_color'])
						$available = false;
				}
				if ($available)
					$availables[] = $color;
			}

			return ($availables);
		}

		/**
		*	Appele lorsqu'un joueur abandonne une partie.
		*
		*	@param {Int} ID du joueur qui abandonne
		*	@return {Boolean} True si le joueur a quitte gratuitement, False s'il y a laisse des plumes
		*/
		public function 	PlayerStopDemand($playerID) {
			$hasPlayed;
			$otherPlayer;

			// Checker si le joueur a joué ou non dans cette partie
			$nbPlayerStrokes = $this->_db->GetRows("SELECT COUNT(`strokes`.`stroke_id`) AS `Strokes` FROM `strokes` WHERE `strokes`.`stroke_game` = '$this->_gameID' AND `strokes`.`stroke_player` = '$playerID'");
			$hasPlayed = ($nbPlayerStrokes[0]['Strokes'] == '0') ? false : true;

			// Recupere le nombre de joueur en lice
			$nbPlayersLeft = count($this->GetPlayers(true)) - 1;

			// Si le lacheur c'est son tour de jouer, il faut donner la main (enfin, que si il reste au moins 2 joueurs en lice...)
			if ($nbPlayersLeft > 1)
				$this->EndTurn($playerID);

			// Si le joueur qui abandonne n'a jamais joue de coup (== refuser une partie)
			if ($hasPlayed === false) {
				// Clore le jeu pour le joueur SANS perte de points
				$this->playerDontWantToPlayThisGame($playerID);

				// Si il ne reste qu'une personne, on clos le jeu sans gagnant proprement
				if ($nbPlayersLeft <= 1) {
					// Recuperer l'id de l'autre joueur et le virer du jeu
					$otherPlayer = $this->GetPlayers(true);
					$otherPlayer = ($otherPlayer[0]['id'] == $playerID) ? $otherPlayer[1]['id'] : $otherPlayer[0]['id'];

					$this->playerDontWantToPlayThisGame($otherPlayer);

					// Adios le jeu
					$this->endThisGameInDB();
				}

				return (true);
			}
			// Si ce coquin abandonne lachement la partie
			else {
				// Desole mec, tu perd des points !
				$this->playerStopGame($playerID);

				// Si il ne reste qu'une personne, on clos le jeu en designant le dernier adversaire gagnant
				if ($nbPlayersLeft <= 1) {
					// Recuperer l'id de l'autre joueur et le virer du jeu
					$otherPlayer = $this->GetPlayers(true);
					$otherPlayer = ($otherPlayer[0]['id'] == $playerID) ? $otherPlayer[1]['id'] : $otherPlayer[0]['id'];
					
					$this->closeGameForPlayer($otherPlayer);

					// Adios le jeu
					$this->endThisGameInDB();
				}
			}

			return (false);

		}

		/**
		*	Change la couleur d'un joueur dans la partie
		*
		*	@param {Int} $playerID L'ID du joueur
		*	@param {String} $color La couleur choisie
		*	@return {Boolean} True si la couleur a été changée, False si un probléme est survenu 
		*/
		public function 	UpdatePlayerColor($playerID, $color) {
			// TODO: checker si la couleur existe dans la liste des couleurs authorisées

			return ($this->_db->Execute("UPDATE `players_in_games` SET `pig_color` = '$color' WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$playerID'"));
		}

		/**
		*	Lorsqu'un joueur veut utiliser son bonus de troupes, c'est ici que ça se passe !
		*
		*	@param {Int} $playerID L'ID du joueur
		*	@return {Int} Le nombre d'unités disponibles après utilisation du bonus 
		*/
		public function 	GiveMeMyBonus($playerID) {
			// Incremente en BDD le nombre de renforts maximum ainsi que le nombre de renforts dispos et set le bonus à 0
			if (!$this->_db->Execute("UPDATE `players_in_games` SET 
							`pig_renf_max` = `pig_renf_max` + `pig_bonus`, 
							`pig_renf_number` = `pig_renf_number` + `pig_bonus`, 
							`pig_bonus` = '0' 
							WHERE `pig_game` = '$this->_gameID' AND `pig_player` = '$playerID'"))
				return (-1);

			$nbRenf = $this->_db->GetRows("SELECT `pig_renf_number` AS `NB` FROM `players_in_games` WHERE `pig_game` = '$this->_gameID' AND `pig_player` = '$playerID'");
			
			if (is_null($nbRenf))
				return (-1);

			return (intval($nbRenf[0]['NB']));
		}








		//////////////////////////////////
		//								//
		//		PRIVATE FUNCTIONS 		//
		//								//
		//////////////////////////////////

		/**
		*	Recupere l'ID du joueur auquel le territoire appartient
		*
		*	@param Int $player Id du joueur
		*	@return Int L'id du joueur
		*/
		private function 	getTerritoryOwner($place) {
			$pl = $this->_db->GetRows("SELECT `boards`.`board_player` FROM `boards` WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_place` = '$place' ORDER BY `boards`.`board_id` DESC LIMIT 0, 1");

			if (is_null($pl))
				return (null);

			return (intval($pl[0]['board_player']));
		}

		/**
		*	Set en BDD le nombre de renforts du joueur pour son prochain tour
		*
		*	@param {Int} $player Id du joueur
		*	@return {Boolean} True si tout a fonctionne, false sinon
		*/
		private function 	setRenforcementNumber($player) {
			$nb = $this->GetRenforcementNumber($player);

			return($this->_db->Execute("UPDATE `players_in_games` SET `pig_renf_max` = '$nb',`pig_renf_number` = '$nb' WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'"));
		}

		/**
		*	Parcours tout les pays d'un joueur pour verifier si celui ci possede  un ou des continents entiers.
		*	Si c'est le cas, retourne 
		*
		*	@param {Array} $map Les territoires du joueur
		*	@return {Int} le nombre de troupes en plus dont il beneficie
		*/
		private function 	getBonusContinent($map) {
			$continents = array('northAmerica' => 0, 'sudAmerica' => 0, 'europa' => 0, 'africa' => 0, 'asia' => 0, 'australia' => 0);
			$id;
			$bonus = 0;

			foreach ($map as $pays) {
				$id = intval($pays['board_place']);

				switch ($id) {
					case 3:
					case 4:
					case 5:
					case 9:
					case 35:
					case 36:
					case 37:
					case 38:
					case 41:
						$continents['northAmerica']++; // 9 pour avoir le territoire
						break;

					case 6:
					case 33:
					case 34:
					case 39:
						$continents['sudAmerica']++; // 4 pour avoir le territoire
						break;

					case 10:
					case 11:
					case 12:
					case 25:
					case 26:
					case 27:
					case 28:
						$continents['europa']++; // 7 pour avoir le territoire
						break;

					case 7:
					case 8:
					case 29:
					case 30:
					case 31:
					case 32:
						$continents['africa']++; // 6 pour avoir le territoire
						break;

					case 13:
					case 14:
					case 15:
					case 16:
					case 17:
					case 18:
					case 19:
					case 20:
					case 21:
					case 22:
					case 23:
					case 24:
						$continents['asia']++; // 12 pour avoir le territoire
						break;

					case 0:
					case 1:
					case 2:
					case 40:
						$continents['australia']++; // 4 pour avoir le territoire
						break;
				}
			}

			// Maintenant c'est l'heure du bilan
			if ($continents['australia'] == 4)
				$bonus += 2;
			if ($continents['sudAmerica'] == 4)
				$bonus += 2;
			if ($continents['northAmerica'] == 9)
				$bonus += 5;
			if ($continents['europa'] == 7)
				$bonus += 5;
			if ($continents['asia'] == 12)
				$bonus += 7;
			if ($continents['africa'] == 6)
				$bonus += 3;

			return ($bonus);
		}

		/**
		*	Check si le joueur a perdu son dernier territoire.
		*	Si c'est le cas, il a perdu et on update son statut. 
		*
		*	@param {Int} $player L'id du joueur qui vient de perdre un territoire
		*	@return {Boolean} True si le joueur a perdu, false sinon
		*/
		private function thisThisTheEndMyFriend($player) {
			// SI le joueur n'as plus de troupes, il a perdu
			if ($this->GetUnitsNumber($player) <= 0) {
				// On passe le joueur a mort
				$this->closeGameForPlayer($player);

				return (true);
			}

			return (false);
		}

		/**
		*	Check si la partie est terminée lorsqu'un joueur vient de perdre un territoire.
		*
		*	@param {Int} $player L'id du joueur qui vient de perdre un territoire
		*	@return {Boolean} True si il ne reste qu'un joueur (le gagnant), false sinon
		*/
		private function checkEndGame($player) {
			$nb_players;

			// Verifier si le joueur a perdu
			if ($this->thisThisTheEndMyFriend($player)) { // Si oui, on verifie combien de joueurs sont encore présent
				$nb_players = count($this->GetPlayers(true));
				
				// Si il ne reste qu'un joueur, on cloture cette partie
				if ($nb_players <= 1) {
					// Cloture le joueur courant en le marquant comme gagnant
					$this->closeGameForPlayer($_SESSION['user-id']);

					// Merci-au-revoir
					$this->endThisGameInDB();

					return (true);
				}
			}

			return (false);
		}

		/**
		*	Termine un joueur proprement en BDD. Attribue le score.
		*
		*	@param {Int} $player L'id du joueur
		*	@param {Boolean} $abandon true si le joueur a abandonne
		*	@param {Int} $malus points perdu par le joueur qui abandonne
		*/
		private function closeGameForPlayer($player, $abandon = false, $malus = 0) {
			$nb;
			$status;
			$points = 0;

			// Recupere le nombre de joueurs EN VIE
			$nb = $this->_db->GetRows("SELECT COUNT(`players_in_games`.`pig_player`) AS `NB`, `games`.`game_nb_players` AS `Total` FROM `players_in_games` JOIN `games` ON `games`.`game_id` = `players_in_games`.`pig_game` WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player_status` = 'alive' ORDER BY `players_in_games`.`pig_player_status` = 'alive'");

			// Classement du joueur et calcul des points
			switch ($nb[0]['NB']) {
				case '4':
					$status = 'four';
					break;
				case '3':
					$status = 'three';
					break;
				case '2':
					$status = 'two';
					break;
				default:
					$status = 'winner';
					break;
			}

			// Attribution de la place
			if (!$this->_db->Execute("UPDATE `players_in_games` SET `pig_player_status` = '$status' WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'"))
				echo "caca tout mou";

			// Update du score
			if ($abandon === false) {
				// Si le joueur courant est le gagnant, pas besoin d'updater son score
				if ($status !== 'winner')
					$points = $this->updateScore($player, $this->isThisGameInCurrentPeriod());
			}
			else if ($malus != 0) {
				if (!$this->_db->Execute("UPDATE `players` SET `player_score` = `player_score` + $malus WHERE `players`.`player_id` = '$player'"))
					echo "caca tout mou x3";
				$points = $malus;
			}

			// Envoie du mail
			if (!$abandon)
				$this->sendPlayerNotification($player, array('type' => 'endGame',
																'position' => $status,
																'points' => $points));
		}

		/**
		*	Met le score des joueurs à jour quand une partie se termine.
		*	Dans les faits, on va enlever autant de points qu'il reste de joueurs à $player et redistribuer ces points aux joueurs encore en lice.
		*
		*	@param {Int} $player 				L'id du joueur
		*	@param {Boolean} $isCurrentPeriod 	True si la partie compte pour la période courante
		*	@return {Int} 	Score enlevé au joueur
		*/
		private function updateScore($player, $isCurrentPeriod) {
			$alivePlayers;
			$pointsLoose = 0;
			$p_id;
			$query;

			// Première étape, on récupère les joueurs encore en jeu
			$alivePlayers = $this->GetPlayers(true);
			// Le perdant redistribue un de ses points à chaque joueur encore en jeu
			$pointsLoose = count($alivePlayers);

			// Update du score global du joueur
			$query = "UPDATE `players` SET `player_global_score` = `player_global_score` - $pointsLoose";
			// Si la partie compte pour la période courante, update du score courant du joueur
			if ($isCurrentPeriod)
				$query .= ", `player_score` = `player_score` - $pointsLoose";
			$query .= " WHERE `players`.`player_id` = '$player'";
			
			// Update in DB
			$this->_db->Execute($query);

			// Maintenant que les points ont été enlevés au perdant, on les redistribue aux personnes encore en jeu
			foreach ($alivePlayers as $p) {
				$p_id = $p['id'];

				// On ajoute un point au score global du joueur
				$query = "UPDATE `players` SET `player_global_score` = `player_global_score` + 1";
				// Si la partie compte pour la période courante, on donne également un point au score courant du joueur
				if ($isCurrentPeriod)
					$query .= ", `player_score` = `player_score` + 1";
				$query .= " WHERE `players`.`player_id` = '$p_id'";
				
				// Update in DB
				$this->_db->Execute($query);
			}

			return ($pointsLoose);
		}

		/**
		*	Clos une partie dans la base de données. Supprime les coups, le plateau et passe le jeu en mode terminé.
		*
		*	@param {Int} $player L'id du joueur
		*/
		private function endThisGameInDB() {

			// TODO: Flusher Board (SAUF le dernier etat ?)
			// TODO: Flusher Strokes
			// TODO: Garder les messages ? Les players_in_games ?

			$this->_db->Execute("UPDATE `games` SET `game_status` = 'ended', `game_end_date` = CURRENT_TIMESTAMP WHERE `games`.`game_id` = '$this->_gameID'");
		}

		/**
		*	Si un joueur abandonne une partie sans y jouer (== refus de la partie), il ne perd pas de points et ses stats ne sont pas modifies.
		*
		*	@param {Int} $player L'id du joueur
		*/
		private function playerDontWantToPlayThisGame($player) {
			// Passer tous les territoires du joueur en neutre
			$this->_db->Execute("UPDATE `boards` SET `boards`.`board_player`= '0' WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_player` = '$player'");

			// Supprimer le joueur de la partie. L'effacer completement reviens a ne garder aucune trace de cette partie, et donc des stats propres.
			$this->_db->Execute("DELETE FROM `players_in_games` WHERE `players_in_games`.`pig_game` = '$this->_gameID' AND `players_in_games`.`pig_player` = '$player'");

			// On diminue le nombre de joueurs de la partie
			$this->_db->Execute("UPDATE `games` SET `game_nb_players` = `game_nb_players` - 1 WHERE `games`.`game_id` = '$this->_gameID'");
		}

		/**
		*	Si un joueur abandonne une partie apres avoir joue un ou plusieurs coups, il est coupable d'abandon !!!
		*	Qu'on le pende haut et court !
		*
		*	@param {Int} $player L'id du joueur
		*/
		private function playerStopGame($player) {
			// Passer tous les territoires du joueur en neutre
			$this->_db->Execute("UPDATE `boards` SET `boards`.`board_player`= '0' WHERE `boards`.`board_game` = '$this->_gameID' AND `boards`.`board_player` = '$player'");

			// Le virer avec son malus
			$this->closeGameForPlayer($player, true, -3);
		}

		/**
		*	Retourne un booleen pour savoir si la partie compte pour la période courante
		*
		*	@param
		*	@return {Boolean} True si la partie compte pour la periode courante, sinon False
		*/
		private function isThisGameInCurrentPeriod() {
			// Récupération du timestamp de debut de la période courante
			$startCurrentPeriod = $this->_db->GetRows("SELECT UNIX_TIMESTAMP(`game_periods`.`gp_start_date`) AS `Start` FROM `game_periods` ORDER BY `game_periods`.`gp_start_date` DESC LIMIT 0, 1");

			// Récupération de la date de début de partie
			$gameStartTime = $this->_db->GetRows("SELECT UNIX_TIMESTAMP(`games`.`game_start_date`) AS `Start` FROM `games` WHERE `games`.`game_id` = '$this->_gameID'");

			if ((is_null($gameStartTime[0])) || (is_null($startCurrentPeriod[0])))
				return (false);

			$startCurrentPeriod = intval($startCurrentPeriod[0]['Start']);
			$gameStartTime = intval($gameStartTime[0]['Start']);
			
			if ($gameStartTime < $startCurrentPeriod)
				return (false);

			return (true);
		}

		/**
		*	Envoie une notification au joueur si celui-ci les accepte.
		*
		*	@param {Int} $player L'id du joueur a notifier
		*/
		private function sendPlayerNotification($player, $infos) {
			// Recuperation des preferences utilisateur
			$pref = $this->_db->GetRows("SELECT `players`.`player_notification`, `players`.`player_mail` FROM `players` WHERE `players`.`player_id` = '$player'");

			if (is_null($pref[0]) || $pref[0]['player_notification'] == '0')
				return;

			// Ajout du mail du destinataire
			$infos['to'] = $pref[0]['player_mail'];
			
			// Envoie du mail - Pour le moment redirection htmlengine
			$post = http_build_query($infos);
			$context_options = array (
					'http' => array (
						'method' => 'POST',
						'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
							. "Content-Length: " . strlen($post) . "\r\n",
						'content' => $post
						)
					);
			
			$context = stream_context_create($context_options);
			$fp = fopen('http://172.21.253.41/htmlengine/old/ben/mailing/mail.php', 'r', false, $context);
		}

	}
 ?> 