<?php

	require_once('libs/Smarty.class.php');
	
	// Init Smarty and start session
	$smt = new Smarty();
	session_start();

	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	//error_reporting(0);
	// $smt->debugging = true;
	
	
	################### FONCTIONS A PORTEE GLOBALE ##########################
	/*
	*	Cette fonction s'occupe de l'affichage general des templates.
	*	Elle affiche le header, la page choisi puis le footer, tout en attribuant un titre a la page
	*	Param: $tpl => le template de corp de page a afficher, $title => le titre de la page voulu.
	*	Return: Rien, cette fonction se contente d'afficher puis de stopper le traitement coté serveur.
	*/
	function	Display($tpl, $title = '')
	{
		global	$smt;
		
		$smt->assign('Title', ('Global War :: ' . $title));
		$smt->assign('Page', $tpl);
		
		$smt->display($tpl);
		exit(0);
	}

	function 	SetMessage($str, $error = false)
	{
		global	$smt;

		$smt->assign('Message', $str);
		if ($error)
			$smt->assign('Message_error', true);
	}

	function 	AjaxExit($raison) {
		echo ('error: ' . $raison);
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