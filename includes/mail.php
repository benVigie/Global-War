<?php

	require_once ('class.myMail.php');

	/**
	* 	GWMailer Prépare et envoie un mail selon le type d'action demandé. Ca peut etre un mail de nouvelle partie, de fin de tour, etc...
	*
	*	Public Methods:
	*		__construct
	*
	*	Private Methods:
	*
	*/ 
	class GWMailer {

		/*---------------
		|	Propriétés 	|
		---------------*/
		private $_mail;


		/*---------------
		|	Methodes 	|
		---------------*/
		/**
		* Constructeur
		* 
		* @param 
		* @return  
		*/
		public function 	__construct() {
			$this->_mail = new myMail();
		}



		public function sendBugReport($infosArray) {
			$cause = $infosArray['cause'];
			$importance = $infosArray['importance'];
			$message = $infosArray['message'];
			$rapporteur = $infosArray['rapporteur'];
			$email = $infosArray['email'];

			// Prepare email texts
			$texts = array();
			$texts[] = "Nouveau bug/amélioration reporté par $rapporteur (<strong>$email</strong>)";
			$texts[] = "<h4>Cause:</h4>$cause";
			$texts[] = "<h4>Importance:</h4>$importance";
			$texts[] = "<h4>Description:</h4>$message";

			$htmlEmail = $this->priv_mailBuilder('Rapport de bug / amélioration', $texts, 0);

			return ($this->_mail->SendHTML(null, 'Rapport de bug / amélioration', $htmlEmail));
		}

		public function sendNewGame($infosArray) {
			$creator = $infosArray['creator'];
			$player = $infosArray['player'];
			$playerList = $infosArray['playerList'];
			$gameID = $infosArray['gameID'];
			$youStart = $infosArray['youStart'];

			// Prepare email texts
			$texts = array();
			$texts[] = "<strong>$creator</strong> vous défie dans une nouvelle partie.";
			if ($youStart == true)
				$texts[] = "Bonne nouvelle: c'est à vous d'engager le combat.";
			$texts[] = '<i>' . $this->priv_quote() . '</i>';
			$texts[] = "Bonne chance Commandant.";

			$htmlEmail = $this->priv_mailBuilder('La guerre est déclarée', $texts, $player, $gameID, $playerList);

			return ($this->_mail->SendHTML($infosArray['to'], 'Nouvelle partie !', $htmlEmail));
		}

		public function sendNewTurn($infosArray) {
			$player = $infosArray['player'];
			$gameID = $infosArray['gameID'];
			$playerList = $infosArray['playerList'];
			
			// Prepare email texts
			$texts = array();
			$texts[] = $this->priv_yourTurnQuote();
			$texts[] = "Bonne chance Commandant.";

			$htmlEmail = $this->priv_mailBuilder('A vous de jouer', $texts, $player, $gameID, $playerList);

			return ($this->_mail->SendHTML($infosArray['to'], "C'est votre tour", $htmlEmail));
		}

		public function sendEndGame($infosArray) {
			$position = $infosArray['position'];
			$points = $infosArray['points'];
			$player = $infosArray['player'];
			$killer = $infosArray['killer'];
			$bigLoose = $infosArray['bigLooser'];
			$playerList = $infosArray['playerList'];
			$title = '';
			
			// Prepare email texts
			$texts = array();

			if ($position === 'winner') {
				$title = 'Félicitaions Commandant';
				$texts[] = "Félicitation Commandant, vous remportez la partie !";
				$texts[] = $this->priv_endGameQuote(false, $position);
				$texts[] = "Votre victoire vous rapporte au total $points points.";
			}
			else {
				$title = 'Mauvaise nouvelle Commandant';
				$texts[] = "La partie est finie. Toutes vos troupes sont soit mortes soit... Vaut mieux pas chercher à savoir.";
				$texts[] = "Des rumeurs du front disent que c'est $killer qui vous aurait porté le coup de grâce.";
				$texts[] = $this->priv_endGameQuote($bigLoose, $position);

				switch ($position) {
					case 'two':
						if ($points > 0)
							$texts[] = "Cette seconde place rapporte tout de même $points point.";
						else if ($points == 0)
							$texts[] = "Cette seconde place ne vous rapporte aucun point.";
						else
							$texts[] = "Cette seconde place vous coûte un point.";
						break;
					case 'three':
							$texts[] = "Cette troisième place vous coûtera $points points dès que la partie sera terminée.";
						break;
					case 'four':
							$texts[] = "Cette dernière place vous coûtera $points points dès que la partie sera terminée.";
						break;	
				}
			}

			$htmlEmail = $this->priv_mailBuilder($title, $texts, $player, null, $playerList);

			return ($this->_mail->SendHTML($infosArray['to'], "Partie terminee", $htmlEmail));
		}


		/**
		*	Build an HTML email with Global War template
		*
		*	@param 	{String} 	$title 			Title of the email
		*	@param 	{Array} 	$textList 		Array of string representing differents text block to insert in the mail
		*	@param 	{Int}	 	$playerID 		ID of the player who will reveive the email
		*	@param 	{Int}	 	$gameID 		Game ID. If provided the function will add a link to the game.
		*	@param 	{Array} 	$playersList 	Player list. If provided the function will insert a panel to show opponents
		*	@return {String} 	Complete and well formated email in HTML format
		*/
		private function priv_mailBuilder($title, $textList, $playerID, $gameID = null, $playersList = null) {
			global $_GW_SERVER_URL;

			$email = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width">
</head>
<body style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;color: #706C6C;display: block;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;text-align: left;line-height: 19px;font-size: 14px;width: 100%;">
  <table class="body" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;height: 100%;width: 100%;">
    <tr style="padding: 0;vertical-align: top;text-align: left;">
      <td class="center" align="center" valign="top" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;border-collapse: collapse;">
        <center style="width: 100%;">
         
          <table class="row header" style="border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;">
            <tr style="padding: 0;vertical-align: top;text-align: left;">
              <td class="center" align="center" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;border-collapse: collapse;">
                <center style="width: 100%;">
          
                  <table class="container" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;">
                    <tr style="padding: 0;vertical-align: top;text-align: left;">
                      <td class="wrapper last" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;position: relative;padding-right: 0px;border-collapse: collapse;">
            
                        <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;">
                          <tr style="padding: 0;vertical-align: top;text-align: left;">
                            <td class="six sub-columns" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;padding-right: 3.448276%;border-collapse: collapse;width: 50%;">
                              <img src="' . $_GW_SERVER_URL . 'images/logo2.png" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 100%;float: left;clear: both;display: block;">
                            </td>

                          </tr>
                        </table>
            
                      </td>
                    </tr>
                  </table>
          
                </center>
              </td>
            </tr>
          </table> 

          <table class="container" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;">
            <tr style="padding: 0;vertical-align: top;text-align: left;">
              <td style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;border-collapse: collapse;">
              
                <table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;">
                  <tr style="padding: 0;vertical-align: top;text-align: left;">
                    <td class="wrapper last" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;position: relative;padding-right: 0px;border-collapse: collapse;">
                  
                      <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;">
                        <tr style="padding: 0;vertical-align: top;text-align: left;">
                          <td style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;border-collapse: collapse;">
                            <h3 style="color: #3F3F3F;display: block;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 15px 0 10px 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 37px;">';


            // Add title
            $email .= htmlentities($title) . '</h3>';

            // Add texts in separate paragraph
            foreach ($textList as $text) {
            	$text = htmlentities($text, (ENT_COMPAT|ENT_XHTML), 'UTF-8', false);
				$text = str_replace(array('&lt;', '&gt;'), array('<', '>'), $text);
            	$email .= '<p style="margin: 0;padding-bottom: 10px;color: #706C6C;display: block;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;text-align: left;line-height: 19px;font-size: 14px;">' . $text . '</p>';
            }

            // Close email text block
            $email .= '</td><td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;visibility: hidden;width: 0px;border-collapse: collapse;"></td></tr></table></td></tr></table>';

            // Add a button to the game if needed
            if (!is_null($gameID)) {
             	$email .= '<table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;">
                  <tr style="padding: 0;vertical-align: top;text-align: left;">
                    <td class="wrapper" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;position: relative;border-collapse: collapse;">
                
                      <table class="four columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 180px;">
                        <tr style="padding: 0;vertical-align: top;text-align: left;">
                          <td style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;border-collapse: collapse;">
                            
                            <a class="button radius" href="' . $_GW_SERVER_URL . 'game.php?game=' . $gameID . '" style="color: #ffffff;font-family: Helvetica, Arial, sans-serif;text-decoration: none;">
                              <table style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;">
                                <tr style="padding: 0;vertical-align: top;text-align: left;">
                                  <td style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 8px 15px;vertical-align: top;text-align: center;display: block;font-weight: bold;text-decoration: none;font-family: Helvetica, Arial, sans-serif;color: #ffffff;background: steelblue;border: 1px solid #2284a1;font-size: 16px;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;border-collapse: collapse;width: auto;">
                                    C\'est parti !
                                  </td>
                                </tr>
                              </table>
                            </a>
                          </td>
                          <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;visibility: hidden;width: 0px;border-collapse: collapse;"></td>
                        </tr>
                      </table>
                    </td>

                    <td class="wrapper last" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;position: relative;padding-right: 0px;border-collapse: collapse;">
                      <table class="height columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;">
                        <tr style="padding: 0;vertical-align: top;text-align: left;">
                          <td style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;border-collapse: collapse;">&nbsp;</td>
                          <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;visibility: hidden;width: 0px;border-collapse: collapse;"></td>
                        </tr>
                      </table>
                    </td>
                    
                  </tr>
                </table>';
            }
              
            if (!is_null($playersList)) {
            	// Prepare class 
            	switch (count($playersList)) {
            		case 4:
            			$class = 'block-grid three-up';
            			break;
            		case 3:
            			$class = 'block-grid two-up';
            			break;
            		case 2:
            		default:
            			$class = 'twelve columns';
            			break;
            	}

            	// Prepare panel
            	$opponentList = '';
            	$picList = '';
            	foreach ($playersList as $p) {
            		if ($p['id'] != $playerID) {
            			// Upadte opponents name list
            			$opponentList .= (empty($opponentList)) ? $p['nick'] : (', ' . $p['nick']);

            			$picList .= '<td class="center" align="center" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: center;display: inline-block;width: 173px;border-collapse: collapse;"><img src="' . $_GW_SERVER_URL . $p['pic'] . '" style="border: 3px solid ' . $p['color'] . ';opacity: 0.4;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 100%;float: none;clear: both;display: block;height: 60px;border-radius: 50%;margin: 0 auto;opacity:' . (($p['status'] === 1) ? '1' : '0.4') . '"></td>';
            		}
            	}

            	// Add panel in email
                $email .= '<table class="row callout" style="border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;">
                  <tr style="padding: 0;vertical-align: top;text-align: left;">
                    <td class="wrapper last" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;position: relative;padding-right: 0px;border-collapse: collapse;">

                      <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;">
                        <tr style="padding: 0;vertical-align: top;text-align: left;">
                          <td class="panel" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px;vertical-align: top;text-align: left;background: #f2f2f2;border: 1px solid #d9d9d9;border-collapse: collapse;">
                            <h5 style="color: #666;display: block;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 21px;margin-bottom: 10px;">Contre ' . htmlentities($opponentList) . '</h5>
                            
                            <table class="block-grid three-up" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;max-width: 580px;">
                            <tr style="padding: 0;vertical-align: top;text-align: left;">' . $picList . '</tr>
                          </table>

                          </td>
                          <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;visibility: hidden;width: 0px;border-collapse: collapse;"></td>
                        </tr>
                      </table>

                    </td>
                  </tr>
                </table>';
            }
              
          	// Finally properly end the mail
            $email .= '<table class="row footer" style="border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block; margin-top: 20px;">
                  <tr style="padding: 0;vertical-align: top;text-align: left;">
                    <td class="wrapper last" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;position: relative;padding-right: 0px;border-collapse: collapse; background: #ebebeb;">
                      <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;">
                        <tr style="padding: 0;vertical-align: top;text-align: left;">
                          <td class="left-text-pad" style="font-size: 12px;word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;border-collapse: collapse; padding-left: 10px;">
                            <h6 style="color: #706C6C;display: block;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 16px;">Trop de mails ?</h6>
                            Pour ne plus recevoir de mail de Global War, cliquez simplement sur le bouton notifications en haut à droite de <a href="' . $_GW_SERVER_URL . 'home.php" style="color: steelblue;text-decoration: none;"> votre profil.</a>
                          </td>
                          <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;visibility: hidden;width: 0px;border-collapse: collapse;"></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              <!-- container end below -->
              </td>
            </tr>
          </table> 
        </center>
      </td>
    </tr>
  </table>
</body>
</html>';

			// return ($this->_mail->SendHTML('vigie.benjamin@outlook.com', 'Test mail', $message));
			return ($email);
		}


		/**
		*	Get a random famous French quote 
		*
		*	@param 
		*	@return {String} 	Return a quote format ["quote"<br/>- author]
		*/
		private function priv_quote() {
			$quotes = array();

			$quotes[] = '"Un lâche, c’est un héros avec une femme, des gosses et un crédit."<br/>- Marvin Kitma';
			$quotes[] = '"Je veux simplement que vous compreniez que lorsque nous parlons de guerre, nous parlons vraiment de paix."<br/>- George W. Bush';
			$quotes[] = '"En temps de paix, les fils ensevelissent leurs pères ; en temps de guerre, les pères ensevelissent leurs fils. "<br/>- Herodote';
			$quotes[] = '"La guerre, c’est la guerre des hommes ; la paix, c’est la guerre des idées."<br/>- Victor Hugo';
			$quotes[] = '"Les guerres, ce sont des gens qui ne se connaissent pas et qui s’entretuent parce que d’autres gens qui se connaissent très bien ne parviennent pas à se mettre d’accord."<br/>- Paul Valéry';
			$quotes[] = '"La victoire a cent pères, mais la défaite est orpheline. "<br/>- John Fitzgerald Kennedy';
			$quotes[] = '"Engagez-vous dans l’armée ! Venez rencontrer des gens passionnants... et tuez-les !"<br/>- Jean-Loup Chiflet';
			$quotes[] = '"Le pouvoir est au bout du fusil"<br/>- Mao Tse-Toung';
			$quotes[] = '"La défaite est novatrice, la victoire est conservatrice. "<br/>- Bernard Werber';
			$quotes[] = '"Moins on a d’idées, plus on se massacre pour elles."<br/>- Paul Carbone';
			$quotes[] = '"Vous êtes purs, parce que vous n’avez pas eu l’occasion de ne pas l’être."<br/>- François Mitterand (Congrès des Jeunesses Socialistes, 1975) ';
			$quotes[] = '"La politique, c’est l’art d’empêcher les gens de se mêler de ce qui les regarde."<br/>- Paul Valéry ';
			$quotes[] = '"Le meilleur argument contre la démocratie est une conversation de cinq minutes avec l’électeur moyen. "<br/>- Winston Churchill ';
			$quotes[] = '"Un chef, c’est fait pour cheffer"<br/>- Jacques Chirac ';
			$quotes[] = '"Je vous promets que je serai attentif à ce qui a été dit ici, même si je n’étais pas présent."<br/>- George W. Bush ';
			$quotes[] = '"Les hommes croient volontiers ce qu’ils désirent."<br/>- Jules César ';
			$quotes[] = '"Tous les arts ont produit des merveilles : l’art de gouverner n’a produit que des monstres."<br/>- Antoine de Saint-Just';
			$quotes[] = '"Notre Nation doit s’unifier pour se réunir."<br/>- George W. Bush ';
			$quotes[] = '"La moitié des hommes politiques sont des bons à rien. Les autres sont prêts à tout."<br/>- Coluche';
			$quotes[] = '"Aucun roi de France n’aurait été réélu au bout de sept ans."<br/>- Valéry Giscard d’Estaing ';
			$quotes[] = '"Ne craignez jamais de vous faire des ennemis ; si vous n’en avez pas, c’est que vous n’avez rien fait. "<br/>- Georges Clemenceau ';
			$quotes[] = '"Les promesses des hommes politiques n’engagent que ceux qui les reçoivent. "<br/>- Charles Pasqua ';
			$quotes[] = '"Le vrai politique, c’est celui qui sait garder son idéal tout en perdant ses illusions."<br/>- John Fitzgerald Kennedy ';
			$quotes[] = '"La propagande est aux démocraties ce que la violence est aux dictatures. "<br/>- Noam Chomsky ';
			$quotes[] = '"En politique le choix est rarement entre le bien et le mal, mais entre le pire et le moindre mal."<br/>- Nicolas Machiavel';

			return ($quotes[rand(0, count($quotes) -1)]);
		}


		/**
		*	Get a random 'Your turn' quote
		*
		*	@param 
		*	@return {String} 	Return a sentence
		*/
		private function priv_yourTurnQuote() {
			$quotes = array();

			$quotes[] = 'Les troupes sont prêtes et attendent vos ordres.';
			$quotes[] = 'Il y a eu un peu de casse mais vos troupes se sont bien défendues.';
			$quotes[] = 'Quelques pertes au front mais on ne fait pas d\'omelette sans casser des oeufs.';
			$quotes[] = 'Vos renforts arrivent. C\'est le bon moment pour agir.';
			$quotes[] = 'Tous vos soldats ont maté l\'intégrale de Rambo. Ils sont chauds comme la braise !';
			$quotes[] = "L'heure de la revanche a sonné.";

			return ($quotes[rand(0, count($quotes) -1)]);
		}

		/**
		*	Get a random end game quote 
		*
		*	@param 
		*	@return {String} 	Return a sentence
		*/
		private function priv_endGameQuote($isLastOne, $pos) {
			$lastOneQuote = array();
			$three = array();
			$two = array();
			$winner = array();

			// Quote for last player
			$lastOneQuote[] = "Dernier c'est pas trop trop mal non plus... Non je plaisante, c'est nul :p !";
			$lastOneQuote[] = "L'important c'est de participer. Lol.";
			$lastOneQuote[] = "Vous ferez mieux la prochaine fois. Ou pas.";
			$lastOneQuote[] = "Il ne faut pas trop penser à tout ces innocents mort à cause de votre incompétence...";
			$lastOneQuote[] = "Oulaaaa... Il faudrait peut être songer à une reconversion. Genre fleuriste ou caissier.";
			$lastOneQuote[] = "HA HA HA cette quenelle :D ! Non sans rire, vous avez au moins lu les rêgles du jeu ?";
			$lastOneQuote[] = "Allez, séchez ces larmes et retournez bosser.";
			
			$three[] = "Troisième c'est pas ridicule. Sauf sur une partie à trois...";
			$three[] = "Mouais, peux mieux faire.";
			$three[] = "Heureusement que c'était une partie à quatre... Ca aurait pu être pire...";
			$three[] = "A un moment j'y ai cru. Et puis vous avez joué votre premier tour :/";

			$two[] = "Si près du but, quel dommage...";
			$two[] = "C'est bon, on a trouvé le Poulidor du Global War !";
			$two[] = "C'était un beau combat. Mais une finale n'est belle que si on la gagne...";
			$two[] = "On va pas faire la fête non plus, mais bon... C'est pas votre meilleur score ?";
			$two[] = "Je ne vous conseille pas de revoir le replay. Mieux vaut ne plus trop y penser...";
			
			$winner[] = "Une partie rondement menée. La classe.";
			$winner[] = "Vous êtes le plus beau Commandant que le monde ai connu. Je le pense vraiment.";
			$winner[] = "C'était magnifique de vous voir jouer, j'en ai encore la  chair de poule...";
			$winner[] = "Quel talent. Heureusement que la chance à fort à faire dans ce jeu...";
			$winner[] = "Splendide. Le Napoléon du 21ème siècle";

			if ($isLastOne)
				return ($lastOneQuote[rand(0, count($lastOneQuote) -1)]);

			switch ($pos) {
				case 'winner':
					return ($winner[rand(0, count($winner) -1)]);
					break;
				
				case 'two':
					return ($two[rand(0, count($two) -1)]);
					break;

				case 'three':
					return ($three[rand(0, count($three) -1)]);
					break;

				case 'four':
				default:
					return ($lastOneQuote[rand(0, count($lastOneQuote) -1)]);
					break;
			}
		}

	}
	
?>