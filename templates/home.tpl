{include file='header.tpl'}

			<section id="main-content">
				<h1>Parties en cours</h1>
				<div id="games">
					{if isset($Games)}
					{foreach from=$Games item=g}
					<a href="game.php?game={$g.id}" class="game-hidden">
					<!-- <a class="game-hidden"> -->
						<article class="{if $g.current == $User_id}is-my-turn{else}is-not-my-turn{/if}">
							<header>Contre {$g.opponents}</header>
							Depuis {$g.started}
							<br/>
							{foreach from=$g.players item=p}
							<img class="opponent-mini-pic {if $p.ID == $g.current}his-turn{/if}" src="{$p.Pic}" style="border-color: {$p.Color}" />
							{/foreach}
							<span class="stopGame" onClick="return (stopGame({$g.id}));">A</span>
						</article>
					</a>
					{/foreach}
					{else}
					Aucune partie pour le moment. Créez-en une !
					{/if}
				</div>

				<!-- <h1>Commencer une nouvelle partie</h1> -->
				<a href="#" onclick="searchForPlayers();" class="m-btn blue"><i class="icon-plus icon-white"></i> Nouvelle partie</a>

			</section>

			<aside id="rank-and-stat">
				<!-- Classement -->
				<section>
					<div id="owl-rank-slider" class="owl-carousel">
						<div>
							<h1>Podium</h1>
							{if (isset($Ranking)) && (count($Ranking) === 3)}
							<article class="podium pos-2 startState">
								<figure class="podium-pic">
									<img src="{$Ranking[1].Pic}" />
									<figcaption>{$Ranking[1].Nick}</figcaption>
								</figure>
								<div class="step">
									<strong>#2</strong>
									{$Ranking[1].Score} points
								</div>
							</article>
							<article class="podium pos-1 startState">
								<figure class="podium-pic">
									<img src="{$Ranking[0].Pic}" />
									<figcaption>{$Ranking[0].Nick}</figcaption>
								</figure>
								<div class="step">
									<strong>2</strong>
									{$Ranking[0].Score} points
								</div>
							</article>
							<article class="podium pos-3 startState">
								<figure class="podium-pic">
									<img src="{$Ranking[2].Pic}" />
									<figcaption>{$Ranking[2].Nick}</figcaption>
								</figure>
								<div class="step">
									<strong>#3</strong>
									{$Ranking[2].Score} points
								</div>
							</article>

							{else}
							Pas encore de classement
							{/if}
						</div>
						<div>
							<h1>Classement</h1>
							{if isset($RankingTable)}
							{foreach from=$RankingTable item=rt}
								<a class="rank-pos" href="#stats-title" onClick="loadPlayerStatistics({$rt['id']}, '{$rt['nick']}');"><span class="rank-pos-position">#{$rt['pos']}</span> <span class="rank-pos-nick">{$rt['nick']}</span> <span class="rank-pos-score">{$rt['score']} points</span> ({$rt['nbGamesEnded']} parties terminées, {$rt['nbGamesPending']} en cours)</a>
							{/foreach}
							{else}
							Pas encore de classement
							{/if}
						</div>
						<div>
							<h1>Classement global</h1>
							{if isset($GlobalRankingTable)}
							{foreach from=$GlobalRankingTable item=rt}
								<a class="rank-pos" href="#stats-title" onClick="loadPlayerStatistics({$rt['id']}, '{$rt['nick']}');"><span class="rank-pos-position">#{$rt['pos']}</span> <span class="rank-pos-nick">{$rt['nick']}</span> <span class="rank-pos-score">{$rt['score']} points</span> ({$rt['nbGamesWin']} parties gagnées pour {$rt['nbGamesEnded']} jouées)</a>
							{/foreach}
							{else}
							Pas encore de classement
							{/if}
						</div>
						<div>
							<h1>Joueur du mois</h1>
							{if isset($PlayerOfTheMonth)}
							<img class="rank-playerOfTheMonth-pic" src="{$PlayerOfTheMonth['pic']}" />
							<p class="rank-playerOfTheMonth-text">Le joueur du mois est <strong>{$PlayerOfTheMonth['nick']}</strong> avec {$PlayerOfTheMonth['score']} points</p>
							<p class="rank-playerOfTheMonth-text">Félicitations !</p>
							{else}
							Pas de joueur du mois pour le moment
							{/if}
						</div>
					</div>

				</section>

				<!-- Statistiques -->
				<section id="stats">
					<h1 id="stats-title">Statistiques</h1>

					<div id="owl-stat-slider" class="owl-carousel">
						{if isset($RankingPie) || isset($RankingEvolution) || isset($ColorPie)}
						
							{if isset($RankingPie)}
							<article class="stat-container stat-container-pie">
								<header>Classement</header>
								<figure class="stat-entity">
									<canvas id="canvas-ranking-pie" height="175" width="175"></canvas>
									<figcaption>{$RankingPie.legend}</figcaption>
								</figure>
								<script>
									var canvasRankingPie = new Chart(document.getElementById("canvas-ranking-pie").getContext("2d")).Pie({$RankingPie.values});
								</script>
							</article>
							{/if}
							
						
							{if isset($RankingEvolution)}
							<article class="stat-container stat-container-line">
								<header>Ratio de victoires</header>
								<figure class="stat-entity">
									<canvas id="canvas-ranking-evol" height="200" width="465"></canvas>
								</figure>
								<script>
									var lineChartData = {
											labels : {$RankingEvolution.label},
											datasets : [
												{
													fillColor : "rgba(151,187,205,0.5)",
													strokeColor : "rgba(151,187,205,1)",
													pointColor : "rgba(151,187,205,1)",
													pointStrokeColor : "#fff",
													data : {$RankingEvolution.values}
												}
											]
										};

									var RankingEvolution = new Chart(document.getElementById("canvas-ranking-evol").getContext("2d")).Line(lineChartData);
								</script>
							</article>
							{/if}

							{if isset($ColorPie)}
							<article class="stat-container stat-container-pie">
								<header>Couleur favorite</header>
								<figure class="stat-entity">
									<canvas id="canvas-color-pie" height="175" width="175"></canvas>
								</figure>
								<script>
									var cPieData = {$ColorPie};
									var canvasColorRankingPie = new Chart(document.getElementById("canvas-color-pie").getContext("2d")).Pie(cPieData);
								</script>
							</article>
							{/if}
						
					{else}
						<div>Vos statistiques apparaitront ici après quelques combats !</div>					
					{/if}
					</div>					
				
				</section>

			</aside>

			<div class="-modal js-modal">
				<div class="-modal-header">Choisissez vos adversaires<i class="-closer">×</i></div>
				<div class="-modal-content" id="modal-players">
					<div id="playerList">

					</div>

					<footer>
						<div id="infoText"><span>3</span> places restantes</div>
						<a href="#" onclick="createNewGame();" id="btn-create-game" class="m-btn green"><i class="icon-white icon-check"></i> Lancer la partie</a>
					</footer>
				</div>
			</div>

			<div class="-modal js-modal-stopGame">
				<div class="-modal-header">Abandonner une partie<i class="-closer">×</i></div>
				<div class="-modal-content" id="stopGame-content">
					<h3>Etes-vous sur de vouloir abandonner cette partie ?</h3>
					<p>Si vous quittez une partie sans avoir joué, vous ne perdez aucun points.<br/>En revanche si vous quittez après joué au moins un coup, c'est un abandon pour lequel vous perdrez 3 points.</p>

					<a href="#" id="abandonButton" class="m-btn red"><i class="icon-white icon-flag"></i> Quitter la partie</a>
					<a href="#" onclick="$('.js-modal-stopGame').data('kit-modal').close();" class="m-btn green"><i class="icon-white icon-ban-circle"></i> Annuler</a>
					
				</div>
			</div>
			
			<script type="text/javascript">

				var gl_User = {$User_id};

				$(document).ready(function() {
					/* Fenetre de creation de partie */
					$('.js-modal').modal({
						animation: 'blurIn'
					});

					/* Fenetre d'abandon de partie' */
					$('.js-modal-stopGame').modal({
						animation: 'blurIn'
					});

					// Plugin slider statistiques
					$('#owl-rank-slider').owlCarousel({
						/*navigation : true,*/
						slideSpeed : 300,
						paginationSpeed : 400,
						singleItem: true,
						autoHeight : true,
						transitionStyle : 'goDown'
					});

					// Si des stats sont a afficher
					if ($('#stats')) {
						$('#owl-stat-slider').owlCarousel({
							/*navigation : true,*/
							slideSpeed : 300,
							paginationSpeed : 400,
							singleItem: true,
							autoHeight : true,
							transitionStyle : 'backSlide',
							autoPlay : 5000,
							stopOnHover : true
						});
					}

					// Afficher les parties en cours
					var gameList, podium,
						i = 0;

					function showGames() {
						gameList = document.querySelector('.game-hidden');
						if (gameList) {
							window.setTimeout(function () {
								gameList = document.querySelector('.game-hidden');
								if (gameList) {
									gameList.classList.remove('game-hidden');
									showGames();
								}
							}, 200);
						}
					}

					showGames();


					// Afficher le classement
					podium = document.querySelectorAll('.startState');
					for (i in podium)
						if (podium[i].classList)
							podium[i].classList.remove('startState');



					/* TEMP: Sereur messages */
					displayMessage('Félicitations à Hugues pour sa première place ! (Gros fourbe)', 'Joueur du mois');

					displayMessage("Vous ne l'attendiez plus, mais la voilà: la mise à jour des statistiques !<br/>Vous pouvez maintenant voir les stats des gens ainsi que le classement du mois, le global et plein d'autres choses encore ;)", 'Mise à jour !');
					/* /TEMP: Sereur messages */
				});

			</script>

		<!-- </div> END Div Page  -->

	{include file='footer.tpl'}