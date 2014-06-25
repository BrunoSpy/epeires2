
var categorie = new Array();
var cat_coul = new Array();
var cat_short = new Array();
var cat_regroup = new Array();
var cat_display = new Array();
var impt_name = new Array();
var impt_style = new Array();
var impt_value = new Array();
var largeur;
var hauteur;
var h_aff;
var dy_max = 30;
var delt_ligne = 10;
var y_temp;
var decoup;
var lar_unit;
var d_actuelle;
var d_ref_deb;
var d_ref_fin;
var d_min;
var d_max; 
var h_act;
var h_ref;
var m_act;
var vue;
var liste_affichee = new Array();
var liste_passee = new Array();
var liste_avenir = new Array();
var tri_cat;
var tri_hdeb;
var tri_impt;
var open_list = new Array();
var x_act;
var tab = Array();
var corresp = Array();
var plus_info = 0;
var warn = new Array();
var temp_fin;
var aff_fin;
var temp_deb;
var aff_deb;
var cpt_journee;
var ini_url;
var on_drag;
var aff_archives = 0;

var timeline = {

		// chargement de la liste des impacts
		set_impacts: function (url) {
			var i = 0;
			return $.getJSON(url+"/getimpacts", function (data) {
				$.each(data, function(key, value) {
					impt_name[i] = value.name;
					impt_style[i] = value.short_name;
					impt_value[i] = value.color;
					i ++;
				});
			});
		},
		// chargement des catégories
		set_cat: function (url) {
			var i = 0;
			return $.getJSON(url+"/getcategories", function (data) {
				$.each(data, function(key, value) {
					categorie[i] = value.name;
					cat_short[i] = value.short_name;
					cat_coul[i] = value.color;
					cat_regroup[i] = 1;
					cat_display[i] = 1;
					i ++;
				});
			});
		},
		// calcul de la hauteur de rectangle fonction de l'impact
		compute_impact: function (name, value) {
			return dy_max*value/100;
		},
		// config initiale : chargement des évènements
		conf: function (element, url) {
			ini_url = url;
			timeline.init(element);
			var base_elmt = $('<div class="Base"></div>');
			$(element).append(base_elmt);
			timeline.base(base_elmt);
			var timeline_content = $('<div class="timeline_content"></div>');
			$(element).append(timeline_content);
			//var timeline_other = $('<div class="timeline_other"></div>');
			$.when(timeline.set_cat(url), timeline.set_impacts(url)).then(function(){
				//$(element).append(timeline_other);
				tab = Array();
				var i = 0;
				var ddeb, dfin;
				$.getJSON(url+"/getevents", function (data) {
					$.each(data, function(key, value) {
						ddeb = new Date(value.start_date);
						if (value.punctual == true) {
							dfin = ddeb;
						} else {
							if (value.end_date == null) { 
								dfin = -1;
							} else {
								dfin = new Date(value.end_date);
							}
						}
						tab[i] = [key, ddeb, dfin, value.punctual, value.name, value.archived, value.category_root,value.modifiable, value.status_name];
						corresp[key] = i;
						var j = 0;
						i ++;
					});
					timeline.create(timeline_content, tab);
					timeline.tri_cat(timeline_content, tab, 1);
					timeline.timeBar(timeline_content);
				});
			});
		},
		// initialisation de la timeline 6h
		init: function(element) {
			d_actuelle = new Date();
			h_act = d_actuelle.getUTCHours();
			m_act = d_actuelle.getMinutes();
			d_ref_deb = new Date();
			d_ref_deb.setHours(d_actuelle.getHours()-1,0,0);
			h_aff = 6;
			y_temp = 10;
			h_ref = d_ref_deb.getUTCHours(); 
			d_ref_fin = new Date();
			d_ref_fin.setDate(d_ref_deb.getDate());
			d_ref_fin.setHours(d_ref_deb.getHours()+h_aff, 0, 0);
			d_min = new Date();
			d_max = new Date(); 
			d_min.setHours(d_ref_deb.getHours() - 10);
			d_min.setMinutes(0);
			d_max.setHours(d_ref_fin.getHours() + 10);
			d_min.setMinutes(0);
			decoup = (h_aff + 1) * 2;
			largeur = $(element).width()-60;
			hauteur = $(element).height();
			lar_unit = largeur / decoup;
			var delta;
			if (d_ref_deb.getDate() != d_actuelle.getDate()) { delta = h_act + 24 - h_ref;  } else { delta = h_act - h_ref; }
			x_act = lar_unit + 2*lar_unit*delta + m_act*(2*lar_unit)/60;
			vue = 0;
		},
		// initialisation de la timeline journée
		init_journee: function(element) {
			d_actuelle = new Date();
			h_act = d_actuelle.getUTCHours();
			m_act = d_actuelle.getMinutes();
			d_ref_deb = new Date();
			d_ref_deb.setUTCHours(0,0,0);
			h_aff = 24;
			y_temp = 10;
			h_ref = d_ref_deb.getUTCHours(); 
			d_ref_fin = new Date();
			d_ref_fin.setDate(d_ref_deb.getDate());
			d_ref_fin.setHours(d_ref_deb.getHours()+h_aff, 0, 0);
			d_min = new Date();
			d_max = new Date(); 
			d_min.setHours(d_ref_deb.getHours() - 10);
			d_min.setMinutes(0);
			d_max.setHours(d_ref_fin.getHours() + 10);
			d_min.setMinutes(0);
			decoup = (h_aff + 1) * 2;
			largeur = $(element).width()-100;
			hauteur = $(element).height();
			lar_unit = largeur / decoup;
			var delta;
			if (d_ref_deb.getDate() != d_actuelle.getDate()) { delta = h_act + 24 - h_ref;  } else { delta = h_act - h_ref; }
			x_act = lar_unit + 2*lar_unit*delta + m_act*(2*lar_unit)/60;
			vue = 1;
		},
		// création des éléments de base
		base: function(base_elmt) {
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
		// création de la timeBar
		timeBar: function(element) {
			var detail1 = $('<div class="TimeBar"></div>');
			element.append(detail1);
			detail1.css({'position':'absolute', 'top': 0+'px', 'left': x_act+'px', 'width': 3, 'height':hauteur-50 ,'z-index' : 10, 
				'background-color':'red'});
		},
		// mise à jour de la timeBar
		maj_timeBar: function(element) {
			var detail1 = $(element).find('.TimeBar');
			detail1.css({'left': x_act+'px'});
		},
		// conversion des heures de début et de fin en positionnement sur la timeline
		position: function (d_debut, d_fin) {
			h_deb = d_debut.getUTCHours();
			m_deb = d_debut.getMinutes();
			if (d_fin > 0) {
				h_fin = d_fin.getUTCHours();
				m_fin = d_fin.getMinutes();
			}
			var x1, wid = 0;
			var delta;
			if (d_debut >= d_ref_deb) {
				if (h_deb >= h_ref) { delta = h_deb - h_ref; } else { delta = 24 + h_deb - h_ref;}
				x1 = lar_unit + delta*lar_unit*2 + m_deb*lar_unit*2/60;
				if (d_fin < 0) {	
						wid = largeur-lar_unit-x1;
				} else if (d_fin < d_ref_fin) {
					if (h_fin >= h_ref) { delta = h_fin - h_ref; } else { delta = 24 + h_fin - h_ref; }
					wid = lar_unit + delta*lar_unit*2 + m_fin*lar_unit*2/60 - x1;
				} else {
					if (d_debut < d_ref_fin) {
						wid = lar_unit + h_aff*lar_unit*2 - x1;
					}
				}
			} else {
				x1 = lar_unit;
				if (d_fin < 0) {
					wid = largeur-lar_unit-x1;
				} else if (d_fin < d_ref_deb) {
					// evmt non terminé
					wid = 40;	
				} else if (d_fin < d_ref_fin) {
					if (h_fin >= h_ref) { delta = h_fin - h_ref; } else { delta = 24 + h_fin - h_ref; }
					wid = lar_unit + delta*lar_unit*2 + m_fin*lar_unit*2/60 - x1;

				} else {
					wid = lar_unit + h_aff*lar_unit*2 - x1;	
				}
			}
			return [x1, wid];
		},
		// création du squelette des évènements affichés sur la timeline 
		create: function(timeline_elmt, tableau) {
			var len = tableau.length;
			var nb = categorie.length;
			liste_passee = new Array();
			liste_avenir = new Array();
			liste_affichee = new Array();
			cpt_journee = new Array();
			var debut, fin, etat;
			var id = 0;
			var yy = 0;
			var cpt = 0;
			var h_current;
			for (var j = 0; j<nb; j++) {
				cpt = 0;
				for (var i = 0; i<len; i++) {
					if (tableau[i][6] == categorie[j]) {
						id = tableau[i][0];
						debut = tableau[i][1];
						fin = tableau[i][2];
						etat = tableau[i][8];
						if (fin == -1 || (debut < d_actuelle && fin > d_actuelle) || 
								(debut.toLocaleDateString() == d_actuelle.toLocaleDateString()) ||
								(fin.toLocaleDateString() == d_actuelle.toLocaleDateString())) {
							cpt_journee.push(i);
						}
						if (fin > 0 && fin < d_ref_deb && (etat == "Terminé" || etat == "Annulé")) { 
							if (fin > d_min) {liste_passee.push(i);}
						} else if (debut > d_ref_fin) {
							if (debut < d_max) {liste_avenir.push(i);}
						} else {
							liste_affichee.push(i);
							this.create_elmt(timeline_elmt, id, debut, fin, tableau[i][3], tableau[i][4], tableau[i][5], tableau[i][6], tableau[i][7], tableau[i][8]);
							cpt ++;
						}
					}
				}
				var text_cat = "";
				var len_cat = cat_short[j].length;
				for (var k = 0; k<len_cat; k++) {
					text_cat += cat_short[j][k]+'<br>';
				}
				var categ = $('<div class="categorie '+j+'">'+text_cat+'</div>');
				timeline_elmt.append(categ);
				categ.css({'position':'absolute', 'top':yy+'px', 'left':'-30px', 'width':30+'px', 'height':'auto', 'text-align':'center',
					'background-color':cat_coul[j],'border-style':'solid', 'border-width': '1px', 'border-color':'grey', 'border-radius': '0px', 'z-index':1});
				h_current = y_temp-7-yy;
				if (categ.height() > h_current) {
					y_temp = yy + categ.height() + 7;
				} else {
					categ.css({'height':h_current+'px'});
				}
				yy = y_temp-4;
			}
		},
		// compression des évènements affichés et qui ont le même nom
		regroupement: function(timeline_elmt, id_list, y1) {
			var z, lbl_temp, id_temp, y_t, dy_temp;
			id_list.sort(function(a,b){return a[1]-b[1];});
			var len = id_list.length;
			var elmt;
			y_t = y1;
			for (var i = 0; i<len; i++) {
				id_temp = id_list[i][0];
				lbl_temp = id_list[i][1];
				elmt = timeline_elmt.find('.ident'+id_temp);
				elmt.css({'top':y_t});
				dy_temp = elmt.height();
				z = 1;
				while (i+1<len && id_list[i+1][1] == lbl_temp){
					i++;
					z++;
					id_temp = id_list[i][0];
					elmt = timeline_elmt.find('.ident'+id_temp);
					elmt.css({'top':y_t+'px','z-index':z});
					dy_temp = Math.max(dy_temp, elmt.height());
				}
				y_t += dy_temp + delt_ligne;
			}
			return y_t;
		},
		// affichage ou non des évènements archivés
		affichage_arch: function(elmt, arch) {
			var elmt_rect = elmt.find('.rect_elmt');
			var elmt_compt = elmt.find('.complement');
			elmt_text = elmt.find('.label_elmt');
			if (arch == 1) {
				elmt_rect.css({'opacity':'0.4'});
				elmt_rect.children().css({'opacity':'0.4'});
				elmt_compt.css({'opacity':'0.4'});
				elmt_text.css({'color':'DarkGray'});
			} else {
				elmt_rect.css({'opacity':'1'});
				elmt_rect.children().css({'opacity':'1'});
				elmt_compt.css({'opacity':'1'});
				elmt_text.css({'color':'black'});
			}			
		},
		// tri des évènements par catégorie
		tri_cat: function(timeline_elmt, tableau, speed) {
			timeline_elmt.find('.categorie').remove();
			timeline_elmt.find('.separateur').remove();
			tri_cat = 1;
			tri_impt = 0;
			tri_hdeb = 0;
			var id_list = new Array();
			var len = tableau.length;
			var nb = categorie.length;
			var debut, fin, etat;
			var id = 0;
			var elmt;
			var yy = 0;
			var y1;
			y_temp = delt_ligne;
			for (var j = 0; j<nb; j++) {
				if (cat_display[j] == 1) {
					id_list = new Array();
					y1 = y_temp;
					for (var i = 0; i<len; i++) {
						if (tableau[i][6] == categorie[j]) {
							id = tableau[i][0];
							debut = tableau[i][1];
							fin = tableau[i][2];
							etat = tableau[i][8];
							arch = tableau[i][5];
							if (!(fin < d_ref_deb && etat == "Terminé") && debut <= d_ref_fin) {
								elmt = timeline_elmt.find('.ident'+id);
								id_list.push([id, tableau[i][4]]);
								elmt.css({'top':y_temp+'px'});
								if (arch == 0 || (arch == 1 && aff_archives == 1)) {
									y_temp = y_temp + elmt.height() + delt_ligne;
									elmt.show();
								} else if (arch == 1 && aff_archives == 0) {
									elmt.hide();
								}
								timeline.affichage_arch(elmt,arch);
							}
						}
					}
					if (cat_regroup[j] == 1 && id_list != null) { 
					}
					var text_cat = "";
					var len_cat = cat_short[j].length;
					for (var k = 0; k<len_cat; k++) {
						text_cat += cat_short[j][k]+'<br>';
					}
					var categ = $('<div class="categorie '+j+'">'+text_cat+'</div>');
					timeline_elmt.append(categ);
					categ.css({'position':'absolute', 'top':yy+'px', 'left':'-15px', 'width':30+'px', 'height':'auto', 'text-align':'center',
						'background-color':cat_coul[j],'border-style':'solid', 'border-width': '1px', 'border-color':'grey', 'border-radius': '0px', 'z-index':1});
					//		var separateur = $('<div class="elmt separateur"></div>');
					//		timeline_elmt.append(separateur);
					h_current = y_temp-7-yy;
					if (categ.height() > h_current) {
						y_temp = yy + categ.height() + 7;
					} else {
						categ.css({'height':h_current+'px'});
					}
					//	separateur.css({'position':'absolute', 'top':y_temp-delt_ligne/2+'px', 'left':-15+'px', 'width':largeur+30+'px', 'height':'1px', 'background-color':'grey','z-index':1});
					yy = y_temp-4;
				}
			}
		},
		// tri des évènements par heure de début
		tri_hdeb: function(timeline_elmt, tableau, speed) {
			timeline_elmt.find('.categorie').remove();
			timeline_elmt.find('.separateur').remove();
			tri_cat = 0;
			tri_impt = 0;
			tri_hdeb = 1;
			var len = tableau.length;
			var debut, fin, etat;
			var elmt;
			y_temp = 0;
			tableau.sort(function(a,b){return a[1]-b[1];});
			for (var i = 0; i<len; i++) {
				id = tableau[i][0];
				corresp[id] = i;
				debut = tableau[i][1];
				fin = tableau[i][2];
				etat = tableau[i][8];
				arch = tableau[i][5];
				if (!(fin < d_ref_deb && etat == "Terminé") && debut <= d_ref_fin) {
					elmt = timeline_elmt.find('.ident'+id);
					if (arch == 0 || (arch == 1 && aff_archives == 1)) {
						y_temp += delt_ligne;
						if (speed) {
							elmt.css({'top':y_temp+'px'});
						} else {
							elmt.animate({'top':y_temp+'px'});
						}
						y_temp += elmt.height();
						elmt.show();
					} else if (arch == 1 && aff_archives == 0) {
						elmt.hide();
					}
					timeline.affichage_arch(elmt,arch);					
				}
			}
		},
		// filtre des évènements par étoile (transparance) 
		tri_impt: function(timeline_elmt, tableau, speed) {
			timeline_elmt.find('.categorie').remove();
			timeline_elmt.find('.separateur').remove();
			tri_cat = 0;
			tri_hdeb = 0;
			tri_impt = 1;
			var len = tableau.length;
			var debut, fin, etat;
			var elmt;
			y_temp = 0;
			tableau.sort(function(a,b){return b[5]-a[5];});
			for (var i = 0; i<len; i++) {
				id = tableau[i][0];
				corresp[id] = i;
				debut = tableau[i][1];
				fin = tableau[i][2];
				etat = tableau[i][8];
				if (!(fin < d_ref_deb && etat == "Terminé") && debut <= d_ref_fin) {
					y_temp += delt_ligne;
					elmt = timeline_elmt.find('.ident'+id);
					var elmt_rect = elmt.find('.rect_elmt');
					if (tableau[i][5] == 0) {
						elmt_rect.css({'opacity':'0.4'});
						elmt_rect.children().css({'opacity':'0.4'});
					}
					if (speed) {
						elmt.css({'top':y_temp+'px'});
					} else {
						elmt.animate({'top':y_temp+'px'});
					}
					y_temp += elmt.height();
				}
			}
		},

		// création des éléments d'une ligne d'un évènement affiché
		creation_ligne: function (base_element, id, label, y0, dy, type, couleur){
			// création d'un élément
			var elmt = $('<div class="elmt"></div>');
			var ss_elmt = elmt[0];
			jQuery.data(ss_elmt, "ident", id);
			$(base_element).append(elmt);
			elmt.addClass("ident"+id);
			// ajout d'un rectangle
			var elmt_rect = $('<div class="rect_elmt"></div>');
			elmt.append(elmt_rect);
			var elmt_compl = $('<div class="complement"></div>');
			elmt_rect.after(elmt_compl);
			// si l'événement a commencé avant la timeline, ajout d'une flèche gauche
			var elmt_fleche1 = $('<div class="elmt_fleche1"></div>');
			elmt.append(elmt_fleche1);
			$(elmt_fleche1).append('<i class="icon-arrow-left"></i>');
			// si l'événement se poursuit au-delà de la timeline, ajout d'une flèche droite
			var elmt_fleche2 = $('<div class="elmt_fleche2"></div>');
			elmt.append(elmt_fleche2);
			$(elmt_fleche2).append('<i class="icon-arrow-right"></i>');
			// création du cadre des infos optionnelles, accessible par le bouton +
			var elmt_opt = $('<div class="elmt_opt"></div>');
			$(elmt).append(elmt_opt);
			var elmt_write = $('<textarea class="elmt_write">'+label+'</textarea>');
			elmt_opt.append(elmt_write);
			// ajout du bouton modifications
			elmt_status = $('<a class="elmt_status" href="#" data-id="'+id+'"data-name="'+label+'"></a>');
			// $(elmt).append(elmt_status);
			
			// ajout du nom de l'événement
			elmt_txt = $('<p class="label_elmt">'+label+'</p>');
			$(elmt).append(elmt_txt);
			// ajout du bouton "ouverture fiche"
			elmt_b1 = $('<a href="#" class="modify-evt" data-id="'+id+'"data-name="'+label+'"></a>');
			$(elmt_txt).append(elmt_b1);
			$(elmt_b1).append('  <i class="icon-pencil"></i>');
			// ajout du bouton "ouverture fiche réflexe"
			elmt_b2 = $('<a href="#" class="checklist-evt" data-id="'+id+'"data-name="'+label+'"></a>');
			$(elmt_txt).append(elmt_b2);
			$(elmt_b2).append('  <i class="icon-tasks"></i>');
			// ajout de l'archivage
			elmt_arch = $('<a href="#" class="archive-evt" data-id="'+id+'"data-name="'+label+'"></a>');
			$(elmt_txt).append(elmt_arch);
			$(elmt_arch).append('  <i class="icon-eye-close"></i>');
			// lien entre le texte et l'événement (si texte écrit en dehors)
			var lien = $('<div class="no_lien"></div>');
			$(elmt).append(lien);
			elmt_deb = $('<a href="#" class="elmt_deb"></a>');
			$(elmt).append(elmt_deb);
			elmt_fin = $('<a href="#" class="elmt_fin"></a>');
			$(elmt).append(elmt_fin);
			move_deb = $('<p class="move_deb"></p>');
			$(elmt_rect).append(move_deb);
			move_fin = $('<p class="move_fin"></p>');
			$(elmt_rect).append(move_fin);
			
			elmt.css({'position':'absolute', 'top': y0+'px', 'left':'0px', 'width': largeur, 'height':dy});
			elmt_fleche1.css({'position':'absolute', 'top': dy-22+'px', 'left': '0px'});
			elmt_fleche2.css({'position':'absolute', 'top': dy-22+'px', 'left': '0px'});
			elmt_write.css({'background-color':couleur});
			elmt_opt.css({'position':'absolute', 'top':dy+1+'px', 'left': '0px', 'width': 'auto', 'height':'auto' ,'z-index' : 1, 
				'background-color':couleur,'border-style':'solid','border-color':'transparent', 'padding':'0px' ,'border-width': '1px', 'display':'none'});
			elmt_b1.css({'z-index' : 1});
			elmt_b2.css({'z-index' : 1});
			elmt_arch.css({'z-index' : 1});
			elmt_status.css({'position':'absolute', 'bottom': 0+'px', 'left': '0px', 'width':'56px', 'z-index' : 1});
			elmt_status.hide();
	//		elmt_obj.css({'position':'absolute', 'top': dy/2-11+'px', 'left': '0px', 'background-color':couleur, 'z-index' : 1});
			elmt_txt.css({'position':'absolute', 'top': dy/2-11+'px', 'left': '0px', 'font-weight':'normal', 'z-index' : 2});
			lien.css({'position':'absolute', 'top': dy/2+'px', 'left': '0px','width':'10px','height':'1px','background-color':'gray', 'z-index' : 1});
			elmt_deb.css({'position':'absolute', 'top': '6px','left': '0px', 'width': '54px', 'text-align' : 'right', 
				'font-style':'italic', 'background-color':'Transparant', 'z-index':3});
			elmt_fin.css({'position':'absolute', 'top': '6px','left': '0px', 'width': '54px', 'text-align' : 'left', 
				'font-style':'italic', 'background-color':'Transparant', 'z-index':3});
			move_deb.css({'position':'absolute', 'top': 4+'px','height':dy-8, 'z-index':2, 'background-color':'Transparant', 
				'border-right-style':'solid', 'border-left-style':'solid', 'border-width':'2px', 'display':'none'});
			move_fin.css({'position':'absolute', 'top': 4+'px','height':dy-8, 'z-index':2, 'background-color':'Transparant', 
				'border-right-style':'solid', 'border-left-style':'solid', 'border-width':'2px', 'display':'none'});
		move_deb.hover(function(){$(this).css({'cursor':'e-resize'});});
		move_fin.hover(function(){$(this).css({'cursor':'e-resize'});});
		},
		// animation pour identifier un évènement
		warn_animate:function(elmt_warn) {
			elmt_warn.toggle(200);
		},
		// positionnement des éléments d'un évènement sur la ligne dédiée
		position_ligne: function (base_element, id, type, x0, wid, arch, mod, couleur) {
			var l_deb = type[0];  
			var l_fin = type[1];
			var warn = type[2];
			var sts = type[3];
			var elmt = base_element.find('.ident'+id);
			var elmt_rect = elmt.find('.rect_elmt');
			var elmt_compl = elmt.find('.complement');
//			var elmt_qm_but = elmt.find('.no_elmt_qm_but');
//			var elmt_warn = elmt.find('.elmt_warn');
			var elmt_fleche1 = elmt.find('.elmt_fleche1');
			var elmt_fleche2 = elmt.find('.elmt_fleche2');
			var elmt_opt = elmt.find('.elmt_opt');
			var elmt_write = elmt_opt.find('.elmt_write');
			var elmt_status = elmt.find('.elmt_status');
			var elmt_b1 = elmt.find('.modify-evt');
			var elmt_b2 = elmt.find('.checklist-evt');
			var elmt_arch = elmt.find('.archive-evt');
			var elmt_txt = elmt.find('.label_elmt');
			var elmt_deb = elmt.find('.elmt_deb');
			var elmt_fin = elmt.find('.elmt_fin');
			var move_deb = elmt.find('.move_deb');
			var move_fin = elmt.find('.move_fin');
			var lien = elmt.find('.no_lien');
			var x1 = x0;
			var b1_pos;
			var x2 = x1 + wid;
			var ponctuel = 0;
			
			// initialisation en cas de redessin de l'élément
			elmt_rect.css({'position':'', 'top':'', 'left': '', 'width': '', 'height':'' ,'z-index' : '', 
				'background-color':'','border-style':'','border-color':'',  'border-width': '', 'border-radius': '',
				'border-left':'', 'border-right':'', 'border-bottom':''});
			elmt_txt.css({'color':'black'});
			elmt_deb.removeClass('nodisp');
			move_deb.removeClass('disp');
			elmt_fin.removeClass('nodisp');
			move_fin.removeClass('disp');
			elmt_deb.removeClass("disabled");
			elmt_fin.removeClass("disabled");
			elmt_b1.removeClass("invisible");
			elmt_b2.removeClass("invisible");
			elmt_arch.removeClass("invisible");
			elmt_compl.show();
			var dr = 1;
			lien.removeClass('lien');
			elmt_txt.css({'background-color':'','border-style':'', 'border-color':'','border-width': '','border-radius': '', 'padding':'','text-decoration':''});
			lien.hide();
			
			// css permanent
			var dy = elmt.height();
			if (l_fin == 0 || l_fin == 1 || l_fin == 2) {
				elmt_rect.css({'position':'absolute', 'top':'0px', 'left': '0px', 'width': '10px', 'height':dy ,'z-index' : 1, 
					'background-color':couleur,'border-style':'solid','border-color':'transparent',  'border-width': '1px', 'border-radius': '5px'});
			} else if (l_fin == -1) {
				elmt_rect.css({'position':'absolute', 'top':'0px', 'left': '0px', 'width': '10px', 'height':dy ,'z-index' : 1, 
					'background-color':couleur,'border-style':'solid', 'border-color':'transparent', 'border-width': '1px', 'border-radius': '5px'});
				// ajout d'une flèche droite et d'un point d'interrogation (ainsi qu'un bouton en survol)
				elmt_compl.css({'position':'absolute','left':'0px' , 'width':0, 'height':0, 'border-left':dy+'px solid '+couleur, 
					'border-top':dy/2+1+'px solid transparent', 'border-bottom': dy/2+1+'px solid transparent' });
			} else if (l_fin == 3) {
				var haut = dy*2/3;
				var larg = haut*5/8;
				elmt_rect.css({'position':'absolute', 'left': -larg+'px', 'width':0, 'height':0, 'border-left':larg+'px solid transparent',
					'border-right':larg+'px solid transparent', 'border-bottom':haut+'px solid '+couleur,'z-index' : 1});
				elmt_compl.css({'position':'absolute', 'left': '0px','width':0, 'height':0, 'border-left':larg+'px solid transparent',
					'border-right':larg+'px solid transparent', 'border-top':haut+'px solid '+couleur, 'margin':haut*3/8+'px 0 0 -'+larg+'px','z-index' : 2});
			}
			
			var txt_wid = elmt_txt.outerWidth();
			
			switch (l_deb) {
			case 0 :
				elmt_fleche1.hide();
				move_deb.addClass('disp');
				break;	
			case 1 :
				elmt_fleche1.show();
				elmt_fleche1.css({'left': x0-12+'px'});
				break;	
			}

			switch (l_fin) {
			case 0 :
				elmt_fleche2.hide();
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});
				elmt_compl.hide();
				move_fin.addClass('disp');
				move_fin.css({'left': wid-10+'px', 'width':'2px'});	
				move_deb.css({'left': 8+'px', 'width':'2px'});
				break;	
			case 1 : // heure de fin au-delà de la timeline
				elmt_fleche2.show();
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});				
				elmt_compl.hide();
				elmt_fleche2.css({'left': x2+'px'});
				move_deb.css({'left': 8+'px', 'width':'2px'});
				break;
			case 2 : // évènement avant la timeline
				elmt_fleche2.show();
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});				
				elmt_compl.hide();
				elmt_fleche2.hide();
				elmt_txt.css({'color':'red'});
				break;
			case -1 : // pas d'heure de fin
				elmt_fleche2.hide();
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});
				elmt_compl.css({'left':x0+wid+5+'px'});
				move_deb.css({'left': 8+'px', 'width':'2px'});
				move_fin.addClass('disp');
				move_fin.css({'left': wid-10+'px', 'width':'2px'});
				elmt_fin.addClass('nodisp');
				break;
			case 3 : // ponctuel
				elmt_fleche1.hide();
				elmt_fleche2.hide();
				move_deb.removeClass('disp');
				elmt_deb.addClass('nodisp');
				var left_pos = elmt_rect.position().left;
				x0 += 2;
				x1 = x0 + left_pos;
				wid = 2*left_pos;
				x2 = x0 - left_pos;
				elmt_rect.css({'left': '+='+x0});
				elmt_compl.css({'left':x0+'px'});
				ponctuel = 1;
				break;
			}
			elmt_deb.find('i').removeClass("icon-question-sign icon-warning-sign icon-check");
			elmt_fin.find('i').removeClass("icon-question-sign icon-warning-sign icon-check");
			if (sts < 3) {
				elmt_deb.prepend(' <i class="icon-question-sign"></i>');
			} else if (sts == 3) { 
				elmt_deb.prepend(' <i class="icon-warning-sign"></i>');
				elmt_deb.show();
			} else if (sts != 20) {
				elmt_deb.prepend(' <i class="icon-check"></i>');
			}
			if (sts == 6) { 
				elmt_fin.append(' <i class="icon-warning-sign"></i>'); 
				elmt_fin.show(); 
			} else if (sts < 11) {
				elmt_fin.append(' <i class="icon-question-sign"></i>');
			} else if (sts < 20) {
				elmt_fin.append(' <i class="icon-check"></i>');
			} else if (sts == 20) {
				elmt_deb.addClass("disabled");
				elmt_fin.addClass("disabled");
				elmt_txt.css({'text-decoration':'line-through'});
			}
			elmt_arch.find('i').removeClass("icon-eye-open icon-eye-close");
			if (arch == 0) { elmt_arch.append(' <i class="icon-eye-close"></i>'); 
			} else {elmt_arch.append(' <i class="icon-eye-open"></i>'); 
			}
			
			if (ponctuel) {
				lien.addClass('lien');
				lien.show();
				x1 -= 30;
				b1_pos = x1+2;
				// on place l'heure à droite
				elmt_fin.css({'left': x2+'px'});
				elmt_txt.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
				txt_wid = elmt_txt.outerWidth();
				x2 += elmt_fin.outerWidth()+10;
				if (x2+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
					elmt_txt.css({'left': x2+'px'});
					lien.css({'left': x2-(elmt_fin.outerWidth()+10)+'px','width':elmt_fin.outerWidth()+10+'px'});	
					x2 += txt_wid;
				} else { // sinon on le met à gauche
					x1 -= txt_wid+2;
					elmt_txt.css({'left': x1+'px'});
					lien.css({'left': x1+'px','width':x0-x1+'px'});
				}
			} else {
				var lar_nec = txt_wid + 50;
				var x_left = x1+18;
				if (wid > lar_nec) {
					elmt_txt.css({'left': x_left+2+'px'});
					// on place l'heure de début à gauche
					x1 -= elmt_deb.outerWidth()+10;
					elmt_deb.css({'left': x1+'px'});
					// on place l'heure de fin à droite
					elmt_fin.css({'left': x2+5+'px'});
					x2 += elmt_fin.outerWidth()+10;
				} else {
					lien.addClass('lien');
					lien.show();
					elmt_txt.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
					txt_wid = elmt_txt.outerWidth();
					// on place l'heure de début à gauche
					x1 -= elmt_deb.outerWidth()+10;
					elmt_deb.css({'left': x1+'px'});
					// on place l'heure de fin à droite
					elmt_fin.css({'left': x2+5+'px'});
					x2 += elmt_fin.outerWidth()+10;
					if (x2+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
						dr = 1;
						elmt_txt.css({'left': x2+'px'});
						lien.css({'left': x2-(elmt_fin.outerWidth()+10)+'px','width':elmt_fin.outerWidth()+10+'px'});	
					} else { // sinon on le met à gauche
						dr = 0;
						lien.css({'left': x1-2+'px','width':x0-x1+'px'});
						x1 -= txt_wid+2;
						elmt_txt.css({'left': x1+'px'});
					}
				} 
			}
			elmt_status.css({'left': b1_pos+'px'});
			timeline.set_status(base_element, id, type);
			if (warn == 1) {
				warn_content.text("Confirmer l'événement ? (début à xx:xx)");
				warn_content.css({'font-size':'18px', 'margin':'auto'});
				warn_buttons.css({'margin':'auto'});
			} else if (warn == 2) {
				warn_content.text("Confirmer la fin de l'événement ? (fin à xx:xx)");
				warn_content.css({'font-size':'18px'});
				warn_buttons.css({'width':'100%'});
			}
			elmt_opt.css({'left':x0, 'width':x2-x0});
			elmt_write.css({'position':'relative','rows':3, 'width':'90%'});
			elmt.css({'left':x1+'px', 'width': x2-x1});
			elmt.children().css({'left':'-='+x1+'px'});
			if (dr == 1) {elmt_deb.css({'left':0+'px'});} else {elmt_txt.css({'left':0+'px'});}
			
			if (mod == 0) {
				elmt_b1.addClass("invisible");
				elmt_b2.addClass("invisible");
				elmt_arch.addClass("invisible");
				move_deb.removeClass('disp');
				move_fin.removeClass('disp');
				elmt_deb.addClass("disabled");
				elmt_fin.addClass("disabled");
			}
			
		},
		// texte supplémentaire déplié. Option pas utilisée pour le moment.
		option_open: function(this_elmt, timeline_content, speed) {
			var h = this_elmt.position().top;
			var min = this_elmt.find('.moins');
			min.show();
			min.addClass('show');
			var elmt_opt = this_elmt.find('.elmt_opt');
			var dh = elmt_opt.outerHeight();
			this_elmt.css({'height':'+='+dh});
			var all_elmt = timeline_content.find('.elmt');
			all_elmt.each(function(index, elmt){
				if ($(elmt).position().top > h) {
					if (speed) {
						$(elmt).css({'top': '+='+dh});
					} else {
						$(elmt).animate({'top': '+='+dh});
					}
				}
			});
			var all_cat = timeline_content.find('.categorie');
			var top;
			all_cat.each(function(index, cat){
				top = $(cat).position().top;
				if (top > h) {
					if (speed) {
						$(cat).css({'top': '+='+dh});
					} else {
						$(cat).animate({'top': '+='+dh});
					}

				} else if (top + $(cat).height() > h) {
					if (speed) {
						$(cat).css({'height':'+='+dh});
					} else {
						$(cat).animate({'height':'+='+dh});
					}
				}
			});
			if (speed) {
				elmt_opt.show();
			} else {
				elmt_opt.slideDown();
			}
		},
		// saisie du texte des champs heure et du champ label
		enrichir_contenu: function (base_element, id, d_debut, d_fin, label) {
			var elmt = base_element.find('.ident'+id);
			var elmt_txt = elmt.find('.label_elmt');
			var elmt_deb = elmt.find('.elmt_deb');
			var elmt_fin = elmt.find('.elmt_fin');
			// positionnement des différents objets sur la ligne elmt
			elmt_txt.css({'position': 'absolute', 'white-space': 'nowrap', 'font-weight':'bold', 'width':'auto'});
			var h1, h2, hDeb, hFin;
			// ajout de l'heure de début
			if (d_debut != d_fin) {
				var deb_min = d_debut.getMinutes();
				if (d_debut.getMinutes() < 10) {deb_min = "0"+d_debut.getMinutes();} 
				var hDeb = d_debut.getUTCHours()+":"+deb_min;						
				if (d_debut < d_ref_deb && d_debut.getDate() != d_actuelle.getDate()){ 
					
					var dDeb = d_debut.getUTCMonth()+1;
					if (dDeb > 10) {hDeb = '<span style="display: inline-block; vertical-align: middle;">'+d_debut.getUTCDate()+"/"+dDeb+"<br/>"+hDeb+'</span>';} 
					else {hDeb = '<span style="display: inline-block; vertical-align: middle;">'+d_debut.getUTCDate()+"/0"+dDeb+"<br/>"+hDeb+'</span>';}
					var h1 = 0;
				} else {
					h1 = 6;
				}
			} else { hDeb = ""; }
			elmt_deb.html(" "+hDeb);
			var data_deb = elmt_deb[0];
			jQuery.data(data_deb,"d_deb",d_debut);
			elmt_deb.css({'top':h1+'px'});
			// ajout de l'heure de fin
			if (d_fin > 0) {
				var fin_min = d_fin.getMinutes();
				if (d_fin.getMinutes() < 10) { fin_min = "0"+d_fin.getMinutes(); }
				var hFin = d_fin.getUTCHours()+":"+fin_min;
				if (d_fin.getDate() != d_actuelle.getDate()){
					var dFin = d_fin.getUTCMonth()+1;
					if (dFin > 10) {hFin = '<span style="display: inline-block; vertical-align: middle;">'+d_fin.getUTCDate()+"/"+dFin+"<br/>"+hFin+'</span>'; }
					else {hFin = '<span style="display: inline-block; vertical-align: middle;">'+d_fin.getUTCDate()+"/0"+dFin+"<br/>"+hFin+'</span>'; }
					h2 = 0;
				} else {
					h2 = 6;
				}
			} else { 
				h2 = 4;
				hFin = ""; 
			}
			elmt_fin.html(hFin+" ");
			var data_fin = elmt_fin[0];
			jQuery.data(data_fin,"d_fin",d_fin);
			elmt_fin.css({'top':h2+'px'});
		},
		// affichage en fonction du statut (à modifier)
		set_status: function (base_element, id, type) {
			var sts = type[3];
			var elmt = base_element.find('.ident'+id);
			var elmt_status = elmt.find('.elmt_status');
			switch (sts) {
			case 0:
				elmt_status.addClass('btn-info');
				elmt_status.text("nouveau");
				break;
			case 10:
				elmt_status.addClass('btn-success');
				elmt_status.text("confirmé");
				break;
			case 11:
				elmt_status.addClass('btn-success');
				elmt_status.text("terminé");
				break;
			case 2:
				elmt_status.addClass('btn-warning');
				elmt_status.text("nouveau");
				elmt_status.show();
				break;
			case 3:
				elmt_status.addClass('btn-danger');
				elmt_status.text("nouveau");
				elmt_status.show();
				break;
			case 4:
				elmt_status.addClass('btn-info');
				elmt_status.text("confirmé");
				break;
			case 5:
				elmt_status.addClass('btn-warning');
				elmt_status.text("confirmé");
				elmt_status.show();
				break;
			case 6:
				elmt_status.addClass('btn-danger');
				elmt_status.text("confirmé");
				elmt_status.show();
				break;
			}
		},
		// association d'une valeur de status en fonction du statut de l'évènement et de l'heure
		type_elmt: function (id, d_debut, d_fin, ponct, etat) {
			var l_deb = 0; 
			var l_fin = 0;
			var warn;
			var sts;
			if (ponct == 0) {
				if (d_fin-d_debut == 0) {
					l_fin = 3;
				} else {
					// flèche gauche
					if (d_debut < d_ref_deb) { l_deb = 1;}
					// flèche droite
					if (d_fin > d_ref_fin) { l_fin = 1;}
					if (d_fin < d_ref_deb) { l_fin = 2;}
					// pas de fin
					if (d_fin == -1) { l_fin = -1; }
				}
			} else { 
				// ponctuel
				l_fin = 3;
			}
			var d_temp = new Date();
			d_temp.setTime(d_actuelle.getTime()+300000);

			if (etat == "Annulé") { sts = 20; warn = 0; 
			} else {
				if (d_debut > d_temp) {
					if (etat == "Nouveau") {
						sts = 0; // Nouveau pas commencé
						warn = 0;
					} else {
						if (etat == "Confirmé") {
							sts = 10; // Confirmé pas commencé
						} else if (etat == "Terminé") {
							sts = 11; // Terminé pas commencé
						}
						warn = 0;
					}
				} else if (d_debut > d_actuelle) {
					if (etat == "Nouveau") {
						sts = 2; // Nouveau et proche de commencer
						warn = 0;
					} else {
						if (etat == "Confirmé") {
							sts = 10; // Confirmé pas commencé
						} else if (etat == "Terminé") {
							sts = 11; // Terminé pas commencé
						}
						warn = 0;
					}
				} else if (d_fin == -1) { 
					if (etat == "Nouveau") {
						warn = 1;
						sts = 3; // Nouveau et commencé
					} else if (etat == "Confirmé") {
						sts = 4; // Confirmé et commencé
						warn = 0;
					}
				} else {
					if (etat == "Nouveau") {
						warn = 1;
						sts = 3; // Nouveau et commencé
					} else {
						if (d_fin < d_actuelle) {
							if (etat == "Confirmé") {
								sts = 6; // Confirmé et terminé
								warn = 2;
							} else {
								sts = 11; // Terminé et bientot terminé
								warn = 0;
							}
						} else if (d_fin < d_temp) {
							if (etat == "Confirmé") {
								sts = 5; // Confirmé et bientot terminé
								warn = 2;
							} else {
								sts = 11; // Terminé et bientot terminé
								warn = 0;
							}
						} else if (d_fin > d_actuelle) {
							if (etat == "Confirmé") {
								sts = 4; // Confirmé et commencé
								warn = 0;
							} else if (etat == "Terminé") {
								sts = 11; // Terminé et commencé
								warn = 0;
							} 
						}
					}
				}
			}
			warn = 0;
			return [l_deb, l_fin, warn, sts];
		},
		// création d'un évènement timeline supplémentaire
		create_elmt: function (base_element, id, d_debut, d_fin, ponct, label, impt, cat, mod, etat) {
			var ind = categorie.indexOf(cat);
			var couleur = cat_coul[ind];
		//	dy = impt;
			dy = dy_max;
			if (d_fin > 0) {
				var coord = timeline.position(d_debut, d_fin);
			} else {
				coord = timeline.position(d_debut, -1); 
			}
			var x0 = coord[0];
			var wid = coord[1];
			var type = timeline.type_elmt(id, d_debut, d_fin, ponct, etat);
			timeline.creation_ligne(base_element, id, label, y_temp, dy, type, couleur);
			timeline.enrichir_contenu(base_element, id, d_debut, d_fin, label);
			timeline.position_ligne(base_element, id, type, x0, wid, impt, mod, couleur);
			y_temp += dy + delt_ligne;
		},
		// création d'un évènement timeline supplémentaire
		add_elmt: function (base_element, id, d_debut, d_fin, ponct, label, impt, cat, mod, etat) {
			var ind = categorie.indexOf(cat);
			var couleur = cat_coul[ind];
		//	dy = impt;
			dy = dy_max;
			if (d_fin > 0) {
				var coord = timeline.position(d_debut, d_fin);
			} else {
				coord = timeline.position(d_debut, -1); 
			}
			var x0 = coord[0];
			var wid = coord[1];
			var type = timeline.type_elmt(id, d_debut, d_fin, ponct, etat);
			timeline.creation_ligne(base_element, id, label, 0, dy, type, couleur);
			timeline.enrichir_contenu(base_element, id, d_debut, d_fin, label);
			timeline.position_ligne(base_element, id, type, x0, wid, impt, mod, couleur);
			if (tri_cat) { 
				timeline.tri_cat(base_element, tab,1);
			} else if (tri_hdeb) {
				timeline.tri_hdeb(base_element, tab,1);
			}
			elmt = base_element.find('.ident'+id);
			elmt.effect( "highlight",4000);
		},
		// mise à jour d'un évènement sur la timeline 
		update_elmt: function (base_element, id, d_debut, d_fin, ponct, label, impt, cat, mod, etat) {
			var ind = categorie.indexOf(cat);
			var couleur = cat_coul[ind];
		//  dy = impt;
			dy = dy_max;
			if (d_fin > 0) {
				var coord = timeline.position(d_debut, d_fin);
			} else {
				coord = timeline.position(d_debut, -1); 
			}
			var x0 = coord[0];
			var wid = coord[1];
			var type = timeline.type_elmt(id, d_debut, d_fin, ponct, etat);
			var elmt = base_element.find('.ident'+id);
			var y = elmt.position().top;
			timeline.enrichir_contenu(base_element, id, d_debut, d_fin, label);
			timeline.position_ligne(base_element, id, type, x0, wid, impt, mod, couleur);
		},
		// informations d'un évènement modifié
		modify: function (data, loc) {
			var i = 0;
			var d_debut, d_fin;
			var j;
			var id = -1;
			var len = tab.length;
			$.each(data, function(key, value) {
				d_debut = new Date(value.start_date);
				if (value.punctual == true) {
					d_fin = d_debut;
				} else {
					if (value.end_date == null) { 
						d_fin = -1;
					} else {
						d_fin = new Date(value.end_date);
					}
				}
				j = 0;
				while (j < len && id == -1) {
					if (tab[j][0] == key) { id = j; }
					j++;
				}
				var ponct = value.punctual;
				var label = value.name;
				var etat = value.status_name;
				var cat = value.category_root;
				var impt = value.archived;
				var mod = value.modifiable;
				tab[id] = [key, d_debut, d_fin, ponct, label, impt, cat,mod, etat];
				$('#cpt_evts').text(cpt_journee.length);
				var timel = $('#timeline');
				var base_element = timel.find('.Base');
				var timeline_content = timel.find('.timeline_content');
				var elmt = timeline_content.find('.ident'+key);
				if (d_fin >0 && d_fin < d_ref_deb && (etat == "Terminé" || etat == "Annulé")) { 
					elmt.remove();
				} else if (d_debut > d_ref_fin) {
					elmt.remove();
				} else {
					timeline.update_elmt(timeline_content, key, d_debut, d_fin, ponct, label, impt, cat, mod, etat);
				}
				if (tri_cat) { 
					timeline.tri_cat(timeline_content, tab,1);
				} else if (tri_hdeb) {
					timeline.tri_hdeb(timeline_content, tab,1);
				}
				elmt = timeline_content.find('.ident'+key);
				if (loc) {
					// elmt.effect( "highlight", 4000);
				} else {
					elmt.addClass("changed");
					elmt.css({'background-color':'yellow'});
				}
				i ++;
			});

		},
		// informations d'un évènement ajouté
		add: function (data) {
			var i = 0;
			var d_debut, d_fin;
			var len = tab.length;
			var d_now  = new Date();
			$.each(data, function(key, value) {
				d_debut = new Date(value.start_date);
				if (value.punctual == true) {
					d_fin = d_debut;
				} else {
					if (value.end_date == null) { 
						d_fin = -1;
					} else {
						d_fin = new Date(value.end_date);
					}
				}
				j = 0;
				var ponct = value.punctual;
				var label = value.name;
				var etat = value.status_name;
				var cat = value.category_root;
				var impt = value.archived;
				var mod = value.modifiable;
				tab[len] = [key, d_debut, d_fin, ponct, label, impt, cat,mod, etat];
				corresp[key] = len;
				if (d_fin == -1 || (d_debut < d_now && d_fin > d_now) || 
						(d_debut.toLocaleDateString() == d_now.toLocaleDateString()) ||
						(d_fin.toLocaleDateString() == d_now.toLocaleDateString())) {
					cpt_journee.push(i);
				}
				$('#cpt_evts').text(cpt_journee.length);
				var timel = $('#timeline');
				var base = timel.find('.Base');
				var timeline_content = timel.find('.timeline_content');
				//var other = timel.find('.timeline_other');
				if (d_fin >0 && d_fin < d_ref_deb && (etat == "Terminé" || etat == "Annulé")) { 
					if (d_fin > d_min) {
						liste_passee.push(len);
					}
				} else if (d_debut > d_ref_fin) {
					if (d_debut < d_max) {
						liste_avenir.push(len);
					}
				} else {
					liste_affichee.push(len);
					timeline.add_elmt(timeline_content, key, d_debut, d_fin, ponct, label, impt, cat, mod, etat);
				}
				i ++;
			});
		},
		// mise à jour de la timeline (déplacement de la timeBar uniquement ou réaffichage complet toutes les heures).
		update: function(timel) {
			var h_ref_old = h_ref;
			if (vue) {timeline.init_journee(timel);} else {timeline.init(timel);}
			var timeline_content = timel.find('.timeline_content');
			timeline.maj_timeBar(timeline_content);
			if (h_ref_old != h_ref) { 
				var base = timel.find('.Base');
				//var other = timel.find('.timeline_other');
				$.holdReady(true);
				base.empty();
				timeline_content.empty();
				//other.empty();
				timeline.base(base);	
				var res = timeline.create(timeline_content, tab);
				if (tri_cat) { 
					timeline.tri_cat(timeline_content, tab,1);
				} else if (tri_hdeb) {
					timeline.tri_hdeb(timeline_content, tab,1);
				}
				timeline.timeBar(timeline_content);
				$.holdReady(false);
			} else {
				var len = liste_affichee.length;
				for (var i=0; i<len; i++) {
					var n = liste_affichee[i];
					var type = timeline.type_elmt(tab[n][0],tab[n][1], tab[n][2],tab[n][3],tab[n][8]);
					timeline.set_status(timeline_content, tab[n][0],type);
				}
			}
		},
};

