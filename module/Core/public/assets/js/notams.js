// objet ListNotam {{{1
var ListNotam = function()
{
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

// objet Notam {{{1
var Notam = function(raw)
{
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

    this.getFirstELine = function()
    {
        return this.getE().split('\n')[0];
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

    this.getData = function()
    {
        return this.getRaw().replace(/\n/g, '<br/>')
          .replace('<br/>', ''); // suppression du premier saut de ligne
    }
}

// création de la liste des Notam à partir d'une liste obtenue du site du SIA {{{1
function CreateNotamListFromSIA(siaNotams)
{
    var listNotam = new ListNotam();
    $.each(siaNotams, function(i) {
        listNotam.add($(this).text());
    });
    return listNotam;
}

// affiche les notam dans un $element {{{1
function showNotamInElement($element, $loadingDiv, code, urlTestAcces, urlGetNotam)
{
    $element.html('');
    $loadingDiv.show();

    $.get(urlTestAcces, function(data)
    {
        if(data.notamAccess == 1)
        {
            $.get(urlGetNotam, {code: code}, getAllNotam);
        }
        else
        {
            $element.html('<div class="alert alert-danger">Impossible de télécharger les NOTAM depuis l\'url fournie en paramètre. <br />Url utilisée : <b>' + data.notamUrl + '</b><br />Proxy utilisé : <b>' + data.notamProxy + '</b><br />Temps avant échec (timeout) : <b>' + data.notamTimeout + '<hr />Le paramètre <b>af_notam_max_loading_seconds</b> a modifier dans config/autoload/local.php permet d\'augmenter la durée avant l\'échec de la récupération. Augmenter la valeur du paramètre dans la configuration peut améliorer un comportement erratique de la récupération des NOTAM.</div>');
            $loadingDiv.hide();
        }
    });

    function getAllNotam(data)
    {
        var siaNotams = $(data.notams).find('font.NOTAMBulletin');
        if (siaNotams.length <= 0)
        {
            $element.html('<div class="alert alert-danger">L\'accès au NOTAM est possible mais aucun NOTAM n\'a été trouvé par l\'application pour ce code OACI. <br />Temps avant échec (timeout) : <b>' + data.notamTimeout + '</b><br />Plafond : <b>' + data.notamPlafond + '</b></br>Rayon : <b> ' + data.notamRayon + '</b><hr />Le paramètre <b>af_notam_max_loading_seconds</b> a modifier dans <b>config/autoload/local.php</b> permet d\'augmenter la durée avant l\'échec de la récupération (timeout). Augmenter la valeur du paramètre dans la configuration peut améliorer un comportement erratique de la récupération des NOTAM.<hr />Enfin le nombre de NOTAM récupérés est parfois trop important pour être traité par l\'application. Pour pallier à ce problème il faut régler les paramètres de plafond <b>af_plafond</b> et de rayon <b>af_rayon</b></div>.');
            $loadingDiv.hide();
        }

        listNotam = CreateNotamListFromSIA(siaNotams);
        $.each(listNotam.getAll(), function(i, not) {
            var div = $('<div></div>');
            $('<a data-toggle="collapse" data-parent="#show-not"></a>')
                .attr('href', '#not' + i)
                .html(not.getId()+" ")
                .appendTo(div);
            $('<strong></strong>')
                .html(not.getFirstELine())
                .appendTo(div);
            $('<p></p>')
                .addClass('collapse')
                .attr('id', 'not' + i)
                .html(not.getData())
                .appendTo(div);
            $('<hr>').appendTo(div);

            div.show().appendTo($element);
            $loadingDiv.hide();
        });
    }
}
