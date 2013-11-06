<?php

/**
*
*	Prevoir un envoie d'email pour:
*		- Ajout dans une partie
*		- C'est don tour de jouer
*		- Il a perdu
*		- Un opposant abandonne
*		- ADMIN: un report de bug
*
*/
class myMail
{
	private $adminMail	= 'vigie.benjamin@outlook.com';
	private	$from 		= 'noreply@GlobalWar.com';
	
	/**
	*	Constructeur. Initialise la classe d'envoie de mail.
	*
	*	@param {String} $frm Adresse email de l'expediteur. Par defaut, adresse email de l'admin
	*/
	public function __construct($frm = null)
	{
		// Si on veut envoyer le mail de la part d'une personne en particulier
		if (!is_null($frm))
			$this->from = $frm;

		// Verification de securitee
		if (version_compare(PHP_VERSION, '5.0.0', '<'))
			die ("Votre version se PHP est trop ancienne pour envoyer un mail.");
	}

	/**
	*	Fonction statique qui teste la validite d'une adresse email.
	*
	*	@param {String} $address L'adresse email a verfier
	*	@return {Boolean} True si l'email est valide, false si c'est une adresse bidon
	*/
	public static function ValidateAddress($address)
	{
		return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address);
	}

	/**
	*	Envoie un email :)
	*
	*	@param {String} $to Adresse email du destinataire
	*	@param {String} $title Titre du mail
	*	@param {String} $body Corp du mail (format HTML)
	*	@return {Boolean} True si le mai a bien ete envoye, false sinon
	*/
	public function Send($to, $title, $body)
	{	
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: Global War <$this->from>" . "\r\n";
		
		$message = '<html><head><title>' . $title . '</title></head><body>' . $body . '</body></html>';
		
		if (is_null($to))
			$to = $this->adminMail;
	
		return (mail($to, $title, $message, $headers));
	}
}
  
?>