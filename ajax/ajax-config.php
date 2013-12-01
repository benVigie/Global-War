<?php
	require_once("../libs/phpfastcache/php_fast_cache.php");

	session_start();

	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	//error_reporting(0);

	function 	AjaxExit($raison) {
		$error = array('error' => $raison);

		echo (json_encode((object)$error));
		exit;
	}

	function	FormatDate($date)
	{
		// Si le parametre donne est un timestamp (== un nombre), on calcul la 
		if (is_numeric($date)) {
			$dif = time() - $date;

			if ($dif < 60)
				return 'quelques instants';
			else if ($dif < 600)
				return ceil($dif / 60) . ' minutes';
			else if ($dif < 900)
				return "mois d'un quart d'heure";
			else if ($dif < 1800)
				return "moins d'une demi-heure";
			else if ($dif < 86400)
				return ceil($dif / 3600) . ' heure' . ((ceil($dif / 3600) > 1) ? 's' : '');
			else
				return floor($dif / 86400) . ' jour' . ((floor($dif / 86400) > 1) ? 's' : '');
		}
		// Sinon on traite une date MysQL au format YYYY-MM-DD HH:MM:SS
		else {
			$month = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
			$dAndT = explode(' ', $date);
			$d = explode('-', $dAndT[0]);
			$t = explode(':', $dAndT[1]);
			
			return ('Le ' . $d[2] . ' ' . $month[$d[1] - 1] . ' ' . $d[0] . ' à ' . $t[0] . 'h' . $t[1]);
		}
	}
	
?>