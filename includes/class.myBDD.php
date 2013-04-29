<?php

class	myBDD
{
	private		$host		= '127.0.0.1';
	private		$user 		= 'root';
	private		$pwd 		= '';
	private		$database	= 'risk';
	private		$link 		= false;
	
	/*
	*	Connexion à la BDD et à une table précise
	*/
	public function		Connect()
	{
		$this->link = mysql_connect($this->host, $this->user, $this->pwd);
		if (!$this->link)
			return (false);
		return (mysql_select_db($this->database, $this->link));
	}
	
	/*
	*	Ferme la connexion à la BDD
	*/
	public function		Close()
	{
		$ret = mysql_close($this->link);
		return ($ret);
	}
	
	/*
	*	Execute un bon gros SELECT et retourne le résultat sous forme de tableau
	*	@param:		$query => String représentant une requête de type SELECT
	*	@return:	Null si la moindre erreur surgit, le tableau de résultats sinon 
	*/
	public function		GetRows($query)
	{
		$ret = null;
	
		$result = mysql_query($query, $this->link);
		if (!$result)
			return (null);
		while ($row = mysql_fetch_array($result, MYSQL_BOTH))
			$ret[] = $row;
		mysql_free_result($result);
		return ($ret);
	}
	
	/*
	*	Récupère le résultat d'une requète type 'COUNT()' et retourne un int
	*/
	public function		GetCount($query)
	{
		$result = mysql_query($query, $this->link);
		if (!$result)
			return (null);
		$ret = (int)mysql_result($result, 0);
		mysql_free_result($result);
		return ($ret);
	}
	
	/*
	*	Execute la requete passée en paramètre (genre CREATE, UPDATE, DELETE)
	*	@param:		$query => String représentant la requête
	*	@return:	True si la requête a réussie, false sinon (http://php.net/manual/fr/function.mysql-query.php)
	*/
	public function		Execute($query)
	{
		return (mysql_query($query, $this->link));
	}
	
	/*
	*	Traitement d'un input utilisateur pour éviter les injections SQL et le smultiples \
	*	@param:		$str => Une entrée utilisateur
	*	@return:	La chaine traitée et échapée au besoin
	*/
	public static function		SecureInput($str)
	{
		if (get_magic_quotes_gpc())
			return ($str);
		else if (function_exists('mysql_real_escape_string'))
			return (mysql_real_escape_string($str));
		else
			return (mysql_escape_string($str));
	}
}

?>