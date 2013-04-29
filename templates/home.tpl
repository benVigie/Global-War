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
				<section id="rank">
					<h1>Classement</h1>

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

					{if isset($OutOfRank)}
					<footer id="outOfRanking">
						<strong>Votre position:</strong> {$OutOfRank}
					</footer>
					{/if}

					{else}
					Pas encore de classement
					{/if}

				</section>

				<!-- Statistiques -->
				{if isset($RankingPie) || isset($RankingEvolution)}
				<section id="stats">
					<h1>Statistiques</h1>

					
					{if isset($RankingPie)}
					<article class="stat-container stat-container-pie">
						<header>Mes scores</header>
						<figure class="stat-entity">
							<canvas id="canvas-ranking-pie" height="175" width="175"></canvas>
							<figcaption>{$RankingPie.legend}</figcaption>
						</figure>
						<script>
							var pieData = [{$RankingPie.values}];
							var canvasRankingPie = new Chart(document.getElementById("canvas-ranking-pie").getContext("2d")).Pie(pieData);
						</script>
					</article>
					{/if}
					
					{if isset($RankingEvolution)}
					<article class="stat-container stat-container-line">
						<header>Ratio de victoires</header>
						<figure class="stat-entity">
							<canvas id="canvas-ranking-evol" height="200" width="600"></canvas>
						</figure>
						<script>
							var lineChartData = {
									labels : [{$RankingEvolution.label}],
									datasets : [
										{
											fillColor : "rgba(151,187,205,0.5)",
											strokeColor : "rgba(151,187,205,1)",
											pointColor : "rgba(151,187,205,1)",
											pointStrokeColor : "#fff",
											data : [{$RankingEvolution.values}]
										}
									]
								};

							var RankingEvolution = new Chart(document.getElementById("canvas-ranking-evol").getContext("2d")).Line(lineChartData);
						</script>
					</article>
					{/if}


				</section>
				{/if}

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
				});

			</script>

		<!-- </div> END Div Page  -->

	{include file='footer.tpl'}