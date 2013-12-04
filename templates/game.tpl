
{include file='header.tpl'}

			<!-- Liste et infos des joueurs -->
			<h1>Joueurs</h1>
			<div class="-dropdown -primary- _top_ colorWindow" style="min-height: 30px;">
				<div class="-arrow"></div>
				<div class="colorBox" onClick="changeColorTo('#875643');"><span style="background: #875643"> </span></div>
				<div class="colorBox" onClick=""><span > </span></div>
			</div>

			<section id="PlayersInGame"></section>

			<section id="map-container">
				<div id="map">
					<div id="actionDiv"></div>
					<div id="gameState"></div>
				</div>
			</section>

			<h1>Messages</h1>
			<section id="chat">

				<article id="sender">
					<h2>Ecrire</h2>
					<textarea></textarea>
					<footer>
						<a href="#chat" onClick="SendChatMessage();" class="m-btn"><i class="icon-envelope"></i> Envoyer</a>
					</footer>
				</article>

			</section>

		<script type="text/javascript">
			var _gameEngine = null;

			function displayPlayers(infos) {
				var i,
					playerDiv,
					divClass,
					logedPlayer;

				for (i in infos.playerList) {
					// Check si ce joueur est le joueur connecté
					logedPlayer = (infos.playerList[i].id == {$User_id}) ? true : false;

					// On attribue les classes qui vont bien
					divClass = 'class="' + ((logedPlayer) ? 'myPlayerPanel ' : '') + ((infos.playerList[i].id === infos.currentPlayer.toString()) ? 'currentPlayer' : '') + '" ';

					// Creation de l'encart joueur
					playerDiv = '<section id="player' + infos.playerList[i].id
									+ '" data-playerColor="' + infos.playerList[i].color
									+ '" style="border-color:' + infos.playerList[i].color
									+ ';" ' + divClass
									+ '><div class="playerBasicInfos"><img src="' + infos.playerList[i].pic
									+ '"></div><div class="infosPlayer"><strong>' + infos.playerList[i].nick + '</strong>Unités: <span id="unitsPlayer' + infos.playerList[i].id
									+ '">0</span><br/>Renforts: <span id="renfortsPlayer' + infos.playerList[i].id
									// + '">0</span></div></section>';
									
									+ '">0</span></div>'
									+ ((logedPlayer) ? '<aside id="color-tooltip" onClick="toggleColorTooltip();">!</aside>' : '')
									+ '</section>';
					document.querySelector('#PlayersInGame').innerHTML += playerDiv;

					// Si on a notre joueur connecté, placement du panneau de choix de couleur
					if (logedPlayer)
						document.querySelector('.colorWindow').classList.add('colorWindowPanel' + i.toString());
				}
			}

			window.onload = function() {
				// TODO: vérifier qu'on ai bien les variables {$GameID} et {$User_id}
				var map = MapSVG();

				_gameEngine = GameEngine({$GameID}, {$User_id}, map);

				// On dessine la map
				map.CreateMap(_gameEngine.ClickFunction);

				// Recuperation des joueurs et de la map tel que laisse par le joueur
				_gameEngine.GetPlayersInfos(displayPlayers);
				_gameEngine.LoadMap(map.AssignMap);

				if (_gameEngine.IsPlayerTurn() === true)
					_gameEngine.StartPlayerTurn(_gameEngine.LaunchScenario);

				getChatMessages();
			};


			/*
			*	Fonctions pratiques
			*/
			function insertChatMessage(message, userID, date) {
				var player_color = document.querySelector('#player' + userID).getAttribute('data-playerColor'),
					player_pic = document.querySelector('#player' + userID + ' .playerBasicInfos > img').getAttribute('src'),
					player_nick = document.querySelector('#player' + userID + ' .infosPlayer > strong').innerHTML,
					chatList = document.querySelectorAll('#chat article'),
					entry;

				// Creation du message
				entry = document.createElement('article');
				entry.innerHTML = '<header><img src="' + player_pic + '" class="chat-pic" /> ' + player_nick + ' <time>Il y a ' + date + '</time></header>' + message + '</article>';
				entry.setAttribute('style', ('border-left: 4px solid ' + player_color));

				// Insertion dans la liste des messages
				if (chatList.length === 1)
					document.querySelector('#chat').appendChild(entry);
				else
					document.querySelector('#chat').insertBefore(entry, chatList[1]);
			}

			function SendChatMessage() {
				var message = document.querySelector('textarea').value;
				
				// Si rien n'est ecrit, on se casse
				if (message === '')
					return;

				// Sinon on poste le message
				$.ajax({
					url: 'ajax/chat.php?game={$GameID}&message=' + message,
					dataType: 'json',
					success: function (data) {
						var message = document.querySelector('textarea'),
							player_id = {$User_id};

						// Check error
						if (data.error) {
							displayMessage(data.error, 'Ouuups !', 'error');
							return;
						}

						insertChatMessage(message.value, player_id, "quelques instants");
						
						// Mise au propre du message
						message.value = '';
					}
				});
			}

			function getChatMessages() {
				$.ajax({
					url: 'ajax/chat.php?game={$GameID}&get=all',
					dataType: 'json',
					success: function (data) {
						if (!data.chat) {
							displayMessage('Impossible de charger le chat...', 'Ouuups !', 'error');
							return;
						}

						for (var i in data.chat)
							insertChatMessage(data.chat[i].message, data.chat[i].player, data.chat[i].date);
					}
				});
			}

			function toggleColorTooltip() {
				var colorBox = document.querySelector('.colorWindow'),
					colorTooltipOpen = colorBox.classList.contains('active');

				if (!colorTooltipOpen) {
					// Requetes get couleur
					$.ajax({
						url: 'ajax/gameColors.php?game={$GameID}',
						dataType: 'json',
						success: function (data) {
							if (!data.colors) {
								displayMessage('Impossible de récupérer la liste des couleurs', 'C\'est embêtant...', 'error');
								return;
							}

							colorBox.innerHTML = '<div class="-arrow"></div>';
							for (var i in data.colors) {
								colorBox.innerHTML += '<div class="colorBox" onClick="changeColorTo(\'' + data.colors[i] + '\');"><span style="background: ' + data.colors[i] + '"> </span></div>';
							}

							colorBox.style.top = (190 - (30 * (data.colors.length - 1))) + 'px';
						}
					});

					// on ouvre la boite
					colorBox.classList.add('active');
				}
				else
					colorBox.classList.remove('active');
			}

			function changeColorTo(color) {
				$.ajax({
						url: 'ajax/gameColors.php?game={$GameID}&setColor=' + color.substring(1),
						dataType: 'json',
						success: function (data) {
							if (!data.colorUpdated) {
								displayMessage('Impossible de changer la couleur', 'C\'est embêtant...', 'error');
								return;
							}

							updatePlayerColor(data.colorUpdated);
						}
					});

				// On ferme la fenetre des couleurs
				document.querySelector('.colorWindow').classList.remove('active');
			}

			function updatePlayerColor(newColor) {
				var oldPlayerColor = document.querySelector('#player{$User_id}').getAttribute('data-playercolor'),
					pastilles, messages, i;

				// Update de l'encart des joueurs
				document.querySelector('#player{$User_id}').setAttribute('data-playercolor', newColor);
				document.querySelector('#player{$User_id}').setAttribute('style', 'border-color:' + newColor + ';');

				// Update des pastilles de jeu
				pastilles = document.querySelectorAll('.svg-pastille');
				for (i = 0; i < pastilles.length; i++) {
					if (pastilles[i].getAttribute('fill') && (pastilles[i].getAttribute('fill') == oldPlayerColor))
						pastilles[i].setAttribute('fill', newColor)
				};

				// Update du chat
				messages = document.querySelectorAll('#chat article');
				for (i = 0; i < messages.length; i++) {
					if (messages[i].getAttribute('style') && (messages[i].getAttribute('style').substring(23) == oldPlayerColor))
						messages[i].setAttribute('style', 'border-left: 4px solid ' + newColor)
				};

				// On change la coueur du jeu
				_gameEngine.ChangePlayerColor(newColor);
			}

		</script>

	{include file='footer.tpl'}