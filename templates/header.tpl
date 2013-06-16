<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>{$Title}</title>
		<link rel="shortcut icon" href="images/favicon.ico" />

		<!-- CSS -->
		<link href="css/shared.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="css/m-buttons.css" type="text/css" />
		<link rel="stylesheet" href="css/m-icons.css" type="text/css" />
		{if $Page === 'game.tpl'}
		<link rel="stylesheet" type="text/css" href="css/svg.css" />
		{/if}
		{if $Page === 'info.tpl'}
		<link rel="stylesheet" type="text/css" href="css/infos.css" />
		{/if}
		
		<!-- Maxmertkit css -->
		<link rel="stylesheet" type="text/css" href="css/maxmertkit/maxmertkit-components.css">
		<link rel="stylesheet" type="text/css" href="css/maxmertkit/maxmertkit-animation.css">
		
		<link rel="stylesheet" href="css/home.css" type="text/css" />
		
		<!-- JavaScript -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
		<script type="text/javascript" src="js/tools.js"></script>
		
		{if $Page === 'home.tpl'}
		<script type="text/javascript" src="js/home.js"></script>
		<script type="text/javascript" src="js/chart/Chart.min.js"></script>
		{/if}
		
		{if $Page === 'game.tpl'}
		<script src="js/raphael-min.js" charset="utf-8" ></script>
		<script src="js/game-engine.js" charset="utf-8" ></script>
		<script src="js/svg.js" charset="utf-8" ></script>
		{/if}

		<!-- Maxmertkit javascript -->
		<script type="text/javascript" src="js/maxmertkit/modernizr.js"></script>
		<script type="text/javascript" src="js/maxmertkit/maxmertkit.js"></script>
		<script type="text/javascript" src="js/maxmertkit/maxmertkit.notify.js"></script>
		<script type="text/javascript" src="js/maxmertkit/maxmertkit.modal.js"></script>
		
	</head>

	<body>
		
		<header>
			<a href="index.php"><img src="images/logo2.png" alt="Global Wars"/></a>

			<aside id="user-infos">
				<div>
					<strong class="user-nick">{$User_nick}</strong>
					<span class="user-email user-fade-effect" onBlur="updateEmail();" contenteditable>{$User_email}</span>
					<a onClick="toggleAvailability();" class="user-availability user-fade-effect" data-availability="{$User_availability}">Disponible: <i></i></a>&nbsp;&nbsp;-&nbsp;&nbsp; 
					<a onClick="toggleNotif();" class="user-notif user-fade-effect" data-notif="{$User_notif}">Notifications: <i></i></a>&nbsp;&nbsp;-&nbsp;&nbsp; 
					<a href="home.php?logout" class="user-fade-effect user-change-color">Déconnexion</a>
				</div>
				<img class="user-picture" src="{$User_pic}" onclick="$('.js-modal-change-picture').data('kit-modal').open();" alt="profil picture" />
			</aside>
		</header>