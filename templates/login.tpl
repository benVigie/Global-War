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
	<script type="text/javascript" src="js/plugins/jquery.jgrowl.js"></script>
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
				<input type="password" name="password" placeholder="Mot de passe" class="loginPassword" />
				
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
					<!-- <input type="submit" name="submit" value="Créer compte" class="buttonM bBlue" /> -->
					<a href="#" onClick="newAccount();" class="m-btn blue"><i class="icon-white icon-plus"></i> Créer compte</a>
					<a href="#" class="m-btn icn-only" onclick="toggleFlip();"><i class="icon-chevron-left"></i></a>
					<!-- <a href="#" onclick="toggleFlip();">Connection</a> -->
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

		{if isset($Message)}
		window.onload = function() {
		setTimeout(function(){
				displayMessage("{$Message}"{if isset($Message_error)}, {$Message_error}{/if});
			},200);
		}
		{/if}
	</script>

</body>
</html>
