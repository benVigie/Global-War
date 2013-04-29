var EnumStates = {};
	EnumStates.Wait			= 0;
	EnumStates.Replay		= 1;
	EnumStates.Renforcement	= 2;
	EnumStates.Attack		= 3;
	EnumStates.Move			= 4;
	EnumStates.Last_move	= 5;

/**
*	Cette classe est la partie cliente du moteur de jeu. C'est elle qui gere les actions du joueur et manage les differentes phases/actions du jeu
*	Elle dialogue avec le serveur pour recuperer les informations et maintenir toutes ces infos a jour a chaque mouvement. 
*
*	@param: {Int} gameID => L'id du jeu en cours
*	@param: {Int} playerID => L'id du joueur
*	@return: {Object} Le subset des methodes publiques de ce jeu.
*/
function	GameEngine(gameID, playerID) {
	// Les proprietes de notre GameEngine
	var that = {},
		_gameID = gameID,				// ID de la partie en cours
		_playerID = playerID,			// ID du joueur
		_isPlayerTurn = false,			// Booleen qui nous dit si c'est actuellement le tour du joueur
		_playersList = null,			// Tableau de liste des joueurs
		_callbackReminder = null,		// Un reminder de callback
		_gameState = EnumStates.Wait,	// Etat du jeu
		_attackCountry = null,			// Place qui attaque
		_attackedCountry = null,		// Place qui est attaque
		_playerColor = null,			// Couleur du joueur. Utilise dans la reconnaissance de territoire
		_ownerTerritories = [],			// Tableau des territoires appartenant au joueur
		_placeA, _placeB;				// Variables pour stocker le nombre d'unités de 2 territoires
		
	// Liste des etats et de leur relation (Oui monsieur c'est du fait main :)
	var	_borders = [[2,40],[2,21,40],[0,1,40],[5,15,41],[5,9,35,36,37,41],[3,4,9,41],[33,38,39],[30,32],[26,27,29,30,31,33],[4,5,10,37],[9,11,12],[10,12,27,28],[10,11,25,28],[15,23],[15,16,24],[3,13,14,23,24],[14,17,22,23,24],[16,18,22,25],[17,19,20,22,25],[18,20,25,26,29,30],[18,19,21,22],[1,20,22],[16,17,18,20,21,23],[13,15,16,22,24],[14,15,16,23],[12,17,18,19,26,28],[8,19,25,27,28,29],[8,11,26,28],[11,12,25,26,27],[8,26,19,30],[7,8,19,29,31,32],[8,30,32],[7,30,31],[6,8,34,39],[33,39],[4,36,37,38],[4,35,38,41],[4,9,35],[6,35,36],[6,33,34],[0,1,2],[3,4,5,36],[3,4,5,36]];




	/**
	*	Fonction d'erreur generique qui fait apparaitre uen petite fenetre notifiant du probleme des qu'une requete/erreur apparait dans le jeu
	*
	*	@param: {Object} jqXHR => L'objet xhr parent qui necessite une levee d'erreur.
	*	@param: {String} textStatus => Le message que l'on veut faire apparaitre
	*	@return:
	*/
	function notifyError (jqXHR, textStatus) {
		console.log('GameEngine::jQuery Ajax error. Raison invoquée: [' + textStatus + ']');
		displayMessage(textStatus, 'Ouuups !', 'error');
	}

	/**
	*	Determine en fonction d'un ID de joueur en parametre si c'est a ce dernier de jouer 
	*
	*	@param: {Int} currentID => id du joueur a tester
	*	@return:
	*/
	function setPlayerTurn (currentID) {
		if (currentID === _playerID)
			_isPlayerTurn = true;
		else
			_isPlayerTurn = false;
	}

	/**
	*	Fonction appele lorsqu'une requete de type getRenforcements ou getUnits a reussi.
	*	Parcours la reponse du serveur et met a jour l'encart des infos joueur recupere.
	*
	*	@param: {Object} datas => L'objet retourne par le serveur contenant le nombre d'unites et le nombre de renforts du joueur
	*	@return:
	*/
	function setPlayerSpecialUnits (datas) {
		var playerDiv, key;

		// En cas d'erreur...
		if (datas.error) {
			displayMessage(datas.error, 'Ouuups !', 'error');
			return;
		}

		for (key in datas) {
			if (key === 'units') {
				playerDiv = document.querySelector('#unitsPlayer' + datas.player);
				if (playerDiv)
					playerDiv.innerHTML = datas.units.toString();
			}
			else if (key === 'renforcements') {
				playerDiv = document.querySelector('#renfortsPlayer' + datas.player);
				if (playerDiv)
					playerDiv.innerHTML = datas.renforcements.toString();
			}
		}
	}


	/**
	*	Callback appele lorsque qu'une requete type "qui sont les joueurs de la partie ?" a reussi
	*	Set pour chaque joueur sa couleur et demande son nombre d'unites et ses renforts
	*
	*	@param: {Object} infos => La liste des joueurs retourne par le serveur
	*	@return:
	*/
	function onPlayersInfosReceive (infos) {
		// En cas d'erreur...
		if (infos.error) {
			displayMessage(infos.error, 'Ouuups !', 'error');
			return;
		}

		// Pour chaque joueur
		for (var i in infos.playerList) {
			// Si pas encore de couleur, on en attribue une
			if (infos.playerList[i].color === '')
				infos.playerList[i].color = 'black';
			
			// Si on est en train de manipuler notre joueur, on stocke sa couleur
			if (infos.playerList[i].id === _playerID.toString())
				_playerColor = infos.playerList[i].color;

			// On recupere 2 infos supplementaires
			$.ajax({ // Le nombre de renforts
				url: 'ajax/getRenforcementNumber.php?game=' + _gameID + '&player=' + infos.playerList[i].id,
				dataType: 'json',
				success: setPlayerSpecialUnits,
				error: notifyError
			});
			$.ajax({ // Le nombre d'unites'
				url: 'ajax/getUnitsNumber.php?game=' + _gameID + '&player=' + infos.playerList[i].id,
				dataType: 'json',
				success: setPlayerSpecialUnits,
				error: notifyError
			});
			
		}

		setPlayerTurn(infos.currentPlayer);
		_playersList = infos.playerList;
		if (_callbackReminder)
			_callbackReminder(infos);
	}

	/**
	*	Update l'encart du joueur pour mettre a jour son nombre d'unites et de renforts
	*
	*	@param: {Int} playerID => Id du joueur a updater
	*	@param: {Int} units => Nombre d'unitées du joueur. Si NULL, On update pas
	*	@param: {Int} renforts => Nombre de renforts du joueur. Si NULL, On update pas
	*	@return:
	*/
	function updatePlayerInformations(playerID, units, renforts) {
		var playerDiv;

		if (units !== null) {
			playerDiv = document.querySelector('#unitsPlayer' + playerID);
			if (playerDiv)
				playerDiv.innerHTML = units.toString();
		}
		if (renforts !== null) {
			playerDiv = document.querySelector('#renfortsPlayer' + playerID);
			if (playerDiv)
				playerDiv.innerHTML = renforts.toString();
		}
	}

	/**
	*	Callback appele lorsqu'on recupere la liste des evenements passe depuis le dernier tour de jeu
	*	Dans le processus du jeu, on joue ce scenario puis on lance une requete de renforts pour le joueur courant. Si cela fonctionne, on continue le deroule du jeu...
	*
	*	@param: {Object} datas => La liste de tous les evenements
	*	@return:
	*/
	function onScenarioReceive (datas) {
		// Pour pas avoir de caca...
		if (datas !== null) {
			// console.log(datas);
			// En cas d'erreur...
			if (datas.error) {
				displayMessage(datas.error, 'Ouuups !', 'error');
				return;
			}
			
			// On deroule le scenario de chaque coup joue
			console.log('>>>>>>>>> Deroulement du scenario precedent');
			if (datas !== null) {
				for (i in datas) {
					console.log(datas[i]);
				}
			}
			console.log('<<<<<<<< Fin du scenar');
		}

		// Pour finir, on fait la transition en appelant la fonction de renforts
		$.ajax({
			url: 'ajax/getRenforcementNumber.php?game=' + _gameID + '&player=' + _playerID + '&playerTurn',
			dataType: 'json',
			success: stepTwo_Renforcement,
			error: notifyError
		});
	}

	/**
	*	Callback appele si un mouvement de troupes a lieu
	*	On verifie si c'etait le dernier mouvement (=> fin du jeu) ou si c'est suite a une attaque (=> on repasse en mode attaque)
	*
	*	@param: {Object} datas => Objet contenant l'etat des territoires source et destination
	*	@return:
	*/
	function onMoveDone(datas) {
		if (datas.error) {
			displayMessage(datas.error, 'Ouuups !', 'error');
			return;
		}

		console.log('Mouvement de troupes: ');
		console.log(datas);
		
		// Si c'est suite a un dernier deplacement, on appelle la methode de fin de tour
		if (_gameState === EnumStates.Last_move) {
			endTurn();
			return;
		}

		// Sinon on repart en mode attaque !!!
		updateGameState(EnumStates.Attack);

		// On cache la vieille fenetre de deplacement
		document.querySelector('#actionDiv').classList.remove('show');

		// Et aussi l'ancien territoire attaqué
		hideSelectedCountries();
	}

	/**
	*	Fonction appelée après le scénario. Permet de passer en mode "Renforts"
	*	Concrètement on recoit le nombre de renforts, on l'affiche et on attend que le user clique :)
	*
	*	@param: {Object} datas => Objet contenant les renforts actuels du joueur
	*	@return:
	*/
	function stepTwo_Renforcement(datas) {
		var actDiv = document.querySelector('#actionDiv'),
			renfDiv;

		if (datas.error) {
			displayMessage(datas.error, 'Ouuups !', 'error');
			return;
		}

		// debugger
		// Si il reste des renforts
		if (datas.renforcements > 0) {
			renfDiv = '<div id="act-renforcement">Renforts restant:<br/><span>' + datas.renforcements + '</span><a class="m-btn red"><i class="icon-white icon-share-alt"></i> Oups !</a></div>'
			actDiv.innerHTML = renfDiv;
			actDiv.classList.add('show');

			updateGameState(EnumStates.Renforcement);
		}
		else
			updateGameState(EnumStates.Attack);
	}

	/**
	*	Change l'etat du jeu. Une partie est compose de differentes phases (attente, scenario, renforts, attaque, deplacements, dernier deplacement).
	*	Cette methode fait passer le jeu dans un nouvel etat et applique au passage differentes notifications/changements/affichages
	*
	*	@param: {Enum} state => Le nouvel etat sur lequel switcher
	*	@return:
	*/
	function updateGameState(state) {
		var stateDiv = document.querySelector('#gameState');

		switch (state) {
			case EnumStates.Replay:
				stateDiv.innerHTML = "Previously, in Global War...";
				_gameState = EnumStates.Replay;
				break;

			case EnumStates.Renforcement:
				stateDiv.innerHTML = "Placez vos renforts";
				_gameState = EnumStates.Renforcement;
				break;
			
			case EnumStates.Attack:
				hideSelectedCountries();
				// Update texte et ajout bouton fin de tour
				stateDiv.innerHTML = 'Attaque ! <br/> <a id="btn-endTurn" class="m-btn blue"> Fin du tour <i class="icon-white icon-flag"></i></a>';
				document.querySelector('#btn-endTurn').onclick = function () {
					updateGameState(EnumStates.Last_move);
				}
				break;
			
			case EnumStates.Move:
				stateDiv.innerHTML = 'Déplacement de troupes';
				break;
			
			case EnumStates.Last_move:
				hideSelectedCountries();
				_attackCountry = null;
				stateDiv.innerHTML = 'L\'ultime déplacement <br/> <a id="btn-back-attack" class="m-btn red"> Attaquer encore <i class="icon-white icon-share"></i></a> <a id="btn-finish" class="m-btn green"> Fin du tour <i class="icon-white icon-flag"></i></a>';

				document.querySelector('#btn-back-attack').onclick = function () {
					updateGameState(EnumStates.Attack);
				}
				document.querySelector('#btn-finish').onclick = endTurn;
				break;
			
			case EnumStates.Wait:
				break;
			default:
				console.log('GameEngine::updateGameState() => état inconnu, quit');
				return;
		}

		// Finallement on switch sur le bon état 
		_gameState = state;
	}


	/*-------------------------------
	|								|
	|		Methodes Publiques		|
	|								|
	-------------------------------*/

	/**
	*	Charge l'etat d'une carte pour un moment donne 
	*
	*	@param: {Function} callback => La callback a appele lorsque le serveur repondra
	*	@return:
	*/
	that.LoadMap = function (callback) {
		$.ajax({
			// url: 'ajax/getMapState.php?game=' + _gameID + '&time=',
			url: 'ajax/getMapState.php?game=' + _gameID,
			dataType: 'json',
			success: callback,
			error: notifyError
		});
	};

	/**
	*	Envoie une requete pour decouvrir les differents joueurs de la partie
	*
	*	@param: {Function} callback => La callback a appele lorsque le serveur repondra (et qu'on aura traite cette reponse !)
	*	@return:
	*/
	that.GetPlayersInfos = function (callback) {
		// On se garde la callback a rappeller
		_callbackReminder = callback;

		// Requete qui recupere la liste des joueurs, leurs infos ainsi que le joueur courant
		$.ajax({
			url: 'ajax/getPlayersOfTheGame.php?game=' + _gameID,
			dataType: 'json',
			async: false,
			success: onPlayersInfosReceive,
			error: notifyError
		});
	};

	/**
	*	Retourne TRUE si c'est al'utilisateur de joiuer, FALSE si ce n'est pas son tour
	*
	*	@param:
	*	@return: {Boolean} Trus si c'est a lui, False si il est simple spectateur
	*/
	that.IsPlayerTurn = function () {
		return (_isPlayerTurn);
	}

	/**
	*	Cette methode va veritablement lancer la partie. Si c'est au joueur de jouer, on demande un compte rendu des dernieres actions puis on lance la machine !!
	*
	*	@param:
	*	@return:
	*/
	that.StartPlayerTurn = function () {
		// La on rentre dans le vif du sujet !
		updateGameState(EnumStates.Replay);

		// Dans un premier temps, on recupere le scenario pour le jouer
		$.ajax({
			url: 'ajax/getScenario.php?game=' + _gameID + '&player=' + _playerID,
			dataType: 'json',
			async: false,
			success: onScenarioReceive,
			error: notifyError
		});
	}

	/**
	*	Lorsqu'un joueur change de couleur, on doit mettre cette derniere a jour car elle est utilise pour differents checks
	*
	*	@param: {String} color La couleur choisie
	*	@return:
	*/
	that.ChangePlayerColor = function (color) {
		_playerColor = color;
	}




	/**
	*	A cause de l'empillement des objets SVG pastille et texte, le noeud clique pouvait etre soit l'un soit l'autre.
	*	Afin de recuperer a chaque fois la bonne infos de cet amas de noeud, cette fonction determine l'objet clique et lis la bonne info contenue dans la pastille
	*
	*	@param: {Object} event => Le noeud SVG clique
	*	@param: {String} attrName => Le nom de l'attribut a recuperer
	*	@return: {Int} La valeur de l'attribut desire, ou -1 si introuvable
	*/
	function getClickAttribute(event, attrName) {
		var attr = '-1',
			node, nodeType;
		
		// Recuperation du noeud et de son type
		node = (event.srcElement) ? event.srcElement : event.currentTarget;
		nodeType = node.toString();

		if (node) {
			if (nodeType === '[object SVGCircleElement]')
				attr = node.getAttribute(attrName);
			// else if ((nodeType === '[object SVGTSpanElement]') || (nodeType === '[object SVGTextElement]'))
			else if (nodeType === '[object SVGTSpanElement]')
				attr = node.parentNode.getAttribute(attrName);
			else if (nodeType === '[object SVGTextElement]') {
				attr = node.getAttribute(attrName);
			}
		}

		return (parseInt(attr, 10));
	}

	/**
	*	Callback appele lorsque le joueur distribue ses renforts sur la carte. Si il en retse, on continue la distribution, sinon on passe a l'attaque !
	*
	*	@param: {Object} datas => La reponse du serveur contenant le nombre de renforts restants ainsi que le territoire update
	*	@return:
	*/
	function onRenfReceive(datas) {
		var updt, nb;

		if (datas.error) {
			displayMessage(datas.error, 'Ouuups !', 'error');
			updateGameState(EnumStates.Renforcement);
			return;
		}

		// Si l'ajout a fonctionné
		if (datas.action === 'add') {
			// Update de la pastille
			updt = document.querySelector('#text' + datas.place + ' > tspan');
			// console.log(updt);
			updt.textContent = datas.units;

			// Update de l'encart renforts
			updt = document.querySelector('#act-renforcement > span');
			nb = parseInt(updt.innerHTML, 10) - 1;

			if (nb == 0) {
				document.querySelector('#actionDiv').classList.remove('show');

				updateGameState(EnumStates.Attack);
			}
			else { // Il reste des renforts a attribuer, on continue !
				updt.innerHTML = nb.toString();
				window.setTimeout(function() {
					updateGameState(EnumStates.Renforcement);
				}, 400);
			}
		}
	}

	/**
	*	Callback appele lorsqu'une attaque a reussie. Update les territoires concernes, la fenetre de score et decide selon le resultat
	*	de passer en mode "deplacement" ou de continuer avec la fenetre d'attaque 
	*
	*	@param: {Object} datas => La reponse du serveur contenant le resultat des des, l'etat des territoires et l'info si oui ou non l'attaque a gagne le territoire
	*	@return:
	*/
	function onAttackResults(datas) {
		var aDices = document.querySelectorAll('.attackDice'),
			dDices = document.querySelectorAll('.defenseDice'),
			place;

		console.log(datas);

		if (datas.error) {
			displayMessage(datas.error, 'Ouuups !', 'error');
			updateGameState(EnumStates.Attack);
			return;
		}

		// Display du score dans la fenetre de des
		for (var i = 0; i < 3; i++) {
			if (datas.rollsA[i]) {
				aDices[i].innerHTML = datas.rollsA[i];
				if (datas.rollsD[i]) {
					aDices[i].classList.add((datas.rollsD[i] >= datas.rollsA[i]) ? 'loose' : 'win');
				}
			}
			if (datas.rollsD[i]) {
				dDices[i].innerHTML = datas.rollsD[i];
				if (datas.rollsA[i]) {
					dDices[i].classList.add((datas.rollsD[i] >= datas.rollsA[i]) ? 'win' : 'loose');
				}
			}
		};

		// Update des unites sur le terrain
		place = document.querySelector('#text' + datas.Aplace.toString() + ' > tspan');
		place.textContent = datas.AplaceUnits;
		place = document.querySelector('#text' + datas.Dplace.toString() + ' > tspan');
		place.textContent = datas.DplaceUnits;

		// Update de l'encart des joueurs
		updatePlayerInformations(datas.player.id, datas.player.units, datas.player.renforcements);
		updatePlayerInformations(datas.ennemy.id, datas.ennemy.units, datas.ennemy.renforcements);
		
		// Si l'attaque a gagnée, on affiche la petite fenêtre de déplacement
		if (datas.endGame === true) {
			updateGameState(EnumStates.Wait);
			updateInvadedCountry(_playerID, datas.Dplace);

			// On affiche l'etat final en grand et on ferme les fenetres de deplacement
			document.querySelector('#gameState').innerHTML = "Félicitation, vous remportez la partie !";
			document.querySelector('#gameState').classList.add('victoryState');
			document.querySelector('#actionDiv').classList.remove('show');
		}
		else if (datas.AttackHasWin === true) {
			window.setTimeout(function () {
				// Changement de propriétaire !
				updateInvadedCountry(_playerID, datas.Dplace);
				// Ouvre la petite fenetre de deplacement
				openMoveWindow(datas.AplaceUnits, datas.DplaceUnits);
				// Changement de l'état du jeu
				updateGameState(EnumStates.Move);
			}, 2000);

		}
		else
			updateGameState(EnumStates.Attack);
	}

	/**
	*	Recupere une info d'un joueur selon son id
	*
	*	@param: {Int} playerID l'Id du joueur
	*	@param: {String} info le nom de l'information voulue: 'picture' ou 'color'
	*	@return: {String} L'information demandee
	*/
	function getPlayerInfo(playerID, info) {
		var res = null;

		// Selon l'info demande on recupere la donne
		switch (info) {
			case 'picture':
				if (document.querySelector('#player' + playerID + ' .playerBasicInfos > img'))
					res = document.querySelector('#player' + playerID + ' .playerBasicInfos > img').getAttribute('src');
				else
					res = 'images/users/noset.jpg';
				break;
			case 'color':
				if (document.querySelector('#player' + playerID))
					res = document.querySelector('#player' + playerID).getAttribute('data-playercolor');
				else
					res = "grey";
				break;

			default:
				consolr.log("L'information demandee est incomprise ou inexistante: " + info + '[' + playerID + ']');
		}

		return (res);
	}

	/**
	*	Met a jour le pays envahit (change la couleur et d'autres petits trucs...) 
	*
	*	@param: {Int} invaderID
	*	@param: {Int} country => id of the invadedv country
	*	@return:
	*/
	function updateInvadedCountry (invaderID, country) {
		document.querySelector('.pastille' + country).setAttribute('fill', _playerColor);
	}

	/**
	*	Dis si oui ou non le territoire "place" appartient au joueur
	*
	*	@param: {Int} place: id du terrain
	*	@return: true si il lui appartient, false sinon
	*/
	function checkTerritoryOwnByPlayer(place) {
		var circle = document.querySelector('.pastille' + place.toString());

		if (circle.getAttribute('fill') === _playerColor)
			return (true);
		return (false);
	}

	function attackWindow(attackTeam, defenseTeam) {
		var place, nbA, nbD,
			box;

		// Recuperation du nombre d'unites sur ces territoires
		place = document.querySelector('#text' + attackTeam.toString() + ' > tspan');
		nbA = parseInt(place.textContent, 10) - 1; // Moins un car il doit rester toujours au moins une unitee sur le territoire qui attaque
		place = document.querySelector('#text' + defenseTeam.toString() + ' > tspan');
		nbD = (parseInt(place.textContent, 10) >= 2) ? 2 : 1;

		// Si on a pas de couilles a poser sur la table, on passe son tour
		if (nbA < 1)
			return;

		// Recuperation de l'id du joueur attaque
		attackedPlayerID = document.querySelector('#text' + defenseTeam.toString()).getAttribute('id').substring(4);
		attackedPlayerID = document.querySelector('.pastille' + attackedPlayerID).getAttribute('fill');
		var players = document.querySelectorAll('#PlayersInGame > section');
		for (var i = 0; i < players.length; i++ ) {
			if (players[i].getAttribute('data-playercolor') == attackedPlayerID)
				attackedPlayerID = players[i].getAttribute('id').substring(6);
		};

		openAttackWindow(Math.min(nbA, 3), nbD, attackedPlayerID);
	}

	function addRemoveMove(e) {
		var numbers = document.querySelectorAll('#moveBox > span'),
			nb1, nb2,
			move, min;

		// console.log(e);
		if (!numbers || numbers.length !== 2)
			return;

		// Recuperation des nombres
		nb1 = parseInt(numbers[0].innerHTML, 10);
		nb2 = parseInt(numbers[1].innerHTML, 10);

		// On veut ajouter ou enlever ?
		move = (e.currentTarget.id === 'btn-move-more') ? 1 : -1;

		// Enfin on calcule la limite. 3 unites min a deplacer pour un move normal, un au moins pour un final
		min = (_gameState === EnumStates.Last_move) ? 1 : 3;

		// Updat des unitees pour tester
		nb1 += -move;
		nb2 += move;

		if (nb2 < min || nb1 < 1 || nb2 < 1) {
			// console.log('Deplacement impossible');
			// debugger
			return;
		}

		// Update sur la carte puis dans la fenetre
		document.querySelector('#text' + _attackCountry.toString() + ' > tspan').textContent = nb1;
		document.querySelector('#text' + _attackedCountry.toString() + ' > tspan').textContent = nb2;

		numbers[0].innerHTML = nb1;
		numbers[1].innerHTML = nb2;

		// TODO: add 'disable' in classList in case we can't push one of teh buttons
	}

	function validMove() {
		// On récupère les nombres de troupes
		var numbers = document.querySelectorAll('#moveBox > span');
		
		if (numbers.length === 0)
			return;

		console.log('ajax/move.php?game=' + _gameID + '&player=' + _playerID + '&src_place=' + _attackCountry + '&dst_place=' + _attackedCountry + '&src_nb=' + numbers[0].innerHTML + '&dst_nb=' + numbers[1].innerHTML);

		$.ajax({
			url: 'ajax/move.php?game=' + _gameID + '&player=' + _playerID + '&src_place=' + _attackCountry + '&dst_place=' + _attackedCountry + '&src_nb=' + numbers[0].innerHTML + '&dst_nb=' + numbers[1].innerHTML,
			dataType: 'json',
			success: onMoveDone,
			error: notifyError
		});
	}

	function stopMove() {
		hideSelectedCountries();

		document.querySelector('#text' + _attackCountry.toString() + ' > tspan').textContent = _placeA;
		document.querySelector('#text' + _attackedCountry.toString() + ' > tspan').textContent = _placeB;
		
		document.querySelector('#actionDiv').classList.remove('show');
	}

	function createOwnerTerritoryArray() {
		var i, j;

		for (i = 0; i < _borders.length; i++) {
			_ownerTerritories[i] = [];
			
			for (j = 0; j < _borders[i].length; j++)
				if (checkTerritoryOwnByPlayer(_borders[i][j]))
					_ownerTerritories[i].push(_borders[i][j]);
		}
		console.log("Le tableau final");
		console.log(_ownerTerritories);
	}

	//------------------------------
	// 		PETITES FENETRES
	//------------------------------
	function openAttackWindow(nbAttack, nbDef, attackedPlayerID) {
		var box, i;

		// On ajoute de 1 a 3 dés en attaque
		box = '<div id="diceBox">';
		if (attackedPlayerID)
			box += '<header><img src="' + getPlayerInfo(_playerID, 'picture') + '" style="border-color: ' + _playerColor + '"> VS <img src="' + getPlayerInfo(attackedPlayerID, 'picture') + '" style="border-color: ' + getPlayerInfo(attackedPlayerID, 'color') + '" class="diceBox-pic-right"></header>';
	
		for (i = 0; i < nbAttack; i++) {
			box += '<span class="attackDice">?</span> ';
		}
		// Petit versus :)
		box += '&nbsp;&nbsp;-&nbsp;&nbsp;';
		
		// Meme chose en defense (mais 2 maxi)
		for (i = 0; i < nbDef; i++) {
			box += '<span class="defenseDice">?</span> ';
		}

		// Maintenant les boutons et on est bon
		// box += '</div><a id="btn-attack" class="m-btn green"> Attaquer !</a> <a id="btn-stop" class="m-btn red"> Stop <i class="icon-white icon-remove-circle"></i></a>';
		box += '</div><a id="btn-attack" class="m-btn green"><i class="icon-white icon-world"></i> Lancer les dés</a> <a id="btn-stop" class="m-btn red"> Stop <i class="icon-white icon-remove-circle"></i></a>';

		// AJout dans la boite d'outils et display
		document.querySelector('#actionDiv').innerHTML = box;
		document.querySelector('#actionDiv').classList.add('show');

		// On attache les evenements
		document.querySelector('#btn-attack').onclick = sendAttackRequest;
		document.querySelector('#btn-stop').onclick = stopAttack;
	}

	function openMoveWindow(nbAttack, nbDef) {
		var box, i;

		// Creation de la boite
		box = 'Déplacement<div id="moveBox"><a id="btn-move-less" class="m-btn blue icn-only"><i class="icon-white icon-plus"></i></a>';

		box += ' <span>' + nbAttack + '</span>-<span>' + nbDef + '</span> ';
		box += '<a id="btn-move-more" class="m-btn blue icn-only"><i class="icon-white icon-plus"></i></a></div>';
		
		// Si c'est le dernier deplacement, il peut decider de ne pas en faire
		if (_gameState === EnumStates.Last_move)
			box += '<a id="btn-move-stop" class="m-btn green">En fait non</a> ';
		
		box += '<a id="btn-move-ok" class="m-btn green">OK</a>';
		
		// Ajout dans la boite d'outils et display
		document.querySelector('#actionDiv').innerHTML = box;
		document.querySelector('#actionDiv').classList.add('show');

		// On attache les evenements
		document.querySelector('#btn-move-less').onclick = addRemoveMove;
		document.querySelector('#btn-move-more').onclick = addRemoveMove;
		document.querySelector('#btn-move-ok').onclick = validMove;
		
		if (_gameState === EnumStates.Last_move)
			document.querySelector('#btn-move-stop').onclick = stopMove;
	}


	//------------------------------
	// 		ACTIONS CLIC
	//------------------------------
	function action_renfort(event) {
		// On recupere l'ID du territoire
		var place = getClickAttribute(event, 'data-uuid');
		
		// On bloque les commandes le temps de recevoir la réponse du serveur
		updateGameState(EnumStates.Wait);

		$.ajax({
			url: 'ajax/renforcement.php?game=' + _gameID + '&player=' + _playerID + '&place=' + place + '&action=add',
			dataType: 'json',
			success: onRenfReceive,
			error: notifyError
		});
	}

	function action_attaque(event) {
		// On recupere l'ID du territoire
		var place = getClickAttribute(event, 'data-uuid'),
			i, country;

		// On cache par securite
		document.querySelector('#actionDiv').classList.remove('show');
		
		// Si le territoire choisit appartient au joueur
		if (checkTerritoryOwnByPlayer(place)) {
			// Set du territoire "attaquant"
			_attackCountry = place;

			// On cache les anciens territoire "attaquables"
			hideSelectedCountries();

			// On parcours tout les territoires frontaliers
			for (i in _borders[place]) {

				// Si le territoire en question n'appartient pas au joueur, on le marque comem "attaquable"
				if (!checkTerritoryOwnByPlayer(_borders[place][i])) {
					country = document.querySelector('.country' + _borders[place][i].toString());
					country.setAttribute('class', country.getAttribute('class') + ' canAttack');
				}
			}
		}
		else {

			// Sinon si le territoire visee n'appartient pas au joueur mais que celui ci a deja un territoire d'attaque
			if (_attackCountry !== null) {
				// On check si le territoire visee est voisin du territoire d'attaque
				for (i in _borders[place]) {

					// Si oui, on peut afficher la petite fenetre d'attaque !
					if (_borders[place][i] === _attackCountry) {
						// On ne garde que le nouveau en surbrillance
						oldEnnemies =  document.querySelectorAll('.canAttack');
						for (i = 0; i < oldEnnemies.length; i++) {
							oldEnnemies[i].setAttribute('class', oldEnnemies[i].getAttribute('class').substr(0, oldEnnemies[i].getAttribute('class').indexOf('canAttack')));
						}
						document.querySelector('.country' + place.toString()).setAttribute('class', document.querySelector('.country' + place.toString()).getAttribute('class') + ' canAttack');

						_attackedCountry = place;
						attackWindow(_attackCountry, place);
					}
				}
			}
		}
	}

	function action_lastMove(event) {
		// On recupere l'ID du territoire
		var place = getClickAttribute(event, 'data-uuid'),
			i, country, oldEnnemies, nbA, nbB;

		// Si le territoire choisit appartient au joueur
		if (checkTerritoryOwnByPlayer(place)) {

			// Si aucun territoire n'avait été choisit, on prend celui ci comme base
			if (_attackCountry === null) {
				_attackCountry = place;
				_ownerTerritories = [];
				createOwnerTerritoryArray();
				linkOwnerTerritories(_attackCountry);
			}
			// Si on clique sur un teritoire sur lequel on peut donner
			else if (place != _attackCountry && document.querySelector('.country' + place.toString()).getAttribute('class').indexOf('canAttack') !== -1) {
				_attackedCountry = place;
				nbA = document.querySelector('#text' + _attackCountry.toString() + ' > tspan').textContent;
				nbB = document.querySelector('#text' + place.toString() + ' > tspan').textContent;
				
				// On stocke les unités au cas ou le joueur se ravise
				_placeA = nbA;
				_placeB = nbB;

				// Ouverture de la fenetre de deplacement
				openMoveWindow(nbA, nbB);
			}
			// Sinon on a cliqué dans une autre zone, non atteignable
			else {
				hideSelectedCountries();
				_attackCountry = place;
				_ownerTerritories = [];
				createOwnerTerritoryArray();
				linkOwnerTerritories(_attackCountry);
			}
		}
		else {
			// On cache les anciens territoire proches
			hideSelectedCountries();

			_attackCountry = null;
		}
	}

	function linkOwnerTerritories(line) {
		var i, div;

		for (var i = 0; i < _ownerTerritories[line].length; i++) {
			div = document.querySelector('.country' + _ownerTerritories[line][i].toString());

			// Si on a pas traité ce territoire, on le marque comme selectionnable et on appel cette meme fonction pour toutes ses relations
			if (div.getAttribute('class').indexOf('canAttack') === -1) {
				div.setAttribute('class', div.getAttribute('class') + ' canAttack');
				linkOwnerTerritories(_ownerTerritories[line][i]);
			}
		};

		// console.log('Fini');
	}
	
	

	function sendAttackRequest () {
		// On place le jeu en attente
		updateGameState(EnumStates.Wait);

		// Redondance tres laide a eviter. Pour le moment permet de rafraichir la petite fenetre de combat
		attackWindow(_attackCountry, _attackedCountry);

		$.ajax({
			url: 'ajax/attack.php?game=' + _gameID + '&player=' + _playerID + '&Aplace=' + _attackCountry + '&Dplace=' + _attackedCountry,
			dataType: 'json',
			success: onAttackResults,
			error: notifyError
		});

	}

	function stopAttack () {
		// L'attaque est stopee, on deselectionne tout
		var selected =  document.querySelectorAll('.canAttack');
		
		_attackCountry = _attackedCountry = null;

		for (i = 0; i < selected.length; i++) {
			selected[i].setAttribute('class', selected[i].getAttribute('class').substr(0, selected[i].getAttribute('class').indexOf('canAttack')));
		}

		document.querySelector('#actionDiv').classList.remove('show');
	}

	/*
	*	Envoie une requete de fin de tour au serveur. Si ok, on est redirige vers la page d'accueil,
	*	sinon on affiche une erreur
	*/
	function endTurn() {
		// On a finit, merci monsieur le serveur !
		$.ajax({
			url: 'ajax/endTurn.php?game=' + _gameID + '&player=' + _playerID,
			dataType: 'json',
			success: function (datas) {
				if (datas.error)
					displayMessage(datas.error, 'Ouuups !', 'error');
				else
					window.location.href = 'index.php';
			},
			error: notifyError
		});
	}

	function hideSelectedCountries() {
		var oldEnnemies = document.querySelectorAll('.canAttack'),
			i;
		
		for (i = 0; i < oldEnnemies.length; i++) {
			oldEnnemies[i].setAttribute('class', oldEnnemies[i].getAttribute('class').substr(0, oldEnnemies[i].getAttribute('class').indexOf('canAttack')));
		}
	}


	that.ClickFunction = function (e) {
		switch (_gameState) {
			case EnumStates.Renforcement:
				action_renfort(e);
				break;
			case EnumStates.Attack:
				action_attaque(e);
				break;
			case EnumStates.Last_move:
				action_lastMove(e);
				break;
		}
	};


	return (that);
}