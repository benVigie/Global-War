		<!-- TEMPORAIRE -->
		<div class="-modal js-modal-report">
			<div class="-modal-header">Ho my god ! A bug !!!<i class="-closer">×</i></div>
			<div class="-modal-content" id="bug-report-content">
				Merci de m'aider !<br/>
				Pour que ce soit le plus efficace possible, n'hesitez pas a donner un max d'informations !
				<form>
					<label>Sujet de la requête</label>
					<select name="bug-type" id="bug-type">
						<option value="">Bug graphique / affichage</option>
						<option value="">Bug de jeu</option>
						<option value="">Connexion / profil</option>
						<option value="">Un truc qui serait bien...</option>
						<option value="">Réclamation !</option>
						<option value="">Autre</option>
					</select>

					<label>C'est grave docteur ?</label>
					<select name="bug-level" id="bug-level">
						<option value="">Mega important !</option>
						<option value="">Un peu quand meme...</option>
						<option value="">Non pas du tout mais j'aime bien faire chier</option>
					</select>

					<label>Description</label>
					<textarea id="bug-message"></textarea>

					<a href="#" onclick="sendBugReport();" class="m-btn blue"><i class="icon-envelope icon-white"></i> Envoyer</a>
				</form>
			</div>
		</div>

		<div class="-modal js-modal-change-picture">
			<div class="-modal-header">Changement de sa photo de profil<i class="-closer">×</i></div>
			<div class="-modal-content" id="bug-report-content" style="min-height: 130px;">
				<form method="post" action="home.php" enctype="multipart/form-data" id="changePic">
					<div class="uploader" id="uniform-fileInputS" style="display: block;margin-bottom: 20px;">
						<input type="file" name="newPic" class="fileInput" id="fileInputS" style="opacity: 0; " />
						<span class="filename">Photo</span>
						<span class="action">Choose File</span>
					</div>
					
					<a href="#" onclick="document.querySelector('#changePic').submit();" class="m-btn blue"><i class="icon-check icon-white"></i> Changer ma photo !</a>
				</form>
			</div>
		</div>

		<script type="text/javascript">
			/* TEMP: fenetre de bug */
			$('.js-modal-report').modal({
				animation: 'blurIn'
			});
			$('.js-modal-change-picture').modal({
				animation: 'blurIn'
			});

			function sendBugReport() {

				// Envoie du rapport
				$.ajax({
					url: 'ajax/bugReport.php',
					type: 'POST',
					data: {
						cause: $('#bug-type option:selected').text(),
						importance: $('#bug-level option:selected').text(),
						rapporteur: document.querySelector('.user-nick').innerHTML,
						email: document.querySelector('.user-email').innerHTML,
						message: $('#bug-message').val()
					},
					success: function(data) {
						// Fermeture de la fenetre
						$('.js-modal-report').data('kit-modal').close();
						
						// Affichage d'un petit message
						displayMessage('Merci de votre aide :) !', 'Rapport envoyé');
					},
					error: notifyError
				});

			}
		</script>
		<!-- / TEMPORAIRE -->

		<footer id="pageFooter">
			<div class="footer-column">
				<header><span>f</span> Reporter un bug</header>
				<span id="footer-bug" onclick="$('.js-modal-report').data('kit-modal').open();">b</span>
			</div>
			<div class="footer-column">
				<header><span>e</span> Global War</header>
				<ul>
					<li><a href="info.php#rules">Règles du jeu</a></li>
					<li><a href="info.php#howToPlay">Comment jouer ?</a></li>
					<li><a href="info.php#score">Système de classement</a></li>
				</ul>
			</div>
			<div class="footer-column">
				<header><span>i</span> Informations</header>
				<ul>
					<li><a href="info.php#infos">Infos générales</a></li>
					<li><a href="info.php#bugs">Liste des bugs</a></li>
				</ul>
			</div>
			<div class="footer-column">
				<header><span>p</span> A propos</header>
				<ul>
					<li>Jeu fait avec amour par <a id="footer-mailto" href="mailto:vigie.benjamin@outlook.com">Ben</a></li>
					<li>Soutenez Global War, <a href="http://www.youtube.com/watch?v=ZzIAiSGKxQc">achetez le tee-shirt officiel</a></li>
				</ul>
			</div>
		</footer>

	</body>
</html>