$(document).ready(function() {	

	$('#timeline').attr('unselectable','on')
    .css({'-moz-user-select':'-moz-none',
          '-moz-user-select':'none',
          '-o-user-select':'none',
          '-khtml-user-select':'none', /* you could also put this in a class */
          '-webkit-user-select':'none',/* and add the CSS class here instead */
          '-ms-user-select':'none',
          'user-select':'none'
    }).bind('selectstart', function(){ return false; });
	
	// mise à jour toutes les minutes 
	setInterval("timeline.update($('#timeline'))", 60000);

	// affichage enrichi d'un évènement survolé
	$('#timeline').on('mouseenter','.elmt', function(){
		var elmt = $(this);
		if (elmt.hasClass('changed')) {
			elmt.css({'background-color':'transparent'});
			elmt.removeClass('changed');
		}
		elmt.css({'z-index':11});
		elmt.find('.modify-evt').show();
		elmt.find('.checklist-evt').show();
		elmt.find('.archive-evt').show();
		elmt.find('.elmt_status').show();
		elmt.find('.show').show();
		var elmt_deb = elmt.find('.elmt_deb');
		if (! elmt_deb.hasClass('nodisp')) {elmt_deb.show();}
		var elmt_fin = elmt.find('.elmt_fin');
		if (! elmt_fin.hasClass('nodisp')) {elmt_fin.show();}
		elmt.find('.elmt_qm_fleche').hide();
		elmt.find('.lien').hide();
		var move_fin = elmt.find('.move_fin');
		if (move_fin.hasClass('disp')) {move_fin.show();}
		var move_deb = elmt.find('.move_deb');
		if (move_deb.hasClass('disp')) {move_deb.show();}
	});
	// affichage normal en sortie d'évènement
	$('#timeline').on('mouseleave','.elmt',function(){
		var elmt = $(this);
		elmt.css({'z-index':1});
		elmt.find('.modify-evt').hide();
		elmt.find('.checklist-evt').hide();
		elmt.find('.archive-evt').hide();
		var elmt_status = elmt.find('.elmt_status');
		if (!(elmt_status.hasClass('btn-warning') || elmt_status.hasClass('btn-danger'))) { elmt_status.hide(); }
		elmt.find('.show').hide();
		var elmt_deb = elmt.find('.elmt_deb');
		if (! elmt_deb.find('i').hasClass("icon-warning-sign")) {elmt_deb.hide();}
		var elmt_fin = elmt.find('.elmt_fin');
		if (! elmt_fin.find('i').hasClass("icon-warning-sign")) {elmt_fin.hide();}
		elmt.find('.elmt_qm_fleche').show();
		elmt.find('.lien').show();
		elmt.find('.move_fin').hide();
		elmt.find('.move_deb').hide();
	});

	// clic sur un statut... à coder
	$('#timeline').on('click','.elmt_status', function(){
		var this_elmt = $(this).closest('.elmt');
	});
	$('#timeline').on('click','.cancel_status', function(){
		var this_elmt = $(this).closest('.elmt');
	});
	
	// ouverture des éléments enrichis après clic sur "+". Désactivé pour le moment
	$('#timeline').on('click','.plus', function(){
		$(this).hide();
		$(this).removeClass('show');
		var timeline_content = $(this).closest('.timeline_content');
		var this_elmt = $(this).closest('.elmt');
		$(this).addClass('opt_open');
		timeline.option_open(this_elmt, timeline_content, 0);
	});
	$('#timeline').on('click','.moins', function(){
		$(this).hide();
		$(this).removeClass('show');
		var timeline_content = $(this).closest('.timeline_content');
		var this_elmt = $(this).closest('.elmt');
		var h = this_elmt.position().top;
		var plus = this_elmt.find('.plus');
		plus.show();
		plus.addClass('show');
		plus.removeClass('opt_open');
		var elmt_opt = this_elmt.find('.elmt_opt');
		var dh = elmt_opt.outerHeight();
		var all_elmt = timeline_content.find('.elmt');
		this_elmt.css({'height':'-='+dh});
		all_elmt.each(function(index, elmt){
			if ($(elmt).position().top > h) {
				$(elmt).animate({'top': '-='+dh});
			} 
		});
		var top;
		var all_cat = timeline_content.find('.categorie');
		all_cat.each(function(index, cat){
			top = $(cat).position().top;
			if (top > h) {
				$(cat).animate({'top': '-='+dh});
			} else if (top + $(cat).height() > h) {
				$(cat).animate({'height':'-='+dh});
			}
		});
		elmt_opt.slideUp();	
	});

	
	// changement de zoom sur la timeline
	$('#zoom').on('switch-change', function(e, data){
		var timel = $('#timeline');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		$.holdReady(true);
		base.empty();
		timeline_content.empty();
		if(data.value){
			timeline.init_journee(timel);
		} else {
			timeline.init(timel);
		}
		timeline.base(base);
		timeline.create(timeline_content, tab);
		if (tri_cat) { 
			timeline.tri_cat(timeline_content, tab,1);
		} else if (tri_hdeb) {
			timeline.tri_hdeb(timeline_content, tab,1);
		}
		timeline.timeBar(timeline_content);
		$.holdReady(false);
	});
	
	// activation / désactivation du tri par catégories
	$('#tri_cat').on('click',function(){
		var timel = $('#timeline');
		$(this).parent().addClass('active');
		$('#tri_deb').parent().removeClass('active');
		var timeline_content = timel.find('.timeline_content');
		timeline.tri_cat(timeline_content, tab);
	});

	// activation / désactivation du tri par heure
	$('#tri_deb').on('click', function(){
		var timel = $('#timeline');
		$(this).parent().addClass('active');
		$('#tri_cat').parent().removeClass('active');
		var timeline_content = timel.find('.timeline_content');
		timeline.tri_hdeb(timeline_content, tab, 0);
	});

	// activation / désactivation du tri par statut
	$('#tri_tout').on('click', function(){
		$(this).parent().addClass('active');
		$('#tri_std').parent().removeClass('active');
		var timel = $('#timeline');
		var timeline_content = timel.find('.timeline_content');
		aff_archives = 1;
		if (tri_cat) { 
			timeline.tri_cat(timeline_content, tab,1);
		} else if (tri_hdeb) {
			timeline.tri_hdeb(timeline_content, tab,1);
		}
	});
	
	$('#tri_std').on('click', function(){
		var timel = $('#timeline');
		$(this).parent().addClass('active');
		$('#tri_tout').parent().removeClass('active');
		var timeline_content = timel.find('.timeline_content');
		aff_archives = 0;
		if (tri_cat) { 
			timeline.tri_cat(timeline_content, tab,1);
		} else if (tri_hdeb) {
			timeline.tri_hdeb(timeline_content, tab,1);
		}
	});
	
	// sauvegarde du texte enrichi. Pas utilisé
	$('#timeline').on('mouseleave','.elmt_write', function(){
		$(this).val(); // valeur à sauvegarder
	});
	
	// clic sur "archiver"
	$('#timeline').on('click','.archive-evt', function(e1){
		var elmt = $(this).closest('.elmt');
		var ss_elmt = elmt[0];
		var id = jQuery.data(ss_elmt, "ident");
		var n = corresp[id];	
		if (tab[n][5] == 0) { 
			tab[n][5] = 1; 
		} else { 
			tab[n][5] = 0;
		}
		$.post(ini_url+'/changefield?id='+id+'&field=archived&value='+tab[n][5], 
				function(data){
			displayMessages(data.messages);
			if (data['event']) {
				timeline.modify(data.event, 1);
			}
		});
	});
	
	// clic sur heure de début
	$('#timeline').on('click','.elmt_deb', function(e1){
		var elmt = $(this).closest('.elmt');
		var elmt_deb = elmt.find('.elmt_deb');
		var ss_elmt = elmt[0];
		var id = jQuery.data(ss_elmt, "ident");
		var n = corresp[id];	
		if (tab[n][8] == "Nouveau") { 
			tab[n][8] = "Confirmé"; 
		}
		$.post(ini_url+'/changefield?id='+id+'&field=status&value='+tab[n][8], 
				function(data){
			displayMessages(data.messages);
			if (data['event']) {
				timeline.modify(data.event, 1);
			}
		});
	});
	
	// clic sur heure de fin
	$('#timeline').on('click','.elmt_fin', function(e1){
		var elmt = $(this).closest('.elmt');
		var elmt_deb = elmt.find('.elmt_deb');
		var elmt_fin = elmt.find('.elmt_fin');
		var ss_elmt = elmt[0];
		var id = jQuery.data(ss_elmt, "ident");
		var n = corresp[id];	
		if (tab[n][8] == "Nouveau" || tab[n][8] == "Confirmé") { 
			tab[n][8] = "Terminé"; 
		}
		$.post(ini_url+'/changefield?id='+id+'&field=status&value='+tab[n][8], 
				function(data){
			displayMessages(data.messages);
			if (data['event']) {
				timeline.modify(data.event, 1);
			}
		});
	});
	
	
	// Déplacement de l'heure de fin
	$('#timeline').on('mousedown','.move_fin', function(e1){
			on_drag = 2;
			var x_ref = e1.clientX;
			var x_temp = x_ref;
			var delt, delt2;
			var elmt = $(this).closest('.elmt');
			elmt.addClass('on_drag');
			var ss_elmt = elmt[0];
			var id = jQuery.data(ss_elmt, "ident");
			var n = corresp[id];
			// if (tab[n][8] == "Confirmé") { tab[n][8] = "Terminé"; }
			// $.post(ini_url+'/changefield?id='+id+'&field=status&value='+tab[n][8], function(data){displayMessages(data);});
			var rect_elmt = elmt.find('.rect_elmt');
			elmt.find('.complement').hide();
			var rect_width = rect_elmt.width();
			var move_fin = $(this);
			var pix_time = 30*60000/lar_unit;
			var elmt_fin = elmt.find('.elmt_fin');
			elmt_fin.show();
			var data_fin = elmt_fin[0];
			var d_fin = new Date();
			temp_fin = new Date();
			aff_fin = new Date();
			if (tab[n][2] < 0) { 
				temp_fin.setTime(d_ref_fin.getTime());
				aff_fin.setTime(d_ref_fin.getTime());
				aff_fin.setHours(aff_fin.getUTCHours());
				elmt_fin.text(aff_fin.toLocaleTimeString().substr(0,5)+" ");
				d_fin.setTime(temp_fin.getTime());
				tab[n][2] = temp_fin;
			} else {
				temp_fin.setTime(d_fin.getTime());
				aff_fin.setTime(d_fin.getTime());
				d_fin = jQuery.data(data_fin,"d_fin");
			}
			$('#timeline').mousemove(function(e2) {
			//	e2.preventDefault();
				delt = e2.clientX-x_temp;
				delt2 = e2.clientX-x_ref;
				if (rect_width + delt2 > 0) {
					temp_fin.setTime(d_fin.getTime()+delt2*pix_time);
					aff_fin.setTime(d_fin.getTime()+delt2*pix_time);
					aff_fin.setHours(aff_fin.getUTCHours());
					elmt_fin.text(aff_fin.toLocaleTimeString().substr(0,5)+" ");
					x_temp = e2.clientX;
					elmt.css({'width':'+='+delt});
					rect_elmt.css({'width':'+='+delt});
					elmt_fin.css({'left':'+='+delt});
					move_fin.css({'left':'+='+delt});
				}
			});
	});
	
	// enregistrement des heure de début ou fin
	$('#timeline').on('mouseup', function(){
		$('#timeline').unbind('mousemove');
		var timeline_content = $('#timeline').find('.timeline_content');
		var elmt = timeline_content.find('.on_drag');
		if (elmt[0] != null){
			var ss_elmt = elmt[0];
			elmt.removeClass('on_drag');
			var id = jQuery.data(ss_elmt, "ident");
			var n = corresp[id];
			if (on_drag == 1) {
				tab[n][1] = temp_deb;
			//	timeline.update_elmt(timeline_content, id, tab[n][1], tab[n][2], tab[n][3], tab[n][4], tab[n][5], tab[n][6], tab[n][7], tab[n][8]);
				$.post(ini_url+'/changefield?id='+id+'&field=startdate&value='+temp_deb.toUTCString(), 
						function(data){
					displayMessages(data.messages);
					if (data['event']) {
						timeline.modify(data.event, 1);
					}
				});
			} else if (on_drag == 2) {
				tab[n][2] = temp_fin;
			//	timeline.update_elmt(timeline_content, id, tab[n][1], tab[n][2], tab[n][3], tab[n][4], tab[n][5], tab[n][6], tab[n][7], tab[n][8]);
				$.post(ini_url+'/changefield?id='+id+'&field=enddate&value='+temp_fin.toUTCString(), 
						function(data){
					displayMessages(data.messages);
					if (data['event']) {
						timeline.modify(data.event, 1);
					}	
				});
				
			}
		}
		on_drag = 0;
	});

	// Déplacement de l'heure de debut
	$('#timeline').on('mousedown','.move_deb', function(e1){
			on_drag = 1;	
			var x_ref = e1.clientX;
			var x_temp = x_ref;
			var delt, delt2;
			var elmt = $(this).closest('.elmt');
			elmt.addClass('on_drag');
			var ss_elmt = elmt[0];
			var id = jQuery.data(ss_elmt, "ident");
			var n = corresp[id];
			if (tab[n][8] == "Nouveau") { tab[n][8] = "Confirmé"; }
			var move_fin = elmt.find('.move_fin'); 
			var rect_elmt = elmt.find('.rect_elmt');
			var elmt_compl = elmt.find('.complement');
			var rect_width = rect_elmt.width();
			var elmt_deb = elmt.find('.elmt_deb');
			elmt_deb.show();
			var pix_time = 30*60000/lar_unit;
			var elmt_fin = elmt.find('.elmt_fin');
			var data_deb = elmt_deb[0];
			var d_deb = jQuery.data(data_deb,"d_deb");
			temp_deb = new Date();
			temp_deb.setTime(d_deb.getTime());		
			aff_deb = new Date();
			aff_deb.setTime(d_deb.getTime());
			$('#timeline').mousemove(function(e2) {
				delt = e2.clientX-x_temp;
				delt2 = e2.clientX-x_ref;
				if (delt2 < rect_width) {
					temp_deb.setTime(d_deb.getTime()+delt2*pix_time);
					aff_deb.setTime(d_deb.getTime()+delt2*pix_time);
					aff_deb.setHours(aff_deb.getUTCHours());
					elmt_deb.text(" "+aff_deb.toLocaleTimeString().substr(0,5));
					x_temp = e2.clientX;
					elmt.css({'left':'+='+delt, 'width':'-='+delt});
					rect_elmt.css({'width':'-='+delt});
					elmt_compl.css({'left':'-='+delt});
					elmt_fin.css({'left':'-='+delt});
					move_fin.css({'left':'-='+delt});
				}
			});
	});
	
	// retracé de la timeline si taille fenêtre modifiée
	$(window).resize(function () {
		var timel = $('#timeline');
                timel.css('height', $(window).height()-82+'px');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		//var other = timel.find('.timeline_other');
		$.holdReady(true);
		base.empty();
		timeline_content.empty();
		//other.empty();
		if(vue == 1){
			timeline.init_journee(timel);
		} else {
			timeline.init(timel);
		}
		timeline.base(base);	
		timeline.create(timeline_content, tab);
		if (tri_cat) { 
			timeline.tri_cat(timeline_content, tab,1);
		} else if (tri_hdeb) {
			timeline.tri_hdeb(timeline_content, tab,1);
		}
		timeline.timeBar(timeline_content);
		$.holdReady(false);
	});
	

/*	$('#timeline').on('mouseenter','.warn_elmt', function(){
		$(this).css({'z-index':3});
		$(this).find('.label_elmt').css({'font-weight':'bold'});
	});
	$('#timeline').on('mouseleave','.warn_elmt',function(){
		$(this).css({'z-index':1});
		$(this).find('.label_elmt').css({'font-weight':'normal'});
	});*/
	
/*} else {
	var lar_nec = 30*2+txt_wid+10;
	if (warn > 0) {lar_nec += 30; }
	var x_left = x1;
	if (wid > lar_nec) {
		elmt_b1.css({'left': x_left+2+'px'});
		b1_pos = x_left+2;
		x_left += 30;
		elmt_b2.css({'left': x_left+2+'px'});
		elmt_b2bis.css({'left': x_left+2+'px'});
		x_left += 30;
		if (warn > 0) { 
			elmt_warn.css({'left': x_left+2+'px'});
			x_left += 30; 
		}
		elmt_txt.css({'left': x_left+2+'px'});
		// on place l'heure de début à gauche
		x1 -= 50;
		elmt_deb.css({'left': x1+'px'});
		// on place l'heure de fin à droite
		elmt_fin.css({'left': x2+5+'px'});
		x2 += 50;
	} else {
		lar_nec = txt_wid+10;
		if (wid > lar_nec) {
			if (warn > 0 && wid-lar_nec > 30) {
				elmt_warn.css({'left': x0+2+'px'});
				x0 += 30;
			} else  {
				x1 -= 30;
				if (warn > 0) { 
					elmt_warn.css({'left': x1+2+'px'});
					x1 -= 30; 
				}
			}
			elmt_b2.css({'left': x1+2+'px'});
			elmt_b2bis.css({'left': x1+2+'px'});
			x1 -= 30;
			elmt_b1.css({'left': x1+2+'px'});
			b1_pos = x1+2;
			// on place l'heure de début à gauche
			x1 -= 50;
			elmt_deb.css({'left': x1+'px'});
			// on place l'heure à droite
			elmt_fin.css({'left': x2+5+'px'});
			x2 += 50;
			elmt_txt.css({'left': x0+2+'px'});
		} else {
			if ((wid > 30*2 && warn == 0) || (wid > 30*3 && warn > 0)) {
				elmt_b1.css({'left': x0+2+'px'});
				b1_pos = x0+2;
				elmt_b2.css({'left': x0+32+'px'});
				elmt_b2bis.css({'left': x0+32+'px'});
				if (warn > 0) { 
					elmt_warn.css({'left': x0+62+'px'}); 
				} 
			}	else {
				if (warn > 0) {
					if (wid > 30) {
						elmt_warn.css({'left': x0+2+'px'});
					} else {
						x1 -= 30;
						elmt_warn.css({'left': x1+2+'px'});
					}
				}
				x1 -= 30;
				elmt_b2.css({'left': x1+2+'px'});
				elmt_b2bis.css({'left': x1+2+'px'});
				x1 -= 30;
				elmt_b1.css({'left': x1+2+'px'});
				b1_pos = x1+2;
			}*/
	
	
/*	affiche_listes: function (element) {
		$('#cpt_evts').text(cpt_journee.length);
		$(element).empty();
		var nb1 = liste_passee.length;
		var nb2 = liste_avenir.length;
		var button1;
		if (nb1 == 0) {
			button1 = $('<button type="button" class="passee" disabled><strong>'+nb1+'</strong></button>');
			button1.css({'position':'absolute', 'top': '0px', 'left':'0px', 'width':'30px','height':hauteur, 'text-align':'center', 'z-index':5});
			$(element).append(button1);
		} else {
			button1 = $('<button type="button" class="passee"><strong>'+nb1+'</strong></button>');
			$(element).append(button1);
			button1.css({'position':'absolute', 'top': '0px', 'left':'0px', 'width':'30px','height':hauteur, 'text-align':'center', 'z-index':5});
			button1.append('<i class="icon-chevron-right"></i>');
			var liste1 = $('<div class="liste_passee">');
			$(element).append(liste1);
			for (var i=0; i<nb1; i++) {
				liste1.append($('<div>'+tab[liste_passee[i]][4]+'</div>'));
			}
			liste1.css({'display':'none','position':'absolute','top':'0px','left':'0px','width':'auto','height':hauteur,'text-align':'left','z-index':5,
				'background-color':'LemonChiffon', 'padding':'5px', 'white-space':'nowrap', 'border-style':'solid', 'border-width': '1px', 'border-radius': '2px' });
		}
		var button2;
		if (nb2 == 0) {
			button2 = $('<button type="button" class="avenir" disabled><strong>'+nb2+'</strong></button>');
			button2.css({'position':'absolute', 'top': '0px', 'right':'0px', 'width':'30px','height':hauteur, 'text-align':'center', 'z-index':5});
			$(element).append(button2);
		} else {
			button2 = $('<button type="button" class="avenir"><strong>'+nb2+'</strong></button>');
			$(element).append(button2);
			button2.css({'position':'absolute', 'top': '0px', 'right':'0px', 'width':'30px','height':hauteur, 'text-align':'center', 'z-index':5});
			button2.append('<i class="icon-chevron-left"></i>');
			var liste2 = $('<div class="liste_avenir">');
			$(element).append(liste2);
			for (var i=0; i<nb2; i++) {
				liste2.append($('<div>'+tab[liste_avenir[i]][4]+'</div>'));
			}
			liste2.css({'display':'none','position':'absolute','top':'0px','right':'0px','width':'auto','height':hauteur,'text-align':'left','z-index':5,
				'background-color':'LemonChiffon', 'padding':'5px', 'border-style':'solid', 'border-width': '1px', 'border-radius': '2px' });
		}
	},*/
});
