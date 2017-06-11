/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author Loïc Perrin
 */

var afis = function(url) {

    var ListNotam = function() {
        this.list = [];

        this.add = function(raw) {
            this.list.push(new Notam(raw));
        }

        this.get = function(i) {
            return this.list[i];
        }

        this.getAll = function() {
            return this.list;
        }

        this.findByAero = function(aero) {
            var notams = [];
            $.each(this.list, function(i, notam) {
                if(notam.getAero() === aero && notam.isOpenHours()) {
                    notams.push(notam);
                }
            });
            return notams;
        }

        this.findOpenHours = function() {
            var notams = [];
            $.each(this.list, function(i, notam) {
                if(notam.isOpenHours()) {
                    notams.push(notam);
                }
            });
            return notams;
        }
    }

    var Notam = function(raw) {
        this.raw = raw;
        this.lignes = this.raw.split('\n');

        this.getId = function() {
            return this.lignes[1];
        }

        this.getA = function() {
            return this.lignes[3];
        }

        this.getE = function() {
            var str = this.lignes[5].substr(3) + '\n';
            for (i=6;i<this.lignes.length;i++) {
                str+= this.lignes[i] + '\n';
            }
            return str;
        }

        this.getContacts = function() {
            var E = this.getE();
            var itel = E.indexOf('TEL ');
            var ifax = E.indexOf('FAX ');
            if (itel == -1 && ifax == -1) return false;  
            var str = '';
            str += E.substr(itel, 25);
            str += E.substr(ifax, 25);
            return str;
        }

        this.getCode = function() {
            var A = this.getA();
            return A.substr(3, 4);
        }

        this.getName = function() {
            var A = this.getA();
            return A.substr(9); 
        }

        this.isOpenHours = function() {
            return (this.getE().indexOf('HORAIRE') == -1) ? false : true;  
        }

        this.getRaw = function() {
            return this.raw;
        }
    }


    var $tAdmbodies = $(".t-adm tbody"),
        $tUsrbody = $(".t-usr tbody"),
        $fEditAf = $("#f-edit-af")
        ;
    // $("#list-afis");

    $fEditAf.on('submit', submitHandler);
    refresh();

    function refresh() {
        $('.btn-switch-af .a-edit-af .a-del-af').remove();
        if ($tUsrbody.length > 0)
            $tUsrbody
                .load(url + 'afis/get', { decomissionned: 0, admin: 0 }, setUsrBtn);

        if ($tAdmbodies.length > 0) {
            $tAdmbodies.first()
                .load(url + 'afis/get', { decomissionned: 0, admin: 1 });

            $tAdmbodies.eq(1)
                .load(url + 'afis/get', { decomissionned: 1, admin: 1 }, setAdmBtn);
        }

        function setUsrBtn() {
            $('.btn-switch-af').change(function() {
                var boolState = 0;
                if ($(this).is(':checked')) {
                    boolState = 1;
                }

                $.post(
                    url + 'afis/switchafis', { id: $(this).data('id'), state: boolState },
                    function(data) {
                        noty({
                            text: data.msg,
                            type: data.type,
                            timeout: 4000,
                        });
                    },
                    'json'
                );
            });

            setNotamBtn();

            $tUsrbody.find('span.glyphicon').tooltip();
            $.material.togglebutton();
        }

        function setAdmBtn() {
            $('.a-edit-af').unbind('click').click(function() {
                $("#title-edit-af").html("Modifier un AFIS");
                loadAfisForm($(this).data('id'));
            });

            $('.a-del-af').unbind('click').click(function() {
                var id = $(this).data('id');
                $('#s-del-af-name').html($(this).data('name'));
                $('#a-del-af-ok').unbind('click').click(function() {
                    $("#mdl-del-af").modal('hide');
                    $.post(
                        url + 'afis/delete', 
                        { id: id }, 
                        function(data) {
                            refresh();
                            noty({
                                text: data.msg,
                                type: data.type,
                                timeout: 4000,
                            });
                        }, 
                        'json'
                    );
                });
            });
            $tAdmbodies.find('span.glyphicon').tooltip();

            setNotamBtn();
        }

        function setNotamBtn() { 
            $('.a-show-not').click(function() {
                var tpl = $('#show-not').find('div').first().hide();
                $('#show-not').find('div').slice(1).remove();
                var code = $(this).data('code');
                $("#title-show-not").html("Tous les NOTAM pour " + code);
                $.get(url + 'afis/getnotams', {code: code}, function(data) {
                    var $n = $(data).find('font.NOTAMBulletin');
                    if ($n.length > 0) {
                        notams = new ListNotam();
                        $.each($n, function(i) {
                            notams.add($(this).text());
                        });
                        $.each(notams.getAll(), function(i, not) {
                            var div = tpl.clone();
                            div.find('a')
                                .attr('href', '#not' + i)
                                .html(not.getId());
                            div.find('.collapse')
                                .attr('id', 'not' + i)
                                .html(not.getRaw());
                            div.show()
                                .appendTo($('#show-not'));    
                        });
                    } else {
                        noty({
                            text: 'Pas d\'informations disponibles pour ce code OACI. <br />Il faut une connexion internet pour récupérer les informations des NOTAM.',
                            type: 'error',
                            timeout: 4000,
                        });  
                    }
                });
            });
        }
    }

    $("#btn-add-af").click(function() {
        $("#title-edit-af").html("Nouvel AFIS");
        loadAfisForm();
    });


    function loadAfisForm(id = null) {
        $fEditAf.load(url + 'afis/form', { id: id }, function() {
            if(id) $fEditAf.find('input[name=code]').prop('disabled', true);

            $.material.checkbox();
            $fEditAf.find('input[name=code]').keyup(function(e){
                if ($(this).val().length == 4 && keyIsALetter(e.which)) {
                    var code = $(this).val();
                    noty({
                        text: 'Recherche des informations (horaires et contacts) associées au code donné.',
                        type: 'info',
                        timeout: 4000,
                    });
                    $.get(url + 'afis/getnotams', {code: code}, function(data) {
                        var $n = $(data).find('font.NOTAMBulletin');
                        if ($n.length > 0) {
                            $fEditAf.find('input[name=name]').val('');
                            $fEditAf.find('textarea[name=openedhours]').val('');
                            $fEditAf.find('textarea[name=contacts]').val('');

                            notams = new ListNotam();
                            $.each($n, function(i) {
                                notams.add($(this).text());
                            });
                            $fEditAf.find('input[name=name]').val(notams.get(0).getName());
                            $.each(notams.findOpenHours(), function() {
                                $fEditAf.find('textarea[name=openedhours]').val(this.getE());
                            });
                            $.each(notams.getAll(), function(i, notam) {
                                var contacts = notam.getContacts();
                                if(contacts)
                                    $fEditAf.find('textarea[name=contacts]').val(contacts);
                            });
                        } else {
                            noty({
                                text: 'Pas d\'informations disponibles pour ce code OACI. <br />Il faut une connexion internet pour récupérer les informations des NOTAM.',
                                type: 'error',
                                timeout: 4000,
                            });  
                        }
                    });
                }
            });
        });
    };

    function submitHandler(e) {
        e.preventDefault();
        $("#mdl-edit-af").modal('hide');
        $fEditAf.find('input[name=code]').prop('disabled', false);
        $.post(
            url + 'afis/save',
            $('#Afis').serialize(),
            function(data) {
                refresh();
                noty({
                    text: data.msg,
                    type: data.type,
                    timeout: 4000,
                });
            },
            'json'
        );
    }

    function keyIsALetter(key) {
        if(key >= 65 && key <= 90) return true;
    }
};
