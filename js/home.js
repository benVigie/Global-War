function togglePlayer () {
	var alreadyChecked, text,
		nbSelected = document.querySelectorAll('.selected').length,
		playerId = this.getAttribute('data-uuid');
	
	// console.log(playerId);
	// Check si on l'a deja selectionne
	alreadyChecked = this.classList.contains('selected');
	if (alreadyChecked) {
		this.classList.remove('selected');
		nbSelected--;
	}
	else if (nbSelected < 3) {
		this.classList.add('selected');
		nbSelected++;
	}

	// Update text
	nbSelected = 3 - nbSelected;
	if (nbSelected === 0)
		text = 'OK on est complet !';
	else {
		text = '<span>' + nbSelected + '</span> place';
		text += (nbSelected > 1) ? 's restantes' : ' restante';
	}
	document.querySelector('#infoText').innerHTML = text;
}

function parsePlayers (data) {
	var i, html = '';

	if (data.error)
		displayMessage(data.error, 'Ouuups !', 'error');

	for (var i = 0; i < data.length; i++) {
		html += '<div class="selectablePlayer" data-uuid="' + data[i]['player_id'] + '"><img src="' + data[i]['player_picture'] + '" /><span>' + data[i]['player_nick'] + '</span></div>'
	}

	document.querySelector('#playerList').innerHTML += html;

	$('.selectablePlayer').click(togglePlayer);

	// Ouverture de la fenetre de selection
	$('.js-modal').data('kit-modal').open();
}

function createGameResponse (data) {
	var game, i, nbGames,
		opponents = '', miniPic = '<br/>';

	if (data.error) {
		displayMessage(data.error, 'Ouuups !', 'error');
		return;
	}

	console.log(data);

	// Creation de la fenetre de jeu
	game = '<a href="game.php?game=' + data.game + '" class="game-hidden">';
	game += '<article class="' + ((data.current == gl_User) ? 'is-my-turn' : 'is-not-my-turn') + '">';
	game += '<header>Contre ';
	for (i in data.players) {
		if (data.players[i].id != gl_User)
			opponents += ((opponents == '') ? '' : ', ') + data.players[i].nick;

		miniPic += '<img class="opponent-mini-pic ' + ((data.players[i].id == data.current) ? 'his-turn' : '') + '" src="' + data.players[i].pic + '" style="border-color: ' + data.players[i].color + '" />'
	}
	game += opponents + '</header>Depuis quelques instants';
	game += miniPic + '<span class="stopGame" onClick="return (stopGame(' + data.game + '));">A</span></article></a>';

	// Affichage dans la zone des parties
	nbGames = document.querySelectorAll('#main-content > div a').length;
	if (nbGames === 0)
		document.querySelector('#main-content > div').innerHTML = game;
	else
		document.querySelector('#main-content > div').innerHTML += game;
	document.querySelector('.game-hidden').classList.remove('game-hidden');

	// Fermeture de la fenetre modal
	$('.js-modal').data('kit-modal').close();
}

function notifyError (jqXHR, textStatus) {
	console.log('Raison invoquee: [' + textStatus + ']');
	displayMessage(textStatus, 'Ouuuups !', 'error');
}

function searchForPlayers() {

	// reset old list
	document.querySelector('#playerList').innerHTML = '';

	$.ajax({
		url: 'ajax/getPlayers.php',
		dataType: 'json',
		success: parsePlayers,
		error: notifyError
	});
}

function createNewGame() {
	var list = document.querySelectorAll('.selected'),
		nbPlayers = list.length,
		players = [],
		i;

	if (nbPlayers < 1) {
		displayMessage("Il faut au moins 2 joueurs pour créer une partie.<br/>Je devrai pas avoir à l'expliquer ça :/ ...", 'Serieusement ?...', 'warning');
		return;
	}
	else {
		for (i = 0; i < nbPlayers; i++) {
			players.push(list[i].getAttribute('data-uuid'));
		}
	}

	$.ajax({
		url: 'ajax/createNewGame.php',
		data: {'players': JSON.stringify(players)},
		dataType: 'json',
		success: createGameResponse,
		error: notifyError
	});
}

function stopGame(idGame) {
	$('.js-modal-stopGame').data('kit-modal').open();

	document.querySelector('#abandonButton').setAttribute('onClick', 'onValidStopGame(' + idGame + ');');

	return (false);
}

function onValidStopGame(gameID) {
	var gamesList, ganeContainer, i;

	$.ajax({
		url: 'ajax/quitGame.php?game=' + gameID,
		dataType: 'json',
		success: function (data) {
			if (data.error)
				displayMessage(data.error, 'Ouuups !', 'error');
			else if (data.free)
				displayMessage(data.free, 'Partie quitée', 'info');
			else if (data.lossePoints)
				displayMessage(data.lossePoints, 'Mauvais joueur va !', 'warning');
			else
				displayMessage('Une couille dans le potage...', 'Ouuups !', 'error');

			// On ferme la fenetre
			$('.js-modal-stopGame').data('kit-modal').close();

			// On supprime le jeu
			gamesList = document.querySelectorAll('#games a');
			ganeContainer = document.querySelector('#games');

			ganeContainer.innerHTML = ''
			for (var i in gamesList) {
				if ((gamesList[i].getAttribute) && (gamesList[i].getAttribute('href') !== 'game.php?game=' + gameID))
					ganeContainer.appendChild(gamesList[i]);
			};
		},
		error: notifyError
	});
}