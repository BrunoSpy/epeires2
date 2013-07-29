$(document).ready(function() {

	var categorie = new Array();
	var cat_coul = new Array();
	var cat_short = new Array();
	var largeur;
	var hauteur;
	var h_aff;
	var dy_ligne = [60,40,30];
	var delt_ligne = 10;
	var y_temp;
	var decoup;
	var lar_unit;
	var d_actuelle;
	var d_ref_deb;
	var d_ref_fin;
	var h_act;
	var h_ref;
	var m_act;
	var vue;
//	var context;

	var exemple = {

			tableau: function() {
				var tab = new Array(); // données
				var debut = new Date(2013,06,29,14,50,00,00);
				var fin = new Date(2013,06,29,21,15,00,00);
				// id, heure de début, heure de fin, libellé, importance, catégorie, action list, état 
				tab[0] = [1, debut, fin, "test_1 : où est-ce que j'apparais ?", 1, "Zone militaire",['a','b','c','d'], "en cours"];
				debut = new Date(2013,06,29,15,45,00,00);
				fin = new Date(2013,06,29,17,53,00,00);
				tab[1] = [2, debut, fin, "test_2", 2, "Technique",['a','b','c','d'], "terminé"];
				debut = new Date(2013,06,28,01,45,00,00);
				fin = new Date(2013,06,29,13,53,00,00);
				tab[2] = [4, debut, fin, "test_3", 2, "CA",["absolument épatant","bravo !","coupable","dessinateur industriel","etudes spécialisées"], "terminé"];
				debut = new Date(2013,06,29,19,00,00,00);
				fin = new Date(2013,06,29,22,30,00,00);
				tab[3] = [7, debut, fin, "test_4", 3, "Attente",['a','b','c','d'], "à venir"];
				debut = new Date(2013,06,29,23,45,00,00);
				fin = new Date(2013,06,29,23,53,00,00);
				tab[4] = [12, debut, fin, "test_5", 1, "Attente",['a','b','c','d'], "en cours"];
				debut = new Date(2013,06,29,16,10,00,00);
				fin = -1; // new Date(2013,06,24,21,10,00,00);
				tab[5] = [19, debut, fin, "test_6", 3, "CA",['a','b','c','d'], "en cours"];
				debut = new Date(2013,06,29,11,37,00,00);
				tab[6] = [15, debut, debut, "test_7", 2, "Technique",['a','b','c','d'], "en cours"];
				debut = new Date(2013,06,29,19,45,00,00);
				fin = new Date(2013,06,29,20,53,00,00);
				tab[7] = [42, debut, fin, "VOL CORONET", 2, "CA",['abracadabra','bravanida','carbonara','detresfa'], "à venir"];
				return tab;
			}
	};

	var timeline = {

			init : function(element) {
				d_actuelle = new Date();
				h_act = d_actuelle.getHours();
				m_act = d_actuelle.getMinutes();
				d_ref_deb = new Date();
				d_ref_deb.setHours(d_ref_deb.getHours()-1,0,0);
				h_aff = 6;
				y_temp = 10;
				h_ref = d_ref_deb.getHours(); 
				d_ref_fin = new Date();
				d_ref_fin.setDate(d_ref_deb.getDate());
				d_ref_fin.setHours(d_ref_deb.getHours()+h_aff, 0, 0);
				decoup = (h_aff + 1) * 2;
				largeur = $(element).width()-60;
				hauteur = $(element).height();
				lar_unit = largeur / decoup;		
				categorie[0] = "Zone militaire";
				categorie[1] = "CA";
				categorie[2] = "Technique";
				categorie[3] = "Attente";
				cat_short[0] = "M<br>I<br>L";
				cat_short[1] = "C<br>A";
				cat_short[2] = "T<br>E<br>C<br>H";
				cat_short[3] = "H<br>O<br>L<br>D";
				cat_coul[0] = "Yellow";
				cat_coul[1] = "Lightsalmon";
				cat_coul[2] = "Greenyellow";
				cat_coul[3] = "Orange";
				vue = 0;
			},

			init_journee : function(element) {
				d_actuelle = new Date();
				h_act = d_actuelle.getHours();
				m_act = d_actuelle.getMinutes();
				d_ref_deb = new Date();
				d_ref_deb.setHours(0,0,0);
				h_aff = 24;
				y_temp = 10;
				h_ref = d_ref_deb.getHours(); 
				d_ref_fin = new Date();
				d_ref_fin.setDate(d_ref_deb.getDate());
				d_ref_fin.setHours(d_ref_deb.getHours()+h_aff, 0, 0);
				decoup = (h_aff + 1) * 2;
				largeur = $(element).width()-60;
				hauteur = $(element).height();
				lar_unit = largeur / decoup;
				vue = 1;
			},

			base : function(base_elmt) {
				var h_temp = h_ref;
				var time_obj = $('<div class="Time_obj"></div>');
				base_elmt.append(time_obj);
				time_obj.css({'position':'absolute', 'top': 40+'px', 'left': lar_unit+'px', 'width': lar_unit*(decoup-2), 'height':1 ,'z-index' : -1, 
					'background-color':'#C0C0C0'});
				for (var i=1;i<decoup;i++) { 
					time_obj = $('<div class="Time_obj"></div>');
					base_elmt.append(time_obj);
					time_obj.css({'position':'absolute', 'top': 35+'px', 'left': lar_unit*i+'px', 'width': 1, 'height':hauteur-50 ,'z-index' : -1, 
						'background-color':'#C0C0C0'});
					if (i%2 == 0) {
						time_obj = $('<div class="Time_obj">30</div>');
						base_elmt.append(time_obj);
						time_obj.css({'position':'absolute', 'top': 20+'px', 'left': lar_unit*i-10+'px','width':'20px', 'text-align':'center', 'z-index' : -1,'color':'#0000FF', 'font-family':'Calibri',
							'font-size':'12px'});
					} else {
						time_obj = $('<div class="Time_obj">'+h_temp+':00</div>');
						base_elmt.append(time_obj);
						time_obj.css({'position':'absolute', 'top': 10+'px', 'left': lar_unit*i-20+'px','width':'20px', 'text-align':'center','z-index' : -1,'color':'#0000FF', 'font-family':'Calibri',
							'font-size':'16px'});
						if (h_temp == 23) {
							h_temp = 0;
						} else {
							h_temp ++;
						}
					}
				}
			},

			timeBar : function(element) {
				var x = 0;
				var delta;
				if (d_ref_deb.getDate() != d_actuelle.getDate()) { delta = h_act + 24 - h_ref;  } else { delta = h_act - h_ref; }
				x = lar_unit + 2*lar_unit*delta + m_act*(2*lar_unit)/60;
				var detail1 = $('<div class="TimeBar"></div>');
				$(element).append(detail1);
				detail1.css({'position':'absolute', 'top': 0+'px', 'left': x+'px', 'width': 3, 'height':hauteur-50 ,'z-index' : 1, 
					'background-color':'red'});
			},

			position: function (d_debut, d_fin) {
				h_deb = d_debut.getHours();
				m_deb = d_debut.getMinutes();
				if (d_fin > 0) {
					h_fin = d_fin.getHours();
					m_fin = d_fin.getMinutes();
				}
				var x1, y1, wid = 0;
				var delta;
				if (d_debut >= d_ref_deb) {
					if (d_fin < d_ref_fin) {
						if (h_deb >= h_ref) { delta = h_deb - h_ref; } else { delta = 24 + h_deb - h_ref; }
						x1 = lar_unit + delta*lar_unit*2 + m_deb*lar_unit*2/60;
						y1 = y_temp;
						if (h_fin >= h_ref) { delta = h_fin - h_ref; } else { delta = 24 + h_fin - h_ref; }
						wid = lar_unit + delta*lar_unit*2 + m_fin*lar_unit*2/60 - x1;
					} else {
						if (d_debut < d_ref_fin) {
							if (h_deb >= h_ref) { delta = h_deb - h_ref; } else { delta = 24 + h_deb - h_ref; }
							x1 = lar_unit + delta*lar_unit*2 + m_deb*lar_unit*2/60;
							y1 = y_temp;
							wid = lar_unit + h_aff*lar_unit*2 - x1;
						}
					}
				} else {
					if (d_fin == 0) {
						x1 = lar_unit;
						y1 = y_temp;
					} else if (d_fin < d_ref_deb) {
						// evmt non terminé
						x1 = 0;
						y1 = y_temp;
						wid = 30;
					} else if (d_fin < d_ref_fin) {
						x1 = lar_unit;
						y1 = y_temp;
						if (h_fin >= h_ref) { delta = h_fin - h_ref; } else { delta = 24 + h_fin - h_ref; }
						wid = lar_unit + delta*lar_unit*2 + m_fin*lar_unit*2/60 - x1;

					} else {
						x1 = lar_unit;
						y1 = y_temp;
						wid = lar_unit + h_aff*lar_unit*2 - x1;	
					}
				}
				return [x1, y1, wid];
			},

			rectangle: function (base_element, id, d_debut, d_fin, label, impt, cat, list, etat) {
				var ind = categorie.indexOf(cat);
				var couleur = cat_coul[ind];
				if (impt <= 3 && impt > 0) { var dy = dy_ligne[impt-1];} else { dy = dy_ligne[2];}
				if (d_fin > 0) {
					var coord = this.position(d_debut, d_fin);
				} else {
					coord = this.position(d_debut, 0); 
				}
				var x1 = coord[0];
				var y1 = coord[1];
				var wid = coord[2];
				var x2 = 0;
				var elmt, elmt_ponct, elmt_rect , elmt_b1, elmt_b2, elmt_txt, elmt_deb, elmt_fin, elmt_opt, elmt_fleche1, elmt_fleche2, elmt_qm_txt, elmt_qm_fleche, elmt_qm_but;
				if (x1 > 0) {
					// création d'un élément
					elmt = $('<div class="elmt" id='+id+'></div>');
					$(base_element).append(elmt);
					elmt.css({'position':'absolute', 'top': y1+'px', 'left': 0+'px', 'width': largeur, 'height':dy});
					if (d_fin < 0) {
						// ajout d'un rectangle "fin non-connue"
						var xmax = lar_unit + h_aff*lar_unit*2;
						if (x1 + 200 < xmax) { wid = 200; } else { wid = xmax - x1;}
						elmt_rect = $('<div class="rect_elmt"></div>');
						$(elmt).append(elmt_rect);
						x2 = x1 + wid;
						elmt_rect.css({'position':'absolute', 'top':'0px', 'left': x1+'px', 'width': wid, 'height':dy ,'z-index' : 1, 
							'background-color':couleur,'border-style':'solid', 'border-width': '1px', 'border-radius': '5px'});
						// ajout d'une flèche droite et d'un point d'interrogation (ainsi qu'un bouton en survol)
						elmt_qm_fleche = $('<div class="elmt_qm_fleche"></div>');
						elmt.append(elmt_qm_fleche);
						elmt_qm_fleche.css({'position':'absolute', 'top': dy-22+'px', 'left': x2+'px'});
						$(elmt_qm_fleche).append('<i class="icon-arrow-right"></i>');
						elmt_qm_txt = $('<div class="elmt_qm_txt"></div>');
						elmt.append(elmt_qm_txt);
						elmt_qm_txt.css({'position':'absolute', 'top': -3+'px', 'left': x2+'px'});
						$(elmt_qm_txt).append('<i class="icon-question-sign"></i>');
						elmt_qm_but = $('<button type="button" id="elmt_qm_but"></button>');
						elmt.append(elmt_qm_but);
						elmt_qm_but.addClass('btn btn-mini');
						elmt_qm_but.css({'position':'absolute', 'top': 4+'px', 'left': x2+4+'px'});
						$(elmt_qm_but).append('<i class="icon-question-sign"></i>');
					} else if (d_debut == d_fin) {
						// ajout du ponctuel
						elmt_ponct = $('<div class="ponct_elmt"></div>');
						$(elmt).append(elmt_ponct);
						x2 = x1 + 10;
						elmt_ponct.css({'position':'absolute', 'top':'0px', 'left': x1+'px', 'width': 10, 'height':dy ,'z-index' : 1, 
							'background-color':couleur,'border-style':'solid', 'border-width': '1px', 'border-radius': '15px'});
					} else {
						// ajout du rectangle
						elmt_rect = $('<div class="rect_elmt"></div>');
						$(elmt).append(elmt_rect);
						x2 = x1 + wid;
						elmt_rect.css({'position':'absolute', 'top':'0px', 'left': x1+'px', 'width': wid, 'height':dy ,'z-index' : 1, 
							'background-color':couleur,'border-style':'solid', 'border-width': '1px', 'border-radius': '5px'});
					}
					// si l'événement a commencé avant la timeline, ajout d'une flèche gauche
					if (d_debut < d_ref_deb) {
						elmt_fleche1 = $('<div class="elmt_fleche1"></div>');
						elmt.append(elmt_fleche1);
						elmt_fleche1.css({'position':'absolute', 'top': dy-22+'px', 'left': x1-12+'px'});
						$(elmt_fleche1).append('<i class="icon-arrow-left"></i>');
					}
					// si l'événement se poursuit au-delà de la timeline, ajout d'une flèche droite
					if (d_fin > d_ref_fin) {
						elmt_fleche2 = $('<div class="elmt_fleche2"></div>');
						elmt.append(elmt_fleche2);
						elmt_fleche2.css({'position':'absolute', 'top': dy-22+'px', 'left': x2+'px'});
						$(elmt_fleche2).append('<i class="icon-arrow-right"></i>');
					}
					// création du cadre des infos optionnelles, accessible par le bouton +
					elmt_opt = $('<div class="elmt_opt"></div>');
					elmt_opt.css({'position':'absolute', 'top':dy+1+'px', 'left': x1+5+'px', 'width': 'auto', 'height':'auto' ,'z-index' : 1, 
						'background-color':couleur,'border-style':'solid', 'padding':'0px' ,'border-width': '1px', 'display':'none'});
					$(elmt).append(elmt_opt);
					var list_tag = $('<ul >'); // class="nav nav-pills nav-stacked"
					elmt_opt.append(list_tag);
					var len = list.length;
					for (var i = 0; i<len; i++) {
						list_tag.append('<li>'+list[i]+'</li>');
					}
					elmt_opt.append('</ul>');
					if (x1+5+elmt_opt.width() > largeur-lar_unit) {
						elmt_opt.css({'left': x1+5-elmt_opt.width()+'px'});
					}
					// ajout du bouton modifications
					elmt_b1 = $('<button type="button" id="mod"></button>');
					$(elmt).append(elmt_b1);
					elmt_b1.addClass('btn btn-mini');
					$(elmt_b1).append('<i class="icon-pencil"></i>');
					// ajout du bouton développé
					elmt_b2 = $('<button type="button" id="plus"></button>');
					$(elmt).append(elmt_b2);
					elmt_b2.addClass('btn btn-mini');
					$(elmt_b2).append('<i class="icon-plus"></i>');
					// ajout du bouton minimisé
					elmt_b2bis = $('<button type="button" id="moins"></button>');
					$(elmt).append(elmt_b2bis);
					elmt_b2bis.addClass('btn btn-mini');
					$(elmt_b2bis).append('<i class="icon-minus"></i>');
					// ajout du nom de l'événement
					elmt_txt = $('<p>'+label+'</p>');
					$(elmt).append(elmt_txt);
					elmt_txt.addClass('label_elmt');
					// ajout de l'heure de début
					var hDeb = d_debut.toLocaleTimeString().substr(0,5);						
					if (d_debut < d_ref_deb && d_debut.getDate() != d_ref_deb.getDate()){ 
						var dDeb = d_debut.toLocaleDateString();
						hDeb = dDeb.substr(0,dDeb.length-5)+" "+hDeb; 
						var h1 = 0;
					} else {
						h1 = 4;
					}
					elmt_deb = $('<p>'+hDeb+'</p>');
					$(elmt).append(elmt_deb);
					elmt_deb.addClass('elmt_deb');
					// ajout de l'heure de fin
					if (d_fin > 0) {
						var hFin = d_fin.toLocaleTimeString().substr(0,5);
						if (d_fin > d_ref_fin && d_fin.getDate() != d_ref_fin.getDate()){
							var dFin = d_fin.toLocaleDateString();
							hFin = dFin.substr(0,dFin.length-5)+" "+hFin; 
							h2 = 0;
						} else {
							h2 = 4;
						}
					} else { 
						h2 = 4;
						hFin = ""; 
					}
					elmt_fin = $('<p>'+hFin+'</p>');
					$(elmt).append(elmt_fin);
					elmt_fin.addClass('elmt_fin');
					// positionnement des différents objets sur la ligne elmt
					elmt_txt.css({'position': 'absolute', 'white-space': 'nowrap', 'font-weight':'bold', 'width':'auto'});
					var txt_wid = elmt_txt.width();
					if (wid - txt_wid > 0) { // si on a la place d'écrire le txt dans le rectangle
						if (wid - txt_wid < 70) { x1 = x1 - 60;} // si il n'y a pas la place de mettre les boutons en plus, on les met à gauche
						elmt_b1.css({'position':'absolute', 'top': 4+'px', 'left': x1+2+'px', 'z-index' : 1});
						elmt_b2.css({'position':'absolute', 'top': 4+'px', 'left': x1+32+'px', 'z-index' : 1});
						elmt_b2bis.css({'position':'absolute', 'top': 4+'px', 'left': x1+32+'px', 'z-index' : 1});
						elmt_txt.css({'position':'absolute', 'top': 4+'px', 'left': x1+62+'px', 'font-weight':'normal', 'z-index' : 2});
					} else { // on n'a pas la place d'écrire le txt dans le rectangle...
						if (wid < 70) { x1 = x1 - 60; } // si on n'a pas non plus la place de mettre les boutons, on les met à gauche, sinon on les place dedans
						elmt_b1.css({'position':'absolute', 'top': 4+'px', 'left': x1+2+'px', 'z-index' : 1});
						elmt_b2.css({'position':'absolute', 'top': 4+'px', 'left': x1+32+'px', 'z-index' : 1});
						elmt_b2bis.css({'position':'absolute', 'top': 4+'px', 'left': x1+32+'px', 'z-index' : 1});
						if (x2+50+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
							elmt_txt.css({'position':'absolute', 'top': 4+'px', 'left': x2+50+'px', 'font-weight':'normal', 'z-index' : 2,
								'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
							var lien = $('<div class="lien"></div>');
							$(elmt).append(lien);
							lien.css({'position':'absolute', 'top': dy/2+'px', 'left': x2+'px','width':50+'px','height':'1px','background-color':'gray', 'z-index' : 1});
						} else { // sinon on le met à gauche
							elmt_txt.css({'position':'absolute', 'top': 4+'px', 'left': x1-45-txt_wid-2+'px', 'font-weight':'normal', 'z-index' : 2,
								'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
							var lien = $('<div class="lien"></div>');
							$(elmt).append(lien);
							if (wid < 70) {
								lien.css({'position':'absolute', 'top': dy/2+'px', 'left': x1-45+'px','width':105+'px','height':'1px',
									'background-color':'gray', 'z-index' : 1});
							} else {
								lien.css({'position':'absolute', 'top': dy/2+'px', 'left': x1-60+'px','width':60+'px','height':'1px',
									'background-color':'gray', 'z-index' : 1});
							}
						}
					}
					// on place l'heure de début à gauche
					elmt_deb.css({'position':'absolute', 'top': h1+'px','left': x1-45+'px', 'width': '40px', 'text-align' : 'center', 
						'font-style':'italic', 'background-color':'LemonChiffon', 'z_index':2});
					// on place l'heure de fin à droite
					elmt_fin.css({'position':'absolute', 'top': h2+'px','left': x2+5+'px', 'width': '40px', 'text-align' : 'center', 
						'font-style':'italic', 'background-color':'LemonChiffon', 'z-index':2});
					y_temp += dy + delt_ligne;  
				} else if (y1 > 0) { 
					// ajout de l'élément warning
					elmt = $('<div class="elmt" id='+id+'></div>');
					$(base_element).append(elmt);
					elmt.css({'position':'absolute', 'top': y1+'px', 'left': 0+'px', 'width': largeur, 'height':dy});
					rect_elmt = $('<div class="rect_elmt"></div>');
					elmt.append(rect_elmt);
					rect_elmt.css({'position':'absolute', 'top': '0px', 'left': lar_unit+'px', 'width': wid, 'height':dy ,'z-index' : 1, 
						'background-color':couleur,'border-style':'solid', 'border-width': '1px','border-radius': '5px'});
					// ajout du bouton warning
					elmt_obj = $('<button type="button"></button>');
					$(elmt).append(elmt_obj);
					elmt_obj.addClass('btn btn-mini');
					elmt_obj.css({'position':'absolute', 'top': 4+'px', 'left': lar_unit+2+'px', 'z-index' : 1});
					$(elmt_obj).append('<i class="icon-warning-sign"></i>');
					y_temp += dy + delt_ligne;
					// ajout de l'heure de fin
					if (d_fin > 0) {
						var hFin = d_fin.toLocaleTimeString().substr(0,5);
						if (d_fin.getDate() != d_ref_deb.getDate()){
							var dFin = d_fin.toLocaleDateString();
							hFin = dFin.substr(0,dFin.length-5)+" "+hFin; 
							h2 = 0;
						} else {
							h2 = 4;
						}	
					} else { 
						h2 = 4;
						hFin = "??:??"; 
					}
					elmt_fin = $('<p>'+hFin+'</p>');
					$(elmt).append(elmt_fin);
					elmt_fin.addClass('elmt_fin');
					elmt_fin.css({'position':'absolute', 'top': h2+'px','left': lar_unit+wid+2+'px', 'width': '40px', 'text-align' : 'center', 
						'font-style':'italic', 'background-color':'LemonChiffon', 'z_index':2});
					// ajout du nom de l'événement
					elmt_txt = $('<p>'+label+'</p>');
					$(elmt).append(elmt_txt);
					elmt_txt.addClass('label_elmt');
					elmt_txt.css({'position':'absolute', 'top': 4+'px', 'left': lar_unit+wid+50+'px', 'font-weight':'normal', 'z-index' : 1,
						'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
					var lien = $('<div class="lien"></div>');
					$(elmt).append(lien);
					lien.css({'position':'absolute', 'top': dy/2+'px', 'left': lar_unit+wid+'px','width':50+'px','height':'1px','background-color':'gray', 'z-index' : 1});
				}
			},
			
			affiche_listes: function (element, liste_passee, liste_avenir) {
				var nb1 = liste_passee.length;
				var nb2 = liste_avenir.length;
				var button1 = $('<button type="button" id="passee"><strong>'+nb1+'</strong></button>');
				$(element).append(button1);
				button1.css({'position':'absolute', 'top': '70px', 'left':'0px', 'width':'30px','height':'150px', 'text-align':'center', 'z-index':5});
				button1.append('<i class="icon-chevron-right"></i>');
				var button2 = $('<button type="button" id="avenir"><strong>'+nb2+'</strong></button>');
				$(element).append(button2);
				button2.css({'position':'absolute', 'top': '70px', 'right':'0px', 'width':'30px','height':'150px', 'text-align':'center', 'z-index':5});
				button2.append('<i class="icon-chevron-left"></i>');
				var liste1 = $('<div class="liste_passee">');
				$(element).append(liste1);
				// liste1.append($('<ul label="Evénements passés :">'));
				var tab = exemple.tableau();
				for (var i=0; i<nb1; i++) {
					// liste1.append($('<li>'+tab[liste_passee[i]][3]+'</li></div>'));
					liste1.append($('<div>'+tab[liste_passee[i]][3]+'</div>'));
				}
				// $(element).append($('</ul>'));
				liste1.css({'display':'none','position':'absolute','top':'70px','left':'0px','width':'auto','height':'auto','text-align':'left','z-index':5,
					'background-color':'LemonChiffon', 'padding':'5px', 'white-space':'nowrap', 'border-style':'solid', 'border-width': '1px', 'border-radius': '2px' });
				var liste2 = $('<div class="liste_avenir">');
				$(element).append(liste2);
				// liste2.append($('<ul label="Evénements à venir :">'));
				for (var i=0; i<nb2; i++) {
					// liste2.append($('<li>'+tab[liste_avenir[i]][3]+'</li></div>'));
					liste2.append($('<div>'+tab[liste_avenir[i]][3]+'</div>'));
				}
				// $(element).append($('</ul>'));
				liste2.css({'display':'none','position':'absolute','top':'70px','right':'0px','width':'auto','height':'auto','text-align':'left','z-index':5,
					'background-color':'LemonChiffon', 'padding':'5px', 'border-style':'solid', 'border-width': '1px', 'border-radius': '2px' });
			},			
			
			create_cat: function(timeline_elmt, tableau) {
				var len = tableau[0].length;
				var nb = categorie.length;
				var liste_passee = new Array();
				var liste_avenir = new Array();
				var debut, fin, etat;
				var id = 0;
				var yy = 0
				var cpt = 0;
//				tableau.sort(function(a,b){return b[5]-a[5];});
				for (var j = 0; j<nb; j++) {
					cpt = 0;
					for (var i = 0; i<len; i++) {
						if (tableau[i][5] == categorie[j]) {
							id = tableau[i][0];
							debut = tableau[i][1];
							fin = tableau[i][2];
							etat = tableau[i][7];
							if (fin < d_ref_deb && etat == "terminé") {
								liste_passee.push(i);
							} else if (debut > d_ref_fin) {
								liste_avenir.push(i);
							} else {
								this.rectangle(timeline_elmt, id, debut, fin, tableau[i][3], tableau[i][4], tableau[i][5], tableau[i][6], tableau[i][7]);
								cpt ++;
							}
						}
					}
					if (cpt > 0) {
						var categ = $('<div class="elmt categorie">'+cat_short[j]+'</div>');
						timeline_elmt.append(categ);
						categ.css({'position':'absolute', 'top':yy+'px', 'left':'0px', 'width':30+'px', 'height':y_temp-7-yy+'px', 'text-align':'center',
							'background-color':cat_coul[j],'border-style':'solid', 'border-width': '1px', 'border-radius': '0px', 'z-index':1});
						var separateur = $('<div class="elmt separateur"></div>');
						timeline_elmt.append(separateur);
						separateur.css({'position':'absolute', 'top':y_temp-6+'px', 'left':'0px', 'width':largeur+'px', 'height':'2px', 'background-color':'black','z-index':1});
					}
					yy = y_temp-4;
				}
				return [liste_passee, liste_avenir];
			},
			
			create: function(timeline_elmt, tableau) {
				var len = tableau[0].length;
				var liste_passee = new Array();
				var liste_avenir = new Array();
				var debut, fin, etat;
				var id = 0;
				var yy = 0
				tableau.sort(function(a,b){return b[5]-a[5];});
					for (var i = 0; i<len; i++) {
					
							id = tableau[i][0];
							debut = tableau[i][1];
							fin = tableau[i][2];
							etat = tableau[i][7];
							if (fin < d_ref_deb && etat == "terminé") {
								liste_passee.push(i);
							} else if (debut > d_ref_fin) {
								liste_avenir.push(i);
							} else {
								this.rectangle(timeline_elmt, id, debut, fin, tableau[i][3], tableau[i][4], tableau[i][5], tableau[i][6], tableau[i][7]);
							}
						}
					return [liste_passee, liste_avenir];
			},
			
			update: function(base_element, tableau) {
				var h_ref_temp = h_ref;
				canvas.init();
				if (h_ref_temp == h_ref) {
					for (var i = 0; i<len; i++) {
						if (d_fin > 0) {
							var coord = this.position(tableau[i][1], tableau[i][2]);
						} else {
							coord = this.position(d_debut, 0); 
						}
						var x1 = coord[0];
						var y1 = coord[1];
						var wid = coord[2];
						elmt = base_element.find('#'+tab[i][0]);

					}
				}
			}
	};


	$.each($('.timeline'), function(index, element) {
		timeline.init(element);
		var base_elmt = $('<div class="Base"></div>');
		$(element).append(base_elmt);
		timeline.base(base_elmt);
		var timeline_content = $('<div class="timeline_content"></div>');
		$(element).append(timeline_content);
		$(element).append('<div class="btn-group">');
		$(element).append('<button type="button" class="btn" id="journee">Vue Journée</button>');
		$(element).append('<button type="button" class="btn" id="courante">Vue courante</button>');
		$(element).append('<button type="button" class="btn" id="tri_cat">Tri par catégorie</button>');
		$(element).append('</div>');
		var timeline_other = $('<div class="timeline_other"></div>');
		$(element).append(timeline_other);
		var res = timeline.create(timeline_content, exemple.tableau());
		timeline.affiche_listes(timeline_other, res[0], res[1]);
		timeline.timeBar(timeline_content);
	});
	$('.timeline_content').on('mouseenter','.elmt', function(){
		$(this).css({'z-index':3});
		$(this).find('.label_elmt').css({'font-weight':'bold'});
		$(this).find('#mod').show();
		$(this).find('#plus').show();
		$(this).find('.elmt_deb').show();
		$(this).find('.elmt_fin').show();
		$(this).find('#elmt_qm_but').show();
		$(this).find('.elmt_qm_txt').hide();
		$(this).find('.elmt_qm_fleche').hide();
	});
	$('.timeline_content').on('mouseleave','.elmt',function(){
		$(this).css({'z-index':1});
		$(this).find('.label_elmt').css({'font-weight':'normal'});
		$(this).find('#mod').hide();
		$(this).find('#plus').hide();
		$(this).find('.elmt_deb').hide();
		$(this).find('.elmt_fin').hide();
		$(this).find('#elmt_qm_but').hide();
		$(this).find('.elmt_qm_txt').show();
		$(this).find('.elmt_qm_fleche').show();
	});
	$('.timeline_content').on('mouseenter','.warn_elmt', function(){
		$(this).css({'z-index':3});
		$(this).find('.label_elmt').css({'font-weight':'bold'});
	});
	$('.timeline_content').on('mouseleave','.warn_elmt',function(){
		$(this).css({'z-index':1});
		$(this).find('.label_elmt').css({'font-weight':'normal'});
	});
	$('.timeline_content').on('click','#plus', function(){
		$(this).hide();
		var elmt = $(this).closest('.elmt');
		elmt.find('#moins').show();
		var elmt_opt = elmt.find('.elmt_opt');
		var dh = elmt_opt.outerHeight();
		elmt.nextAll('.elmt').animate({'top': '+='+dh});
		elmt_opt.slideDown();
	});
	$('.timeline_content').on('click','#moins', function(){
		$(this).hide();
		var elmt = $(this).closest('.elmt');
		elmt.find('#plus').show();
		var elmt_opt = elmt.find('.elmt_opt');
		var dh = elmt_opt.outerHeight();
		elmt_opt.slideUp();	
		elmt.nextAll('.elmt').animate({'top': '-='+dh});
	});
	$('.timeline').on('click','#journee', function(){
		var timel = $(this).closest('.timeline');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		var other = timel.find('.timeline_other');
		base.empty();
		timeline_content.empty();
		other.empty();
		timeline.init_journee(timel);
		timeline.base(base);	
		var res = timeline.create(timeline_content, exemple.tableau());
		timeline.affiche_listes(other, res[0], res[1]);
		timeline.timeBar(timeline_content);
	});
	$('.timeline').on('click','#courante', function(){
		var timel = $(this).closest('.timeline');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		var other = timel.find('.timeline_other');
		base.empty();
		timeline_content.empty();
		other.empty();
		timeline.init(timel);
		timeline.base(base);	
		var res = timeline.create(timeline_content, exemple.tableau());
		timeline.affiche_listes(other, res[0], res[1]);
		timeline.timeBar(timeline_content);
	});
	$('.timeline').on('click','#tri_cat', function(){
		var timel = $(this).closest('.timeline');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		var other = timel.find('.timeline_other');
		base.empty();
		timeline_content.empty();
		other.empty();
		if (vue) {timeline.init_journee(timel);} else {timeline.init(timel);}
		timeline.base(base);	
		var res = timeline.create_cat(timeline_content, exemple.tableau());
		timeline.affiche_listes(other, res[0], res[1]);
		timeline.timeBar(timeline_content);
	});
	$('.timeline').on('click','#passee', function(){
		var timel = $(this).closest('.timeline_other');
		var liste_passee = timel.find('.liste_passee');
		var len = liste_passee.outerWidth(); 
		if (liste_passee.hasClass("liste_passee_affichee")) {
			liste_passee.removeClass("liste_passee_affichee");
			$(this).css({'left':'-='+len});
			$(this).find('.icon-chevron-left').remove();
			$(this).append($('<i class="icon-chevron-right"></i>'));
			liste_passee.hide();
		} else {
			liste_passee.addClass("liste_passee_affichee");
			$(this).css({'left':'+='+len});
			$(this).find('.icon-chevron-right').remove();
			$(this).append($('<i class="icon-chevron-left"></i>'));
			liste_passee.show();
		}
	});
	$('.timeline').on('click','#avenir', function(){
		var timel = $(this).closest('.timeline_other');
		var liste_avenir = timel.find('.liste_avenir');
		var len = liste_avenir.outerWidth();
		if (liste_avenir.hasClass("liste_avenir_affichee")) {
			liste_avenir.removeClass("liste_avenir_affichee");
			$(this).css({'right':'-='+len});
			$(this).find('.icon-chevron-right').remove();
			$(this).append($('<i class="icon-chevron-left"></i>'));
			liste_avenir.hide();
		} else {
			liste_avenir.addClass("liste_avenir_affichee");
			$(this).css({'right':'+='+len});
			$(this).find('.icon-chevron-left').remove();
			$(this).append($('<i class="icon-chevron-right"></i>'));
			liste_avenir.show();
		}
	});
});


/*	var canvas = {

init : function(element) {
	d_actuelle = new Date();
	h_act = d_actuelle.getHours();
	m_act = d_actuelle.getMinutes();
	d_ref_deb = new Date();
	d_ref_deb.setHours(d_ref_deb.getHours()-1,0,0);
	h_ref = d_ref_deb.getHours(); 
	d_ref_fin = new Date();
	d_ref_fin.setDate(d_ref_deb.getDate());
	d_ref_fin.setHours(d_ref_deb.getHours()+h_aff, 0, 0);
	decoup = (h_aff + 1) * 2;
	largeur = $(element).width();
	hauteur = $(element).height();
	lar_unit = largeur / decoup;
},

base : function(element) {
	context = element.getContext('2d');
	var h_temp = h_ref;
	context.beginPath();
	context.lineWidth=2;
	context.moveTo(lar_unit, 40);
	context.lineTo(lar_unit*(decoup-1), 40);
	context.strokeStyle = '#0000FF';
	context.stroke();
	context.closePath();
	context.beginPath();
	context.strokeStyle = '#C0C0C0';
	context.fillStyle = '#0000FF';
	for (var i=1;i<decoup;i++) { 
		context.moveTo(lar_unit*i, 35);
		context.lineTo(lar_unit*i, hauteur-15);
		if (i%2 == 0) {
			context.font = '8pt Calibri';
			context.fillText('30', lar_unit*i-6, 30);
		} else {
			context.font = '12pt Calibri';
			context.fillText(h_temp+':00', lar_unit*i-15, 20);
			if (h_temp == 23) {
				h_temp = 0;
			} else {
				h_temp ++;
			}
		}
	}
	context.stroke();
	context.closePath();
}		
};*/
