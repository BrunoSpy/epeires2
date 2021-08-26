/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */

var maintenance = function(url) {

    $("#import-regulations").on('click', function(){
        let org = $('#maintenanceOrganisation option:selected').val();
        $.getJSON(url + '/importregulations?org='+org, function(data){
            displayMessages(data);
        });
    });

    $("#import-mapd").on('click', function(){
        let org = $('#maintenanceMAPDOrganisation option:selected').val();
        $.getJSON(url + '/importzonesmapd?org='+org, function(data){
            displayMessages(data);
        });
    });

    $("#import-zonesnmb2b").on('click', function(){
        let org = $('#maintenanceNMB2BOrganisation option:selected').val();
        $.getJSON(url + '/importzonesnmb2b?org='+org, function(data){
            displayMessages(data);
        });
    });

    $("#generate-rpo").on('click', function(){
        let org = $('#maintenanceCRRPOOrganisation option:selected').val();
        $.getJSON(url + '/sendrpo?org='+org, function(data){
            displayMessages(data);
        });
    });

    $("#generate-rpo-delta").on('click', function(){
        let org = $('#maintenanceCRRPODeltaOrganisation option:selected').val();
        $.getJSON(url + '/sendrpodelta?org='+org, function(data){
            displayMessages(data);
        });
    });
}