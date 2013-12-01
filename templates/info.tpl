{include file='header.tpl'}

			<aside id="info-menu">
				<ul>
					<li><a href="#rules">Règles du jeu</a></li>
					<li><a href="#howToPlay">Comment jouer ?</a></li>
					<li><a href="#score">Système de classement</a></li>
					<li><a href="#infos">Infos générales</a></li>
					<li><a href="#bugs">Liste des bugs / améliorations</a></li>
				</ul>
			</aside>

			<section id="info-content">

				<article id="rules">
					<header>Règles du jeu</header>

					<p>
						<h3>But du jeu</h3>
						Bienvenue dans Global War !
					</p>
					<p>
						Global War est un jeu de stratégie au tour par tour dont le but est simple: vous devez annéantir vos adversaire !<br/>
					</p>
					<p>
						Chaque joueur dispose d'emplacements sur la carte. Plus vous avez d'emplacement, et plus ces derniers vous permettront de recevoir des troupes. Contrôler un continent entier (c'est à dire posséder tout les points d'une couleur de continent) vous fournira un bonus de troupe supplémentaire.<br/>
						Pour posséder de nouveaux territoires, vous devez attaquer un emplacement ennemi. Si vous gagnez, cet emplacement vous apprtiendra.<br/>
						En revanche si vous perdez tout vos territoires, vous perdez la partie ! 
					</p>
					<p>
						Un tour de jeu se définit en 3 étapes:
						<ul>
							<li>Placement des nouvelles unités</li>
							<li>Phase d'attaque</li>
							<li>Renforcement d'un territoire</li>
						</ul>
					</p>
					<p>
						<h3>1) Placement de nouvelles unités</h3>
						Au début de chaque tour, vous disposez d'au moins 3 unités. Plus vous possédez de territoire et plus vous aurez d'unités. 3 territoires vous octroient 1 unité supplémentaire. Un joueur qui possède de 12 à 14 territoires aura donc 4 unités à placer, s'il a 15 à 17 territoires il aura 5 unités à placer, etc...<br/>
						Vous pouvez placer autant d'unités que vous voulez sur un territoire qui vous appartient. Une fois toute vos unités placées, vous passez en phase 2: l'attaque.<br/>
						<strong>Bonus de continent: </strong>Occuper un continent entier vous raporte des unités en bonus. Pour en bénéficer, vous devez avoir au début de votre tour au moins une unitée sur chaque pays d'un continent. Ces derniers sont visible par couleur et rapporte un nombre différent de bonus attribué comme ceci:
						<ul>
							<li>Amérique du Nord (gris): +5 unités</li>
							<li>Amérique du Sud (rose): +2 unités</li>
							<li>Europe (bleu): +5 unités</li>
							<li>Afrique (orange): +3 unités</li>
							<li>Asie (vert): +7 unités</li>
							<li>Océanie (jaune): +2 unités</li>
						</ul>
					</p>

					<p>
						<h3>1-bis) Les bonus</h3>
						Lors de votre phases de renfort, et si vous avez gagné des territoires dans les précédents tours, un bouton "bonus" apparait. Il vous indique combien de troupes bonus sont actuellement disponibles. En cliquant sur ce bouton, vos renforts sont incrementés du nombre de bonus disponible.
					</p>
					<p>
						2 règles simples régissent les bonus:
						<ul>
							<li>Chaque territoire conquis <strong>vous rapporte un bonus</strong> pour le tour suivant</li>
							<li>Si vous ne consommez pas vos bonus lors de la phase de renforts, <strong>vous gagnez une unité bonus en plus</strong> pour le tour suivant</li>
						</ul>
					</p>

					<p>
						<h3>2) Attaque</h3>
						Vous pouvez attaquer autant de fois que vous le voulez lors de votre tour. Vous ne pouvez attaquer qu'un territoire dont la frontière est commune avec l'un de vos territoire. Attention: vous ne pouvez attaquer que si votre position possède au moins 2 unités.
					</p>
					<p>
						L'attaque est une simulation de lancer de dés, le nombre le plus fort l'emporte. L'attaquant peut jeter de 1 à 3 dés et la défense 1 ou 2 dés (selon le nombre d'unités présentent sur les territoires). En cas d'égalité, la victoire est donné à la défense. En cas de lancé de dés multiples (si l'attaque joue au moins 2 dés et si la défense joue aussi 2 dés), on oppose le meilleur score de l'attaque contre le meilleur score de la défense puis le 2ème meilleur score de l'attaque contre le second dés de la defense. 
					</p>
					<p>
						<strong>Exemple: </strong> Le territoire attaquant a 7 unités et le territoire attaqué en possède 3. L'attaque pourra donc lancer 3 dés et la défense 2. Le score de l'attaque est 5-1-4 et la défense fait 5-3. L'attaque perd une unité (5-5, avantage défense) et la défense perd aussi une unité (4-3).<br/>
						<strong>Exemple 2: </strong> Le territoire attaquant a 3 unités et le territoire attaqué en possède 2. L'attaque pourra donc lancer 2 dés et la défense 2. Le score de l'attaque est 3-3 et la defense fait 6-3. L'attaque perd 2 unités.<br/>
						<strong>Exemple 3: </strong> Le territoire attaquant a 2 unités et le territoire attaqué en possède 1. L'attaque pourra donc lancer 1 dés et la défense 1 également. L'attaque fait 5 et la défense fait 1. L'attaque gagne et prend le territoire attaqué.
					</p>
					<p>
						<h3>3) Renforcement d'un territoire</h3>
						Une fois que vous avez finit vos attaques, vous disposez d'un ultime déplacement. Ce dernier permet de renforcer un de vos territoires en transférant des troupes d'un territoire A vers un territoire B.
					</p>
					<p>
						Vous pouvez transferer des unités uniquement si un lien existe entre le territoire A et B (c'est à dire qu'un chemin de territoire en votre possession existe entre A et B). Vous devez laisser au moins une unité sur le territoire A ou B. Une fois le déplacement fait, votre tour de jeu se termine et c'est à vos adversaire de jouer. 
					</p>
				</article>

				<article id="howToPlay">
					<header>Comment jouer ?</header>
					<p>
						<h3>Vos parties en cours</h3>
						Toutes vos parties en cours sont listées sur la page d'accueil de Global War. Si la bordure est verte, c'est à vous de jouer. En revanche si elle est rouge c'est au tour de vos adversaires.
					</p>

					<p>
						<h3>Créer une partie</h3>
						Pour créer une nouvelle partie, rien de plus simple. Rendez vous sur votre page d'accueil Global War, puis cliquez sur le bouton "Nouvelle Partie". Une fenêtre avec la liste des joueurs présents s'affiche et vous permet de sélectionner les gens contre qui vous voulez jouer. Vous pouvez alors choisir de 1 à 3 personnes. 
					</p>
					<p>
						Quand vous êtes prêt, cliquez sur le bouton "Lancer la partie". La partie est alors rajoutée dans votre liste de jeu et est prête à jouer.
					</p>
					<p>
						<strong>Astuce:</strong> Vous partez en vacances ? Vous avez trop de parties en cours ou vous ne souhaitez pas jouer à une nouvelle partie ? Vous pouvez à tout moment changer votre disponibilitée en cliquant sur le bouton "Disponible" en haut à droite de l'écran. Si vous êtes indisponible, les autres joueurs ne vous voient plus et ne pourront plus vous inviter.
					</p>

					<p>
						<h3>Dans le jeu</h3>
						Vous pouvez prendre connaissance des règles du jeu dans la partie <a href="#rules">"Règles du jeu"</a>.<br/>
						De manière générale, tout se joue en cliquant sur la "pastille" d'un territoire (le rond de couleur avec un nombre à l'intérieur). Chaque territoire en possède une.<br/>
						La pastille donne 2 informations:
						<ul>
							<li>La couleur du joueur à qui appartient le territoire</li>
							<li>Le nombre d'unités présentes sur le territoire</li>
						</ul>
						Cliquer sur une pastille vous permet d'intérargir avec.
					</p>
					<p>
						<h3>Lors de la phase de renforts</h3>
						L'encart en haut à droite vous donne le nombre d'unités restantes à placer. Cliquer sur un territoire vous appartenant pour renforcer ce territoire d'une unité supplémentaire.
					</p>
					<p>
						Si vous avez des bonus à disposition, ces derniers apparaissent dans un bouton bleu. En cliquant dessus, vous choisissez de les prendre et ces bonus s'ajoutent à votre total de renfort.
					</p>
					<p>
						<h3>Lors de la phase d'attaque</h3>
						Lors de cette phase, vous allez devoir désigner un territoire attaquant puis sa cible.
					</p>
					<p>
						Cliquez sur n'importe quel territoire vous appartenant. Les territoires "attaquables" apparaissent alors en surbrillance. Choisissez-en un puis cliquez sur sa pastille: une fenêtre d'attaque s'ouvre alors. Dans cette fenêtre, vous pouvez voir les unités qui seront engagées dans le combat (de 1 à 3 pour l'attaque et de 1 à 2 pour la défense).
					</p>
					<p>
						Cliquez sur le bouton "Attaquer" pour lancer l'assaut. Le résultat du lancé de dés apparaitra dans la fenêtre d'attaque. Vert pour le lancé gagnant, rouge pour le perdant.
					</p>

					<p>
						<h3>Lors d'un déplacement</h3>
						Lorsque vous gagnez un nouveau territoire, vous avez la possibilitée de déplacer de 3 à X unités dessus (3 étant le minimum). Pour ce faire, l'encart d'attaque se transforme en encart de déplacement automatiquement apres une victoire.
					</p>
					<p>
						Si vous avez assez d'unités (plus de 3), vous pourrez alors choisir combien d'unitées envoyer sur le nouveau territoire et combien resteront sur le territoire attaquant en jouant avec les boutons "+".
					</p>

					<p>
						<h3>Lors de la phase dernier déplacement</h3>
						<strong>N'oubliez pas !</strong> Avant de passer la main à vos adversaires, vous avez la possibilitée de réaliser un dernier déplacement. Cette étape est falcutative, et vous ne pouvez déplacer des troupes que si le territoire source a au moins 2 unités.
					</p>
					<p>
						Le dernier déplacement s'opère de n'importe quel terrritoire vous appartenant vers un autre territoire <strong>si un lien existe entre eux.</strong> Un lien est simplement un chemin de territoires contigue vous appartenant allant de A vers B.
					</p>
					<p>
						Avant de cliquer sur le bouton rouge "Fin du tour", choisissez 2 territoires et opérez un déplacement comme vous le faites après une conquête de territoire. En validant votre choix, votre tour prend fin et c'est au joueur suivant de jouer.
					</p>

				</article>

				<article id="score">
					<header>Système de classement</header>
					<p>
						Chaque partie de Global Wars n'est pas anodine, car vous jouez vos propres points !
					</p>
					<p>
						La règle est simple: quand un joueur est éliminé, il donne un point de son propre stock à tous les joueurs encore vivants. Vous pouvez avoir un stock de points négatif, et ce stock est remis à égalité pour tous les joueurs chaque mois.
					</p>
					<p>
						Chaque mois, le joueur ayant cumulé le plus grand nombre de points est désigné "Joueur du mois". A cette occasion, tout les scores sont effacés et chaque joueur recommence le nouveau mois avec 0 point.
					</p>
					<p>
						<strong>Tips: </strong>refuser une partie ne vous fera pas perdre de points. Si vous abandonner sans avoir joué, vous ne perdez rien. En revanche quitter une partie en cours dans laquelle vous avez joué au moins une fois vous fera perdre directement 3 points.
					</p>
					<p>
						Afin d'illustrer le système de répartition de points, prenons un exemple concret. Nous partons du constat que tous les joueurs ont un même total de 0 point au début de la partie.

						<table class="-table _striped_ _hovered_">
							<thead>
								<tr>
									<th>Nombre de joueurs</th>
									<th>Premier</th>
									<th>Deuxième</th>
									<th>Troisième</th>
									<th>Dernier</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>4 joueurs</td>
									<td>3</td>
									<td>1</td>
									<td>-1</td>
									<td>-3</td>
								</tr>
								<tr>
									<td>3 joueurs</td>
									<td>2</td>
									<td>0</td>
									<td>-2</td>
									<td class="null">&#216;</td>
								</tr>
								<tr>
									<td>2 joueurs</td>
									<td>1</td>
									<td>-1</td>
									<td class="null">&#216;</td>
									<td class="null">&#216;</td>
								</tr>
							</tbody>
						</table>
					</p>
					<p>
						Même si les points sont remis à zéro chaque mois, vous pouvez toujours voir votre score global dans l'onglet "Classement global" de l'accueil.
					</p>
				</article>

				<article id="infos">
					<header>Infos générales</header>

					<p>
						<h3>Bienvenue sur Global Wars !</h3>
						Le jeu est en phase de bêta-test. Merci à tous pour votre aide (même si jouer n'est pas la partie la plus désagreable :p ). N'hésitez pas à remonter tout bug / anomalies que vous rencontreriez ainsi que toutes vos suggestions et vos idées. Pour ce faire c'est simple, il suffit de cliquer sur le petit invader en bas a gauche de chaque page et de décrire votre problème. Toute bonne idée d'amélioreration est la bienvenue !<br/>
						
						Bon jeu à tous et amusez vous bien ;) !
					</p>

				</article>

				<article id="bugs">
					<header>Liste des bugs / améliorations</header>

					<ul id="bugList">
						<li class="improvement"><span>&#x22;</span> Ajouter des animations lors de combats</li>
						<li class="improvement"><span>&#x22;</span> Voir les parties desquelles on a été éliminé</li>
						<li class="improvement"><span>&#x22;</span> Ajouter une petite tête de mort sur les gens éliminés</li>
						<li class="improvement"><span>&#x22;</span> Ajouter un mode multijoueur</li>
						<li class="improvement"><span>&#x22;</span> Mettre au propre le JS (Require.js)</li>
						<li class="improvement done"><span>&#x22;</span> Enrichir les statistiques</li>
						<li class="improvement done"><span>&#x22;</span> faire un vrai template de mail</li>
						<li class="bug done"><span>&#x66;</span> Correction du système de points</li>
					</ul>
				</article>


			</section>

	{include file='footer.tpl'}