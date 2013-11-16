
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
var dy_max = 50;
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
var impt_on;
var open_list = new Array();
var x_act;
var tab = Array();
var corresp = Array();
var plus_info = 0;
var warn = new Array();
var temp_fin;
var cpt_journee;

var timeline = {

		set_impacts: function (url) {
			var i = 0;
			$.getJSON(url+"/getimpacts", function (data) {
				$.each(data, function(key, value) {
					impt_name[i] = value.name;
					impt_style[i] = value.short_name;
					impt_value[i] = value.color;
					i ++;
				});
			});
		},
		set_cat: function (url) {
			var i = 0;
			$.getJSON(url+"/getcategories", function (data) {
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
		compute_impact: function (name, value) {
			return dy_max*value/100;
		},
		conf: function (element, url) {
			timeline.init(element);
			var base_elmt = $('<div class="Base"></div>');
			$(element).append(base_elmt);
			timeline.base(base_elmt);
			var timeline_content = $('<div class="timeline_content"></div>');
			$(element).append(timeline_content);
			var timeline_other = $('<div class="timeline_other"></div>');
			timeline.set_cat(url);
			timeline.set_impacts(url);
			$(element).append(timeline_other);
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
//					tab[i] = [key, ddeb, dfin, value.punctual, value.name, timeline.compute_impact("",value.impact_value), value.category_root,"", value.status_name];
					var impt = 0;
					if (value.impact_value == 100) {impt = 1;}
					tab[i] = [key, ddeb, dfin, value.punctual, value.name, impt, value.category_root,"", value.status_name];
					corresp[key] = i;
					var j = 0;
					/*					tab[i][7] = new Array();
					$.each(value.actions, function(k, val){
						tab[i][7][j] = [k, val];
						j ++;
					});*/
					i ++;
				});
				timeline.create(timeline_content, tab);
				timeline.tri_cat(timeline_content, tab, 1);
				timeline.affiche_listes(timeline_other);
				timeline.timeBar(timeline_content);
			});
		},
		init: function(element) {
			d_actuelle = new Date();
			d_actuelle.setHours(d_actuelle.getUTCHours());
			h_act = d_actuelle.getHours();
			m_act = d_actuelle.getMinutes();
			d_ref_deb = new Date();
			d_ref_deb.setHours(d_ref_deb.getUTCHours()-1,0,0);
			h_aff = 6;
			y_temp = 10;
			h_ref = d_ref_deb.getHours(); 
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
		init_journee: function(element) {
			d_actuelle = new Date();
			d_actuelle.setHours(d_actuelle.getUTCHours());
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
		timeBar: function(element) {
			var detail1 = $('<div class="TimeBar"></div>');
			element.append(detail1);
			detail1.css({'position':'absolute', 'top': 0+'px', 'left': x_act+'px', 'width': 3, 'height':hauteur-50 ,'z-index' : 10, 
				'background-color':'red'});
		},
		maj_timeBar: function(element) {
			var detail1 = $(element).find('.TimeBar');
			detail1.css({'left': x_act+'px'});
		},
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
					if (x_act < x1 + 50) {
						wid = ((largeur-lar_unit)-x1)/2;
					} else {
						wid = Math.min(x_act-x1+lar_unit*4,largeur-lar_unit-x1);
					}
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
					if (x_act < x1 + 50) {
						wid = ((largeur-lar_unit)-x1)/2;
					} else {
						wid = x_act-x1+50;
					}
				} else if (d_fin < d_ref_deb) {
					// evmt non terminé
					wid = 50;	
				} else if (d_fin < d_ref_fin) {
					if (h_fin >= h_ref) { delta = h_fin - h_ref; } else { delta = 24 + h_fin - h_ref; }
					wid = lar_unit + delta*lar_unit*2 + m_fin*lar_unit*2/60 - x1;

				} else {
					wid = lar_unit + h_aff*lar_unit*2 - x1;	
				}
			}
			return [x1, wid];
		},
		affiche_listes: function (element) {
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
		},			
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
						if (fin >0 && fin < d_ref_deb && etat == "Terminé") { 
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
				categ.css({'position':'absolute', 'top':yy+'px', 'left':'-15px', 'width':30+'px', 'height':'auto', 'text-align':'center',
					'background-color':cat_coul[j],'border-style':'solid', 'border-width': '1px', 'border-color':'grey', 'border-radius': '0px', 'z-index':1});
				//	var separateur = $('<div class="elmt separateur"></div>');
				//	timeline_elmt.append(separateur);
				h_current = y_temp-7-yy;
				if (categ.height() > h_current) {
					y_temp = yy + categ.height() + 7;
				} else {
					categ.css({'height':h_current+'px'});
				}
				//	separateur.css({'position':'absolute', 'top':y_temp-delt_ligne/2+'px', 'left':-15+'px', 'width':largeur+30+'px', 'height':'1px', 'background-color':'grey','z-index':1});
				yy = y_temp-4;
			}
		},
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
							if (!(fin < d_ref_deb && etat == "Terminé") && debut <= d_ref_fin) {
								elmt = timeline_elmt.find('.ident'+id);
								id_list.push([id, tableau[i][4]]);
								elmt.css({'top':y_temp+'px'});
								y_temp = y_temp + elmt.height() + delt_ligne;
							}
						}
					}
					if (cat_regroup[j] == 1 && id_list != null) { 
						y_temp = timeline.regroupement(timeline_elmt,id_list, y1); 
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
				if (!(fin < d_ref_deb && etat == "Terminé") && debut <= d_ref_fin) {
					y_temp += delt_ligne;
					elmt = timeline_elmt.find('.ident'+id);
					if (speed) {
						elmt.css({'top':y_temp+'px'});
					} else {
						elmt.animate({'top':y_temp+'px'});
					}
					y_temp += elmt.height();
				}
			}
		},
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
		impt_on: function(timeline_elmt, tableau) {
			impt_on = 1;
			var len = tableau.length;
			var debut, fin, etat;
			var elmt;
			y_temp = 0;
			for (var i = 0; i<len; i++) {
				id = tableau[i][0];
				corresp[id] = i;
				debut = tableau[i][1];
				fin = tableau[i][2];
				etat = tableau[i][8];
				impt = tableau[i][5];
				if (!(fin < d_ref_deb && etat == "Terminé") && (debut <= d_ref_fin)) {
					elmt = timeline_elmt.find('.ident'+id);
					var elmt_rect = elmt.find('.rect_elmt');
					var elmt_compt = elmt.find('.complement');
					var elmt_b1 = elmt.find('.modify-evt');
					var elmt_b2 = elmt.find('.plus');
					elmt_text = elmt.find('.label_elmt');
					if (impt == 0) {
						elmt_rect.css({'opacity':'0.4'});
						elmt_rect.children().css({'opacity':'0.4'});
						elmt_compt.css({'opacity':'0.4'});
						elmt_b1.css({'opacity':'0.4'});
						elmt_b2.css({'opacity':'0.4'});
						elmt_text.css({'color':'DarkGray'});
					} else {
						elmt_rect.css({'opacity':'1'});
						elmt_rect.children().css({'opacity':'1'});
						elmt_compt.css({'opacity':'1'});
						elmt_b1.css({'opacity':'1'});
						elmt_b2.css({'opacity':'1'});
						elmt_text.css({'color':'black'});
					}
				}
			}
		},
		impt_off: function(timeline_elmt, tableau) {
			impt_on = 0;
			var len = tableau.length;
			var debut, fin, etat;
			var elmt;
			y_temp = 0;
			for (var i = 0; i<len; i++) {
				id = tableau[i][0];
				corresp[id] = i;
				debut = tableau[i][1];
				fin = tableau[i][2];
				etat = tableau[i][8];
				impt = tableau[i][5];
				if (!(fin < d_ref_deb && etat == "Terminé") && (debut <= d_ref_fin) && (impt == 0)) {
					elmt = timeline_elmt.find('.ident'+id);
					var elmt_rect = elmt.find('.rect_elmt');
					elmt_text = elmt.find('.label_elmt');
					elmt_rect.css({'opacity':'1'});
					elmt_rect.children().css({'opacity':'1'});
					elmt_text.css({'color':'black'});
				}
			}
		},
		creation_ligne: function (base_element, id, label, list, y0, dy, type, couleur){
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
			// ajout d'un point d'interrogation (ainsi qu'un bouton en survol)
			var elmt_qm_but = $('<button type="button" class="no_elmt_qm_but"></button>');
			elmt.append(elmt_qm_but);
			elmt_qm_but.addClass('btn btn-link btn-mini');
			$(elmt_qm_but).append('<i class="icon-question-sign"></i>');
			// ajout du bouton warning
			var elmt_obj = $('<button type="button" class="elmt_warn"></button>');
			$(elmt).append(elmt_obj);
			elmt_obj.addClass('btn btn-link btn-mini');
			$(elmt_obj).append('<i class="icon-warning-sign icon-red"></i>');
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
			var elmt_warn_det = $('<div class="elmt_warn_det"></div>');
			$(elmt).append(elmt_warn_det);
			var warn_content = $('<p class="warn_content"></p>');
			elmt_warn_det.append(warn_content);
			var warn_buttons = $('<div class="warn_buttons"><button type="submit" class="btn btn-success valid_status">Valider</button>'+
			'<button type="submit" class="btn btn-danger cancel_status">Annuler</button></div>');
			elmt_warn_det.append(warn_buttons);
			// +
			/*					'<table class="table">'+
					'<tbody>'+
					'<tr>'+
					'<td class="hour">'+
					'<a class="next" href="#"><i class="icon-chevron-up"></i></a><br>'+
					'<input type="text" class="input-mini"><br>'+
					'<a class="previous" href="#"><i class="icon-chevron-down"></i></a>'+
					'</td>'+
					'<td class="separator">:</td>'+
					'<td class="minute">'+
					'<a class="next" href="#"><i class="icon-chevron-up"></i></a><br>'+
					'<input type="text" class="input-mini"><br>'+
					'<a class="previous" href="#"><i class="icon-chevron-down"></i></a>'+
					'</td>'+
					'<td>'+
					'<button type="submit" class="btn">OK</button>'+
					'</td>'+
					'<td>'+
					'<button type="submit" class="btn">Annuler</button>'+		
					'</tr>'+
					'</tbody>'+
					'</table>'+
					'</div>');*/

//			var list_tag = $('<form>'); // class="nav nav-pills nav-stacked"
//			elmt_opt.append(list_tag);
//			var len = list.length;
//			for (var i = 0; i<len; i++) {
			//	list_tag.append('<label class="checkbox"><input type="checkbox" value='+list[i][0]+'>'+list[i][1]+'</label><li></li>');
//			}
//			elmt_opt.append('</form>');
			// ajout du bouton modifications
			elmt_status = $('<button type="button" class="elmt_status" data-id="'+id+'"data-name="'+label+'"></button>');
			elmt_status.addClass('btn btn-mini');
			$(elmt).append(elmt_status);
			elmt_b1 = $('<button type="button" class="modify-evt" data-id="'+id+'"data-name="'+label+'"></button>');
			$(elmt).append(elmt_b1);
			elmt_b1.addClass('btn btn-link btn-mini');
			$(elmt_b1).append('<i class="icon-pencil"></i>');
			// ajout du bouton développé
			elmt_b2 = $('<button type="button" class="plus"></button>');
			$(elmt).append(elmt_b2);
			elmt_b2.addClass('btn btn-link btn-mini show');
			$(elmt_b2).append('<i class="icon-plus"></i>');
			// ajout du bouton minimisé
			elmt_b2bis = $('<button type="button" class="moins"></button>');
			$(elmt).append(elmt_b2bis);
			elmt_b2bis.addClass('btn btn-link btn-mini');
			$(elmt_b2bis).append('<i class="icon-minus"></i>');
			elmt_star = $('<button type="button" class="elmt_star"></button>');
			$(elmt).append(elmt_star);
			elmt_star.addClass('btn btn-link btn-mini');
			// ajout du nom de l'événement
			elmt_txt = $('<p class="label_elmt">'+label+'</p>');
			$(elmt).append(elmt_txt);
			// lien entre le texte et l'événement (si texte écrit en dehors)
			var lien = $('<div class="no_lien"></div>');
			$(elmt).append(lien);
			// + h_deb + h_fin
			elmt_deb = $('<p class="elmt_deb"></p>');
			$(elmt).append(elmt_deb);
			elmt_fin = $('<p class="elmt_fin"></p>');
			$(elmt).append(elmt_fin);
			move_fin = $('<p class="move_fin"></p>');
			$(elmt).append(move_fin);
			// css permanent
			elmt.css({'position':'absolute', 'top': y0+'px', 'left':'0px', 'width': largeur, 'height':dy});
			var l_deb = type[0];  
			var l_fin = type[1];
			if (l_fin == 0 || l_fin == 1) {
				elmt_rect.css({'position':'absolute', 'top':'0px', 'left': '0px', 'width': '10px', 'height':dy ,'z-index' : 1, 
					'background-color':couleur,'border-style':'solid','border-color':'transparent',  'border-width': '1px', 'border-radius': '5px'});
			} else if (l_fin == -1) {
				elmt_rect.css({'position':'absolute', 'top':'0px', 'left': '0px', 'width': '10px', 'height':dy ,'z-index' : 1, 
					'background-color':couleur,'border-style':'solid', 'border-color':'transparent', 'border-width': '1px', 'border-radius': '5px'});
				// ajout d'une flèche droite et d'un point d'interrogation (ainsi qu'un bouton en survol)
				elmt_compl.css({'position':'absolute','left':'0px' , 'width':0, 'height':0, 'border-left':dy+'px solid '+couleur, 
					'border-top':dy/2+1+'px solid transparent', 'border-bottom': dy/2+1+'px solid transparent' });
				elmt_qm_but.css({'position':'absolute', 'top': dy/2-11+'px', 'left': '0px', 'z-index':2});
			} else if (l_fin == 2) {
				var haut = dy*2/3;
				var larg = haut*5/8;
				elmt_rect.css({'position':'absolute', 'left': -larg+'px', 'width':0, 'height':0, 'border-left':larg+'px solid transparent',
					'border-right':larg+'px solid transparent', 'border-bottom':haut+'px solid '+couleur,'z-index' : 1});
				elmt_compl.css({'position':'absolute', 'left': '0px','width':0, 'height':0, 'border-left':larg+'px solid transparent',
					'border-right':larg+'px solid transparent', 'border-top':haut+'px solid '+couleur, 'margin':haut*3/8+'px 0 0 -'+larg+'px','z-index' : 2});
			}
			elmt_fleche1.css({'position':'absolute', 'top': dy-22+'px', 'left': '0px'});
			elmt_fleche2.css({'position':'absolute', 'top': dy-22+'px', 'left': '0px'});
			elmt_write.css({'background-color':couleur});
			elmt_opt.css({'position':'absolute', 'top':dy+1+'px', 'left': '0px', 'width': 'auto', 'height':'auto' ,'z-index' : 1, 
				'background-color':couleur,'border-style':'solid','border-color':'transparent', 'padding':'0px' ,'border-width': '1px', 'display':'none'});
			elmt_b1.css({'position':'absolute', 'top': 0+'px', 'left': '0px', 'background-color':couleur, 'z-index' : 1});
			elmt_b2.css({'position':'absolute', 'top': 0+'px', 'left': '0px', 'background-color':couleur, 'z-index' : 1});
			elmt_b2bis.css({'position':'absolute', 'top': 0+'px', 'left': '0px', 'background-color':couleur, 'z-index' : 1});
			elmt_star.css({'position':'absolute', 'top': 0+'px', 'left': '0px', 'z-index' : 1});
			elmt_status.css({'position':'absolute', 'bottom': 0+'px', 'left': '0px', 'width':'56px', 'z-index' : 1});
			elmt_status.hide();
			elmt_obj.css({'position':'absolute', 'top': dy/2-11+'px', 'left': '0px', 'background-color':couleur, 'z-index' : 1});
			elmt_warn_det.hide();
			elmt_warn_det.css({'position':'absolute', 'top': dy+'px', 'background-color':'white', 'border-style':'solid', 'border-color':'gray','border-width': '1px',
				'border-radius': '5px', 'padding':'5px'});
			elmt_txt.css({'position':'absolute', 'top': dy/2-11+'px', 'left': '0px', 'font-weight':'normal', 'z-index' : 2});
			lien.css({'position':'absolute', 'top': dy/2+'px', 'left': '0px','width':'10px','height':'1px','background-color':'gray', 'z-index' : 1});
			elmt_deb.css({'position':'absolute', 'top': '0px','left': '0px', 'width': '40px', 'text-align' : 'center', 
				'font-style':'italic', 'background-color':'LemonChiffon', 'z_index':2});
			elmt_fin.css({'position':'absolute', 'top': '0px','left': '0px', 'width': '40px', 'text-align' : 'center', 
				'font-style':'italic', 'background-color':'LemonChiffon', 'z_index':2});
			move_fin.css({'position':'absolute', 'top': '0px', 'height':dy, 'z_index':2});
			move_fin.hover(function(){$(this).css({'cursor':'e-resize'});});
		},
		warn_animate:function(elmt_warn) {
			elmt_warn.toggle(200);
		},
		position_ligne: function (base_element, id, type, x0, wid, impt) {
			var l_deb = type[0];  
			var l_fin = type[1];
			var warn = type[2];
			var sts = type[3];
			var elmt = base_element.find('.ident'+id);
			var elmt_rect = elmt.find('.rect_elmt');
			var elmt_compl = elmt.find('.complement');
			var elmt_qm_but = elmt.find('.no_elmt_qm_but');
			var elmt_warn = elmt.find('.elmt_warn');
			var elmt_fleche1 = elmt.find('.elmt_fleche1');
			var elmt_fleche2 = elmt.find('.elmt_fleche2');
			var elmt_opt = elmt.find('.elmt_opt');
			var elmt_warn_det = elmt.find('.elmt_warn_det');
			var warn_content = elmt_warn_det.find('.warn_content');
			var warn_buttons = elmt_warn_det.find('.warn_buttons');
			var elmt_write = elmt_opt.find('.elmt_write');
			var elmt_status = elmt.find('.elmt_status');
			var elmt_b1 = elmt.find('.modify-evt');
			var elmt_b2 = elmt.find('.plus');
			var elmt_b2bis = elmt.find('.moins');
			var elmt_star = elmt.find('.elmt_star');
			var elmt_txt = elmt.find('.label_elmt');
			var elmt_deb = elmt.find('.elmt_deb');
			var elmt_fin = elmt.find('.elmt_fin');
			var move_fin = elmt.find('.move_fin');
			var lien = elmt.find('.no_lien');
			var x1 = x0;
			var b1_pos;
			var x2 = x1 + wid;
			var inter_boutons = 2;
			var ponctuel = 0;
			var txt_wid = elmt_txt.outerWidth();
			if (warn > 0) {
				elmt_warn.show();
				//	setInterval(timeline.warn_animate(elmt_warn),500);
			} else {
				elmt_txt.css({'color':'black'});
				elmt_warn.hide();
			}
			switch (l_deb) {
			case 0 :
				elmt_fleche1.hide();
				break;	
			case 1 :
				elmt_fleche1.show();
				elmt_fleche1.css({'left': x0-12+'px'});
				break;	
			}
			switch (l_fin) {
			case 0 :
				elmt_fleche2.hide();
				elmt_qm_but.hide();
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});
				elmt_compl.hide();
				elmt_star.css({'left': x0+wid-30+'px'});
				move_fin.css({'left': x0+wid+'px', 'width':'10px'});
				break;	
			case 1 :
				elmt_fleche2.show();
				elmt_qm_but.hide();
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});				
				elmt_compl.hide();
				elmt_fleche2.css({'left': x2+'px'});
				elmt_star.css({'left': x0+wid-30+'px'});
				move_fin.css({'left': x0+wid+'px', 'width':'10px'});
				break;
			case -1 :
				elmt_fleche2.hide();
				elmt_qm_but.show();
				elmt_qm_but.addClass('elmt_qm_but');
				elmt_rect.css({'left':x0+'px', 'width':wid+'px'});
				elmt_compl.css({'left':x0+wid+1+'px'});
				elmt_qm_but.css({'left': x2-8+'px'});
				elmt_star.css({'left': x0+wid-30+'px'});
				break;
			case 2 :
				elmt_fleche1.hide();
				elmt_fleche2.hide();
				elmt_qm_but.hide();
				elmt_deb.hide();
				var left_pos = elmt_rect.position().left;
				x0 += 2;
				x1 = x0 + left_pos;
				wid = 2*left_pos;
				x2 = x0 - left_pos;
				elmt_rect.css({'left': '+='+x0});
				elmt_compl.css({'left':x0+'px'});
				ponctuel = 1;
				elmt_star.css({'left': x0-15+'px'});
				move_fin.css({'left': x0-2+'px', 'width':'4px'});
				break;
			}
			lien.hide();
			if (ponctuel) {
				lien.addClass('lien');
				lien.show();
				x1 -= 30;
				if (warn > 0) { 
					elmt_warn.css({'left': x1+2+'px'});
					x1 -= 30; 
				}
				elmt_b2.css({'left': x1+2+'px'});
				elmt_b2bis.css({'left': x1+2+'px'});
				x1 -= 30;
				elmt_b1.css({'left': x1+2+'px'});
				b1_pos = x1+2;
				// on place l'heure à droite
				elmt_fin.css({'left': x2+5+'px'});
				elmt_txt.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
				txt_wid = elmt_txt.outerWidth();
				x2 += 50;
				if (x2+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
					elmt_txt.css({'left': x2+'px'});
					lien.css({'left': x2-50+'px','width':50+'px'});	
					x2 += txt_wid;
				} else { // sinon on le met à gauche
					x1 -= txt_wid+2;
					elmt_txt.css({'left': x1+'px'});
					lien.css({'left': x1+'px','width':x0-x1+'px'});
				}
			} else {
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
						}
						lien.addClass('lien');
						lien.show();
						elmt_txt.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
						txt_wid = elmt_txt.outerWidth();
						// on place l'heure de début à gauche
						x1 -= 50;
						elmt_deb.css({'left': x1+'px'});
						// on place l'heure de fin à droite
						elmt_fin.css({'left': x2+5+'px'});
						x2 += 50;
						if (x2+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
							elmt_txt.css({'left': x2+'px'});
							lien.css({'left': x2-50+'px','width':50+'px'});	
							x2 += 30;
						} else { // sinon on le met à gauche
							lien.css({'left': x1-2+'px','width':x0-x1+'px'});
							x1 -= txt_wid+2;
							elmt_txt.css({'left': x1+'px'});
						}
					}
				} 
			}
			if (impt == 1) {
				elmt_star.find('.icon-star-empty').remove();
				elmt_star.append('<i class="star_style icon-star icon-red"></i>');
			} else {
				elmt_star.find('.icon-star').remove();
				elmt_star.append('<i class="star_style icon-star-empty"></i>');
			}
			elmt_status.css({'left': b1_pos+'px'});
			timeline.set_status(base_element, id, type);
			elmt_warn_det.css({'left':x0, 'width':400,'height':70,'z-index':6});
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
		},
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
					var dDeb = d_debut.toLocaleDateString();
					hDeb = dDeb.substr(0,dDeb.length-5)+" "+hDeb; 
					var h1 = 0;
				} else {
					h1 = 4;
				}
			} else { hDeb = ""; }
			elmt_deb.text(hDeb);
			elmt_deb.css({'top':h1+'px'});
			// ajout de l'heure de fin
			if (d_fin > 0) {
				var fin_min = d_fin.getMinutes();
				if (d_fin.getMinutes() < 10) { fin_min = "0"+d_fin.getMinutes(); }
				var hFin = d_fin.getUTCHours()+":"+fin_min;
				if (d_fin.getDate() != d_actuelle.getDate()){
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
			elmt_fin.text(hFin);
			elmt_fin.css({'top':h2+'px'});
			var data_fin = elmt_fin[0];
			jQuery.data(data_fin,"d_fin",d_fin);
		},
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
		type_elmt: function (id, d_debut, d_fin, ponct, etat) {
			var l_deb = 0; 
			var l_fin = 0;
			var warn;
			var sts;
			// flèche gauche
			if (ponct == 0) {
				if (d_debut < d_ref_deb) { l_deb = 1;}
				// flèche droite
				if (d_fin > d_ref_fin) { l_fin = 1;}
				// pas de fin
				if (d_fin == -1) { l_fin = -1; }
				// ponctuel
			} else { 
				l_fin = 2;
			}
			var d_temp = new Date();
			d_temp.setTime(d_actuelle.getTime()+300000);
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
						} else {
							sts = 11; // Terminé et commencé
							warn = 0;
						} 
					}
				}
			}
			warn = 0;
			return [l_deb, l_fin, warn, sts];
		},
		create_elmt: function (base_element, id, d_debut, d_fin, ponct, label, impt, cat, list, etat) {
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
			timeline.creation_ligne(base_element, id, label, list, y_temp, dy, type, couleur);
			timeline.enrichir_contenu(base_element, id, d_debut, d_fin, label);
			timeline.position_ligne(base_element, id, type, x0, wid, impt);
			y_temp += dy + delt_ligne;
		},
		add_elmt: function (base_element, id, d_debut, d_fin, ponct, label, impt, cat, list, etat) {
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
			timeline.creation_ligne(base_element, id, label, list, 0, dy, type, couleur);
			timeline.enrichir_contenu(base_element, id, d_debut, d_fin, label);
			timeline.position_ligne(base_element, id, type, x0, wid, impt);
			if (tri_cat) { 
				timeline.tri_cat(base_element, tab,1);
			} else if (tri_hdeb) {
				timeline.tri_hdeb(base_element, tab,1);
			}
			elmt = base_element.find('.ident'+id);
			elmt.effect( "highlight",4000);
		},
		update_elmt: function (base_element, id, d_debut, d_fin, ponct, label, impt, cat, list, etat) {
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
			elmt.toggle(400);
			var y = elmt.position().top;
			elmt.remove();
			timeline.creation_ligne(base_element, id, label, list, y, dy, type, couleur);
			timeline.enrichir_contenu(base_element, id, d_debut, d_fin, label);
			timeline.position_ligne(base_element, id, type, x0, wid, impt);
		},
		modify: function (data, loc) {
			var i = 0;
			var d_debut, d_fin;
			var j;
			var id = -1;
			var len = tab.length;
			var d_now = new Date();
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
//				var impt = timeline.compute_impact("",value.impact_value);
				var impt = 0;
				if (value.impact_value == 100) {impt = 1;} 
				tab[id] = [key, d_debut, d_fin, ponct, label, impt, cat,"", etat];
				/*				var l = 0;
				tab[id][7] = new Array();
				$.each(value.actions, function(k, val){
					tab[id][7][l]= [k, val];
					l ++;
				});*/
				if (d_fin == -1 || (d_debut < d_now && d_fin > d_now) || 
						(d_debut.toLocaleDateString() == d_now.toLocaleDateString()) ||
						(d_fin.toLocaleDateString() == d_now.toLocaleDateString())) {
					if (cpt_journee.indexOf(i) == -1) {
						cpt_journee.push(i);
					}
				} else {
					if (cpt_journee.indexOf(i) > 0) {
						cpt_journee.splice(cpt_journee.indexOf(i),1);
					}
				}
				$('#cpt_evts').text(cpt_journee.length);
				var timel = $('#timeline');
				var base = timel.find('.Base');
				var timeline_content = timel.find('.timeline_content');
				var other = timel.find('.timeline_other');
				var elmt = timeline_content.find('.ident'+key);
				if (d_fin >0 && d_fin < d_ref_deb && etat == "Terminé") { 
					if (d_fin > d_min){ 
						liste_passee.push(id);
						timeline.affiche_listes(other);
					}
					elmt.remove();
				} else if (d_debut > d_ref_fin) {
					if (d_debut < d_max){
						liste_avenir.push(id);
						timeline.affiche_listes(other);
					}
					elmt.remove();
				} else {
					liste_affichee.push(id);
					timeline.update_elmt(timeline_content, key, d_debut, d_fin, ponct, label, impt, cat, tab[id][7], etat);
				}
				if (tri_cat) { 
					timeline.tri_cat(timeline_content, tab,1);
				} else if (tri_hdeb) {
					timeline.tri_hdeb(timeline_content, tab,1);
				}
				elmt = timeline_content.find('.ident'+key);
				if (loc) {
					elmt.effect( "highlight", 4000);
				} else {
					elmt.addClass("changed");
					elmt.css({'background-color':'yellow'});
				}
				i ++;
			});

		},
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
	//			var impt = timeline.compute_impact("",value.impact_value);
				var impt = 0;
				if (value.impact_value == 100) {impt = 1;} 
				tab[len] = [key, d_debut, d_fin, ponct, label, impt, cat,"", etat];
				corresp[key] = len;
				/*				var l = 0;
				tab[id][7] = new Array();
				$.each(value.actions, function(k, val){
					tab[len][7][l] = [k, val];
					l ++;
				});*/
				if (d_fin == -1 || (d_debut < d_now && d_fin > d_now) || 
						(d_debut.toLocaleDateString() == d_now.toLocaleDateString()) ||
						(d_fin.toLocaleDateString() == d_now.toLocaleDateString())) {
					cpt_journee.push(i);
				}
				$('#cpt_evts').text(cpt_journee.length);
				var timel = $('#timeline');
				var base = timel.find('.Base');
				var timeline_content = timel.find('.timeline_content');
				var other = timel.find('.timeline_other');
				if (d_fin >0 && d_fin < d_ref_deb && etat == "Terminé") { 
					if (d_fin > d_min) {
						liste_passee.push(len);
						timeline.affiche_listes(other);
					}
				} else if (d_debut > d_ref_fin) {
					if (d_debut < d_max) {
						liste_avenir.push(len);
						timeline.affiche_listes(other);
					}
				} else {
					liste_affichee.push(len);
					timeline.add_elmt(timeline_content, key, d_debut, d_fin, ponct, label, impt, cat, tab[len][7], etat);
				}
				i ++;
			});
		},
		update: function(timel) {
			var h_ref_old = h_ref;
			if (vue) {timeline.init_journee(timel);} else {timeline.init(timel);}
			var timeline_content = timel.find('.timeline_content');
			timeline.maj_timeBar(timeline_content);
			if (h_ref_old != h_ref) { 
				var base = timel.find('.Base');
				var other = timel.find('.timeline_other');
				$.holdReady(true);
				base.empty();
				timeline_content.empty();
				other.empty();
				timeline.base(base);	
				var res = timeline.create(timeline_content, tab);
				if (tri_cat) { 
					timeline.tri_cat(timeline_content, tab,1);
				} else if (tri_hdeb) {
					timeline.tri_hdeb(timeline_content, tab,1);
				}
				if (impt_on) {
					impt_on(timeline_content, tab);
				}
				timeline.affiche_listes(other);
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

	setInterval("timeline.update($('#timeline'))", 60000);

	$('#timeline').on('mouseenter','.elmt', function(){
		var elmt = $(this);
		if (elmt.hasClass('changed')) {
			elmt.css({'background-color':'transparent'});
			elmt.removeClass('changed');
		}
		elmt.css({'z-index':11});
		elmt.find('.modify-evt').show();
		elmt.find('.elmt_status').show();
		elmt.find('.elmt_star').show();
		elmt.find('.show').show();
		elmt.find('.elmt_deb').show();
		elmt.find('.elmt_fin').show();
		elmt.find('.elmt_qm_fleche').hide();
		elmt.find('.lien').hide();
	});
	$('#timeline').on('mouseleave','.elmt',function(){
		var elmt = $(this);
		elmt.css({'z-index':1});
		elmt.find('.modify-evt').hide();
		var elmt_status = elmt.find('.elmt_status');
		if (!(elmt_status.hasClass('btn-warning') || elmt_status.hasClass('btn-danger'))) { elmt_status.hide(); }
		elmt.find('.elmt_star').hide();
		elmt.find('.show').hide();
		elmt.find('.elmt_deb').hide();
		elmt.find('.elmt_fin').hide();
		elmt.find('.elmt_qm_fleche').show();
		elmt.find('.lien').show();
	});
	$('#timeline').on('mouseenter','.warn_elmt', function(){
		$(this).css({'z-index':3});
		$(this).find('.label_elmt').css({'font-weight':'bold'});
	});
	$('#timeline').on('mouseleave','.warn_elmt',function(){
		$(this).css({'z-index':1});
		$(this).find('.label_elmt').css({'font-weight':'normal'});
	});
	$('#timeline').on('click','.elmt_status', function(){
		var this_elmt = $(this).closest('.elmt');
		var elmt_warn_det = this_elmt.find('.elmt_warn_det');
		elmt_warn_det.show();

	});
	$('#timeline').on('click','.cancel_status', function(){
		var this_elmt = $(this).closest('.elmt');
		var elmt_warn_det = this_elmt.find('.elmt_warn_det');
		elmt_warn_det.hide();
	});
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

	$('#zoom').on('switch-change', function(e, data){
		//data.value = true => vue journée
		var timel = $('#timeline');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		var other = timel.find('.timeline_other');
		$.holdReady(true);
		base.empty();
		timeline_content.empty();
		other.empty();
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
		timeline.affiche_listes(other);
		timeline.timeBar(timeline_content);
		$.holdReady(false);
	});

	$('#tri_cat').on('click',function(){
		var timel = $('#timeline');
		$(this).parent().addClass('active');
		$('#tri_deb').parent().removeClass('active');
		var timeline_content = timel.find('.timeline_content');
		timeline.tri_cat(timeline_content, tab);
	});

	$('#tri_deb').on('click', function(){
		var timel = $('#timeline');
		$(this).parent().addClass('active');
		$('#tri_cat').parent().removeClass('active');
		var timeline_content = timel.find('.timeline_content');
		timeline.tri_hdeb(timeline_content, tab, 0);
	});

	$('#tri_impt').on('click', function(){
		$(this).parent().addClass('active');
		$('#tri_std').parent().removeClass('active');
		var timel = $('#timeline');
		var timeline_content = timel.find('.timeline_content');
		timeline.impt_on(timeline_content, tab);
	});
	$('#tri_std').on('click', function(){
		var timel = $('#timeline');
		$(this).parent().addClass('active');
		$('#tri_impt').parent().removeClass('active');
		var timeline_content = timel.find('.timeline_content');
		timeline.impt_off(timeline_content, tab);
	});
	$('#timeline').on('click','.passee', function(){
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
	$('#timeline').on('click','.avenir', function(){
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
	$('#timeline').on('mouseleave','.elmt_write', function(){
		$(this).val(); // valeur à sauvegarder

	});
	$('#timeline').on('click','.elmt_star', function(){
		var star_style = $(this).find('.star_style');
		var ss_elmt = $(this).closest('.elmt')[0];
		var id = jQuery.data(ss_elmt, "ident");
		var n = corresp[id];
		if (tab[n][5] == 1) {
			star_style.removeClass('icon-star');
			star_style.removeClass('icon-red');
			star_style.addClass('icon-star-empty');
			tab[n][5] = 0;
		} else if (tab[n][5] == 0) {
			star_style.removeClass('icon-star-empty');
			star_style.addClass('icon-red');
			star_style.addClass('icon-star');
			tab[n][5] = 1;
		}
		if (impt_on == 1) {
			var timel = $('#timeline');
			var timeline_content = timel.find('.timeline_content');
			timeline.impt_on(timeline_content, tab);			
		}
/*		if (tri_impt == 1) { 
			var timel = $('#timeline');
			var timeline_content = timel.find('.timeline_content');
			timeline.tri_impt(timeline_content, tab, 0);
		}*/
	});
	$('#timeline').on('mousedown','.move_fin', function(e1){
			var x_ref = e1.clientX;
			var x_temp = x_ref;
			var delt, delt2;
			var elmt = $(this).closest('.elmt');
			elmt.addClass('on_drag');
			var rect_elmt = elmt.find('.rect_elmt');
			var move_fin = $(this);
			var elmt_star = elmt.find('.elmt_star');
			var elmt_fin = elmt.find('.elmt_fin');
			var pix_time = 30*60000/lar_unit;
			var elmt_fin = elmt.find('.elmt_fin');
			var data_fin = elmt_fin[0];
			var d_fin = jQuery.data(data_fin,"d_fin");
			temp_fin = new Date();
			temp_fin.setTime(d_fin.getTime());
			$('#timeline').mousemove(function(e2) {
				delt = e2.clientX-x_temp;
				delt2 = e2.clientX-x_ref;
				temp_fin.setTime(d_fin.getTime()+delt2*pix_time);
				elmt_fin.text(temp_fin.toLocaleTimeString().substr(0,5));
				x_temp = e2.clientX;
				elmt.css({'width':'+='+delt});
				rect_elmt.css({'width':'+='+delt});
				elmt_star.css({'left':'+='+delt});
				elmt_fin.css({'left':'+='+delt});
				move_fin.css({'left':'+='+delt});
			});
	});
	$('#timeline').on('mouseup', function(){
		$('#timeline').unbind('mousemove');
		var timeline_content = $('#timeline').find('.timeline_content');
		var elmt = timeline_content.find('.on_drag');
		if (elmt[0] != null){
			var ss_elmt = elmt[0];
			elmt.removeClass('on_drag');
			var elmt_fin = elmt.find('.elmt_fin');
			var id = jQuery.data(ss_elmt, "ident");
			var n = corresp[id];
			tab[n][2] = temp_fin;
			timeline.update_elmt(timeline_content, id, tab[n][1], tab[n][2], tab[n][3], tab[n][4], tab[n][5], tab[n][6], tab[n][7], tab[n][8]);
		}
	});

	
	$(window).resize(function () {
		var timel = $('#timeline');
		var base = timel.find('.Base');
		var timeline_content = timel.find('.timeline_content');
		var other = timel.find('.timeline_other');
		$.holdReady(true);
		base.empty();
		timeline_content.empty();
		other.empty();
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
		timeline.affiche_listes(other);
		timeline.timeBar(timeline_content);
		$.holdReady(false);
	});
	
			
});
