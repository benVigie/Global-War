function	MapSVG() {
	var that = {}, 						// Objet retourné
		
		_raph_paper,					// Objet de la lib Raphael permettant de manipuler le "canvas" et ses objets
		_statesList = {},				// Liste des etats
		_textList = [],					// Liste des 
		_width = 1200, _height = 700,	// Taille par défaut du canvas

		_posX = _posY = 0,				// Position du canvas dans son container
		_dragX, _dragY,					// Utilisez dans le déplacement de la map

		// Liste des etats et de leur relation (Oui monsieur c'est du fait main :)
		_borders = [[2,40],[2,21,40],[0,1,40],[5,15,41],[5,9,35,36,37,41],[3,4,9,41],[33,38,39],[30,32],[26,27,29,30,31,33],[4,5,10,37],[9,11,12],[10,12,27,28],[10,11,25,28],[15,23],[15,16,24],[3,13,14,23,24],[14,17,22,23,24],[16,18,22,25],[17,19,20,22,25],[18,20,25,26,29,30],[18,19,21,22],[1,20,22],[16,17,18,20,21,23],[13,15,16,22,24],[14,15,16,23],[12,17,18,19,26,28],[8,19,25,27,28,29],[8,11,26,28],[11,12,25,26,27],[8,26,19,30],[7,8,19,29,31,32],[8,30,32],[7,30,31],[6,8,34,39],[33,39],[4,36,37,38],[4,35,38,41],[4,9,35],[6,35,36],[6,33,34],[0,1,2],[3,4,5,36],[3,4,5,36]];


	
	function mapLoader(clickFunction) {
		var xmlhttp, xmlDoc,
			i, j, g, c, t,
			countries, svg;

		// Récupération du fichier map svg
		xmlhttp = new XMLHttpRequest()
		xmlhttp.open("GET", "map/map.svg", false);
		xmlhttp.send();
		xmlDoc = xmlhttp.responseXML;

		// Parsing des noeuds de la map
		g = xmlDoc.querySelectorAll('g');
		for (i in g) {
			if (g[i].getAttribute && g[i].getAttribute('inkscape:label')) {
				if (g[i].getAttribute('inkscape:label') === 'flightpaths' ||
					g[i].getAttribute('inkscape:label') === 'countries') {
					countries = g[i].querySelectorAll('path');
					
					// console.log(countries.length + ' pays a dessiner');
					for (j in countries) {
						if (countries[j].getAttribute) {
							svg = _raph_paper.path(countries[j].getAttribute('d'));
							if (countries[j].getAttribute('id').toString().indexOf('path') === -1) {
								uuid = countries[j].getAttribute('uuid').toString();
								_statesList[countries[j].getAttribute('id').toString()] = svg;
								
								// On applique quelques modifications esthetiques
								svg.node.setAttribute('class', 'svg-country country' + uuid);
								svg.node.setAttribute('data-uuid', uuid);
								
								// On applique un drag event qui permet de deplacer la map
								svg.drag(function (dx, dy, x, y) {
											_posX += _dragX - x;
											_posY += _dragY - y;
											_raph_paper.setViewBox(_posX, _posY, _width, _height, true);
											_dragX = x;
											_dragY = y;
										}, function (x, y) {
											_dragX = x;
											_dragY = y;
										});
								
								// Et pour finir on colorie chaque continent de la bonne couleur
								switch (countries[j].getAttribute('continent')) {
									case 'north-america':
										svg.attr({fill: "#c7cfd1"}); break;
									case 'south-america':
										svg.attr({fill: "#d7a38e"}); break;
									case 'europa':
										svg.attr({fill: "#adc8dd"}); break;
									case 'asia':
										svg.attr({fill: "#c3dbab"}); break;
									case 'africa':
										svg.attr({fill: "#f0c082"}); break;
									case 'australia':
										svg.attr({fill: "#f9f780"}); break;
								}
								
								// Dessin de la pastille d'action et d'information
								infos = Raphael.pathBBox(countries[j].getAttribute('d'));
								c = _raph_paper.circle(infos.x + infos.width / 2, infos.y + infos.height / 2, 9);
								c.attr({fill: "Grey", title: countries[j].getAttribute('id').toString()});
								c.node.setAttribute('class', 'svg-pastille pastille' + uuid);
								c.node.setAttribute('data-uuid', uuid);
								// c.click(clickCountry);
								c.click(clickFunction);

								// Texte de la svg-pastille
								t = _raph_paper.text(infos.x + infos.width / 2, infos.y + infos.height / 2, '');
								_textList.push(t);
								t.attr({'font-family': 'CPMono'});
								t.node.setAttribute('class', 'svg-text text' + uuid);
								t.node.setAttribute('id', 'text' + uuid);
								t.node.setAttribute('data-uuid', uuid);
								t.click(clickFunction);
							}
							else
								svg.node.setAttribute('class', 'svg-line');
						}
					}
				}
			}
		}
	}

	that.CreateMap = function (clickFunction, width, height) {
		// Si on veut une taille speciale
		if (width)
			_width = width;
		if (height)
			_height = height;
		_raph_paper = new Raphael(document.getElementById('map'), _width, _height);
		
		// Dessin de la map
		mapLoader(clickFunction);

		// Ajout d'evenements scroll pour le zoom (hors events Raphael)
		var plan = document.querySelector('#map');
		plan.addEventListener("mousewheel", mouseWheelHandler, false);
		plan.addEventListener("DOMMouseScroll", mouseWheelHandler, false);

		// Zoom sur la map et replacement
		_width = Math.round(_width * 0.8);
		_height = Math.round(_height * 0.8);
		_raph_paper.setViewBox(50, 80, _width, _height, true);
	}

	that.AssignMap = function (data) {
		var i,
			place, pl_id, nb_units, color;

		for (i = 0; i < data.length; i++) {
			place = parseInt(data[i]['place'], 10);
			nb_units = parseInt(data[i]['units'], 10);
			color = getColor(data[i]['player']);

			// Set de la couleur
			document.querySelector('.pastille' + place).setAttribute('fill', color);

			// Set du nombre d'unités
			_textList[place].attr('text', nb_units);
		}
	}

	that.SetPastillaText = function (pastillaNumber, text) {
		if (isNaN(pastillaNumber))
			pastillaNumber = parseInt(pastillaNumber, 10);

		_textList[pastillaNumber].attr('text', text);
	}

	 function canIAttack(warCountry, targetCountry) {
		var i;

		for (i = 0; i < _borders[warCountry].length; i++) {
			if (_borders[warCountry][i] == targetCountry)
				return (true);
		}
		return (false);
	}

	function getColor(playerId) {
		var pl = document.querySelector('#player' + playerId);
		
		if (pl)
			return (pl.getAttribute('data-playercolor'));
		else
			return ('Grey');
	}


	
	function mouseWheelHandler(e) {
		var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
		
		delta = (delta === -1) ? 1.2 : 0.8;
		_width = Math.round(_width * delta);
		if (_width > 1200)
			_width = 1200;
		if (_width < 378)
			_width = 378;
		_height = Math.round(_height * delta);
		if (_height > 700)
			_height = 700;
		if (_height < 220)
			_height = 220;
		
		_raph_paper.setViewBox(_posX, _posY, _width, _height, true);
		e.preventDefault();
	}

	function clickCountry(e) {
		var i, uuid = this.node.getAttribute('data-uuid'),
			t, oldSelect,
			all, id;
		
		oldSelect = document.querySelectorAll('.canAttack');
		for (i = 0; i < oldSelect.length; i++) {
			oldSelect[i].setAttribute('class', oldSelect[i].getAttribute('class').substr(0, oldSelect[i].getAttribute('class').indexOf('canAttack')));
		}
		oldSelect = document.querySelectorAll('.cannotAttack');
		for (i = 0; i < oldSelect.length; i++) {
			oldSelect[i].setAttribute('class', oldSelect[i].getAttribute('class').substr(0, oldSelect[i].getAttribute('class').indexOf('cannotAttack')));
		}

		all = document.querySelectorAll('.svg-country');
		for (i = 0; i < 42; i++) {
			id = all[i].getAttribute('data-uuid');
			if (id !== uuid) {
				if (canIAttack(uuid, id))
					all[i].setAttribute('class', all[i].getAttribute('class') + ' canAttack');
				else
					all[i].setAttribute('class', all[i].getAttribute('class') + ' cannotAttack');
			}
		}
	}

	return (that);

}