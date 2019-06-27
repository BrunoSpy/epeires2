
function CreateNotamListFromSIA(siaNotams)
{
    var listNotam = new ListNotam();
    $.each(siaNotams, function(i) {
        listNotam.add($(this).text());
    });
    return listNotam;
}

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
}
