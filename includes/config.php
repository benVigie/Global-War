<?php

	require_once('libs/Smarty.class.php');
	require_once("libs/phpfastcache/php_fast_cache.php");
	
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

	/**
	*	Genere une petite imagette pour la photo de profil
	*
	*	@param: $file => l'element $_FILE de l'image uploade
	*	@param: $dst => Le chemin d'enregistrement de l'image
	*	@param: $dst_w => Largeur de l'image voulue 
	*	@param: $dst_h => Hauteur de l'image voulue
	*	@return: True en cas de succes, une String d'erreur sinon
	*/
	function	CreateVignette($file, $dst, $dst_w = 50, $dst_h = 50) {
		$src = $file['tmp_name'];
		
		if (empty($file['size']) || $file['size'] > 2000000)
			return ('Image trop grosse !');
		
		// Lit les dimensions de l'image
		$size = GetImageSize($src);
		$src_w = $size[0];
		$src_h = $size[1];
		
		// Teste les dimensions tenant dans la zone
		$test_h = round(($dst_w / $src_w) * $src_h);
		$test_w = round(($dst_h / $src_h) * $src_w);

		// Sinon teste quel redimensionnement tient dans la zone
		$x = $y = 0;
		$sizeCpy = $src_w;
		if ($src_w > $src_h)
		{
			$sizeCpy = $src_h;
			$x = ($src_w - $src_h) / 2;
		}
		else if ($src_w < $src_h)
		{
			$sizeCpy = $src_w;
			$y = ($src_h - $src_w) / 2;
		}
		
		// Crée une image vierge aux bonnes dimensions
		$dst_im = ImageCreateTrueColor($dst_w, $dst_h);
		// Copie dedans l'image initiale redimensionnée
		if ($file['type'] == 'image/jpeg')
			$src_im = imagecreatefromjpeg($src);
		else if ($file['type'] == 'image/png')
			$src_im = imagecreatefrompng($src);
		else if ($file['type'] == 'image/gif')
			$src_im = imagecreatefromgif($src);
		else if ($file['type'] == 'image/bmp')
			$src_im = imagecreatefromwbmp($src);
		else
			return ('Format d\'image non supporté !');
		//$src_im = ImageCreateFromJpeg($src);
		ImageCopyResampled($dst_im, $src_im, 0, 0, $x, $y, $dst_w, $dst_h, $sizeCpy, $sizeCpy);
		// Sauve la nouvelle image
		ImageJpeg($dst_im, $dst, 85);
		// Détruis les tampons
		ImageDestroy($src_im);
		ImageDestroy($dst_im);
		
		return (true);
	}
	
?>