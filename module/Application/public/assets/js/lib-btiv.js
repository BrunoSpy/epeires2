(function($) {
    function Tab (c1, c2, label, champ) {
        this.c1 = c1;
        this.c2 = c2;
        this.label = label;
        this.champ = champ;

        this.html = 
        '<div class="row">' + 
            '<div class="col-sm-'+this.c1+' form-group">' +
                '<label> ' + this.label + '</label>' +
            '</div>' +
            '<div class="col-sm-'+this.c2+' form-group">' + this.champ + '</div>' +
        '</div>';
    }

    $.fn.addModal = function (titre, corps, pied) {
        this.addClass('modal fade');
        return this.append(creerModal(titre, corps, pied));

        function creerModal (titre, corps, pied) {   
            var $html = 
                    $('<div class="modal-dialog">' +
                        '<div class="modal-content">' +
                            '<div class="modal-header">' +
                                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span aria-hidden="true">&times;</span></button>' +
                            '<h3></h3>' +
                        '</div>' +
                        '<div class="well">' +
                            '<div class="modal-body"></div>' +
                            '<div class="modal-footer"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>');

            $html.find('h3').html(titre);
            $html.find('.modal-body').html(corps);
            $html.find('.modal-footer').html(pied);
            return $html;
        }
    }

    $.fn.fhtml = function (tabProp, tabVal) {
        var $f = this;

        $.each(tabProp, function(i, champ) {
            $f.append(affChamp(champ));
        });

        function affChamp (champ) {
            switch (champ.type) {
                case 'text':
                case 'hidden':
                    return affInput(champ);
                    break;

                case 'datetime':
                    var $t = affInput(champ);
                    $t.find('input').timepickerform({ init: champ.init });
                    $t.find('.day input').bootstrapMaterialDatePicker();
                    return $t;
                    break;

                case 'select':
                    return affSelect(champ);
                    break;

                default:
                    return '';
                    break;
            }
        }

        function affInput (champ) {
            var keys = ['type', 'value', 'name', 'required'];
            var $inp = $('<input class="form-control" />');

            $.each(champ, function(key, val) {
                if ($.inArray(key, keys) > -1) $inp.prop(key, val);
            });

            var t = new Tab(6, 6, champ.label, $inp.prop('outerHTML'));
            var $t = $(t.html);

            if (champ.type == "hidden") $t.hide();

            return $t;
        }

        function affSelect (champ) {

            var $slt = $('<select name = "'+champ.name+'" class="selectpicker"></select>');

            $.each(champ.options, function(key, val) {
                $slt.append($('<option value = "' + key + '">' + val + '</option>'));
            });

            var t = new Tab(6, 6, champ.label, $slt.prop('outerHTML'));
            var $t = $(t.html);

            return $t;
        }
    }

    // $.fn.finput = function (label, options) {
    //     this.append(creerFormInput(label, options));

    //     function creerFormInput(label, options) {
    //         var keys = ['type', 'value', 'name'];
    //         var $inp = $('<input class="form-control" />');

    //         $.each(options, function(key, val) {
    //             if ($.inArray(key, keys) > -1) $inp.prop(key, val);
    //         });
    //         $t = new Tab(6, 6, label, $inp.prop('outerHTML'));
    //         return $t.html;
    //     }
    // }

    // $.fn.fselect = function (label, options) {
    //     this.append(creerFormSelect(label, options));

    //     function creerFormSelect(label, options) {
    //         var $slt = $('<select class="selectpicker"></select>');

    //         $.each(options, function(key, val) {
    //             $slt.append($('<option value = "' + key + '">' + val + '</option>'));
    //         });
    //         $t = new Tab(6, 6, label, $slt.prop('outerHTML'));
    //         return $t.html;
    //     }
    // }

    // $.fn.plnInter = function (latLon, terrains) {
        
    //     return creerListeTerrains(latLon, terrains);

    //     function creerListeTerrains(latLon, terrains) {
    //         return distanceTerrains(latLon, terrains);
    //     }
    //     /* Calcul la distance d'une liste de terrains par rapport à un point et les classent */
    //     function distanceTerrains(latLon, terrains) {
    //         var tabDist = [];
    //         $.each(terrains, function(i, val) {
    //             var tmp = val;
    //             var coord = val.geometry.coordinates;
    //             var dRad = distRad(latLon[0], latLon[1], coord[1], coord[0]);
    //             tmp.d = radTokm(dRad);
    //             tmp.cap = cap(dRad, latLon[0], latLon[1], coord[1], coord[0]) * 180 / Math.PI;
    //             if (tmp.d && tmp.cap) tabDist.push(tmp);
    //         });

    //         tabDist.sort(function(a, b) {
    //             return a.d - b.d;
    //         });

    //         return tabDist;
    //     }

    //   function distRad(lat1, lon1, lat2, lon2) {
    //         // var dLat = (lat2 - lat1) * Math.PI / 180;  // deg2rad below
    //         // var dLon = (lon2 - lon1) * Math.PI / 180;
    //         // var a = 
    //         //  0.5 - Math.cos(dLat)/2 + 
    //         //  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    //         //  (1 - Math.cos(dLon))/2;

    //         lat1 = (Math.PI / 180) * lat1;
    //         lat2 = (Math.PI / 180) * lat2;
    //         lon1 = (Math.PI / 180) * lon1;
    //         lon2 = (Math.PI / 180) * lon2;
    //         return Math.acos(Math.sin(lat1) * Math.sin(lat2) + Math.cos(lat1) * Math.cos(lat2) * Math.cos(lon1 - lon2));
    //     }

    //     function cap(drad, lat1, lon1, lat2, lon2) {
    //         lat1 = (Math.PI / 180) * lat1;
    //         lat2 = (Math.PI / 180) * lat2;
    //         lon1 = (Math.PI / 180) * lon1;
    //         lon2 = (Math.PI / 180) * lon2;

    //         if (Math.sin(lon2 - lon1) < 0)
    //             return Math.acos((Math.sin(lat2) - Math.sin(lat1) * Math.cos(drad)) / (Math.sin(drad) * Math.cos(lat1)));
    //         else
    //             return 2 * Math.PI - Math.acos((Math.sin(lat2) - Math.sin(lat1) * Math.cos(drad)) / (Math.sin(drad) * Math.cos(lat1)));

    //     }

    //     function radTokm(rad) {
    //         return R_TERRE_KM * rad;
    //     }
    // }

}(jQuery));