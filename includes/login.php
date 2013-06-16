<?php		/**	*	Essaye de loguer un utilisateur et se souvient de lui en $_SESSION	*	*	@param: $db => une reference sur la ressource MySQL ouverte	*	@param: $n => Le pseudo du type	*	@param: $p => Son mot de passe hashe	*	@return: True en cas de succes, un message d'erreur sinon	*/	function 	logUser($db, $n, $p) {		$user = $db->GetRows("SELECT `players`.`player_id` FROM `players` WHERE `players`.`player_nick` = '$n' AND `players`.`player_pass` = '$p'");		if (isset($user[0])) {			$_SESSION['user-id'] = $user[0]['player_id'];			return (true);		}		return ('Mauvais login / mot de passe');	}	/**	*	Enregistre un nouvel utilisateur en BDD et se souvient de lui en $_SESSION	*	*	@param: $db => une reference sur la ressource MySQL ouverte	*	@param: $n => Le pseudo du type	*	@param: $p => Son mot de passe hashe	*	@param: $m => Son adresse email	*	@return: True en cas de succes, un message d'erreur sinon	*/	function 	inscription($db, $n, $p, $m) {		$exist = $db->GetRows("SELECT `players`.`player_id` FROM `players` WHERE `players`.`player_nick` = '$n'");		if ($exist === null) {			if ($db->Execute("INSERT INTO `players` (`player_id`, `player_nick`, `player_pass`, `player_mail`, `player_score`, `player_global_score`, `player_notification`, `player_available`) VALUES (NULL, '$n', '$p', '$m', '0', '0', '1', '1');") === false)				return ("Un probleme nous emepeche de vous inscrire. Merci de reessayer plus tard.");			$id = mysql_insert_id();			if (isset($_FILES['picture']))				CreateVignette($_FILES['picture'], ('images/users/' . $id . '.jpg'), 75, 75);			$_SESSION['user-id'] = $id;			return (true);		}		else			return ('Ce nom de joueur existe d&eacute;j&agrave; :/');	}	/**	*	Cette fonction va essayer de loguer l'utilisateur (login OU inscription)	*		si les données sont bonnes.	*	*	@param: $db => une reference sur la ressource MySQL ouverte	*	@return: True en cas de succes, un message d'erreur sinon	*/	function	tryToLog($db) {		// Si rien, on se casse		if ($_POST['password'] == '' || $_POST['nick'] == '')			return ("En fait, faut remplir les champs...");		$pwd = hash('sha256', $_POST['password']);		$nick = myBDD::SecureInput($_POST['nick']);		if (isset($_POST['email'])) {			$email = myBDD::SecureInput($_POST['email']);			return (inscription($db, $nick, $pwd, $email));		}		else			return (logUser($db, $nick, $pwd));	}?>