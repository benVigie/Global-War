<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>{$Title}</title>
	<link rel="shortcut icon" href="images/favicon.ico" />

	<link href="css/shared.css" rel="stylesheet" type="text/css" />
	<link href="css/login.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="css/m-buttons.css" type="text/css" />
	<link rel="stylesheet" href="css/m-icons.css" type="text/css" />

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
	<script type="text/javascript" src="js/tools.js"></script>
</head>

<body>

	<section id="loginContainer">
	   <img src="images/logo.png" alt="global war" id="login-logo" />
		
		<!-- Login wrapper begins -->
		<div id="loginWrapper">
			<!-- Current user form -->
			<form method="post" action="index.php" id="login">
				
				<input type="text" name="nick" placeholder="Pseudo" class="loginUsername" />
				<input id="remember" type="checkbox" name="remember" value="on" style="display: none;" />
				<input type="password" name="password" placeholder="Mot de passe" class="loginPassword" id="logPass" onkeyup="revealRemember()" />
				<div id="remember-box" onClick="changeRememberState();">
					<span id="remember-icon"></span> Se souvenir de moi !
				</div>

				<div class="logControl">
					<!-- <input type="submit" name="submit" value="Connection" class="buttonM bBlue" /> -->
					<a href="#" class="m-btn blue" onclick="login();">Connection</a>
					<a href="#" class="m-btn" onclick="toggleFlip();">Inscription <i class="icon-chevron-right"></i></a>
				</div>
			</form>
			
			<!-- New user form -->
			<form method="post" action="index.php" id="newAccount" enctype="multipart/form-data">	
				<input type="text" name="nick" placeholder="Pseudo" class="loginUsername" />
				<input type="text" name="email" placeholder="Email" class="loginEmail" />
				<input type="password" name="password" placeholder="Mot de passe" class="loginPassword" />
				<div class="uploader" id="uniform-fileInputS">
					<input type="file" name="picture" class="fileInput" id="fileInputS" style="opacity: 0; " />
						<span class="filename">Photo</span>
						<span class="action">Choose File</span>
				</div>
				
				<div class="logControl">
					<a href="#" onClick="newAccount();" class="m-btn blue"><i class="icon-white icon-plus"></i> Créer compte</a>
					<a href="#" class="m-btn icn-only" onclick="toggleFlip();"><i class="icon-chevron-left"></i></a>
				</div>
			</form>
		</div>
		<!-- Login wrapper ends -->
	</section>

	<script type="text/javascript">
		function toggleFlip() {
			var div = document.querySelector('#loginWrapper');
			div.classList.toggle('flipped');
		}

		function login() {
			document.querySelector('#login').submit();
		}

		function newAccount() {
			document.querySelector('#newAccount').submit();
		}

		function changeRememberState() {
			if ($('#remember').is(":checked")) {
				$('#remember').attr("checked", false);
			}
			else {
				$('#remember').attr("checked", true);
			}

			document.querySelector('#remember-icon').classList.toggle('remember-select');
		}

		function revealRemember() {
			document.querySelector('#remember-box').classList.add('reveal');

			// Remove event
			$('#logPass').attr('onkeyup', '');
		}
	</script>

</body>
</html>
