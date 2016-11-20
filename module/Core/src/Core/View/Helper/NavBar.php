<?php
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
namespace Core\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;


/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class NavBar extends AbstractHelper
{

    private $sm;

    public function __invoke($color = 'epeires')
    {
        $html = "";
        
        $urlHelper = $this->view->plugin('url');
        $em = $this->sm->get('Doctrine\ORM\EntityManager');
        
        $html .= '<nav class="navbar navbar-default navbar-fixed-top navbar-lower navbar-material-'.$color.'-500 shadow-z-2">';
        
        $html .= '<div class="container-fluid">';
        
        $html .= '<div class="navbar-header">';
        $html .= '<button type="button" class="navbar-toggle collapsed"	data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">'
              .  '<span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>'
              .  '</button>';
        $html .= '</div>';
        
        $html .= '<div class="collapse navbar-collapse" id="navbar-collapse">';
        
        $html .= '<ul class="nav navbar-nav navbar-center">';
        $html .= '<li class="dropdown active">';
        $html .= '<a id="home" href="';
        $html .= $urlHelper('application', 
                            array(
                                'controller' => 'events',
                                'action' => 'index'
        ));
        $html .= '" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <span class="glyphicon glyphicon-home"></span> Évènements <span class="caret"></span></a>'
            . '<ul class="dropdown-menu dropdown-menu-material-purple-300">'
                . '<li class="dropdown-header">Tri</li>'
                . '<li class="tri active"><a href="#" id="tri_cat">Par catégorie</a></li>'
                . '<li class="tri"><a href="#" id="tri_deb">Par heure de début</a></li>';
        if($this->view->isGranted('events.delete')) {
            $html .= '<li role="separator" class="divider"></li>'
                . '<li class="dropdown-header">Filtre</li>'
                . '<li class="filter active"><a href="#" id="filter_deleted">Évènements supprimés non affichés</a></li>'
                . '<li class="filter"><a href="#" id="filter_none">Évènements supprimés affichés</a></li>';
        }
        $html .= '</ul>';
        $html .= '</li>' . '<li role="separator" class="divider"></li>';
        
        if ($this->view->isGranted('radars.read')) {
            $html .= '<li><a id="radartab" href="' . $urlHelper('application', array(
                'controller' => 'radars',
                'action' => 'index'
            )) . '">Radars</a></li>';
        }
        
        if ($this->view->isGranted('frequencies.read')) {
            $html .= '<li><a id="frequency" href="' 
                . $urlHelper('application',
                    array(
                        'controller' => 'frequencies',
                        'action' => 'index'
                    )) 
                . '">Radio</a></li>';
        }

        if ($this->view->isGranted('afis.read')) {
            $html .= '<li><a id="afis" href="'
                . $urlHelper('application',
                    [
                        'controller' => 'afis',
                        'action' => 'index'
                    ])
                . '">AFIS</a></li>';
        }

        if ($this->view->isGranted('flightplans.read')) {
            $html .= '<li><a id="flightplans" href="'
                . $urlHelper('application',
                    [
                        'controller' => 'flightplans',
                        'action' => 'index'
                    ])
                . '">Gestion PLN</a></li>';
        }

        if ($this->view->isGranted('flightplans.read')) {
            $html .= '<li><a id="sarflightplans" href="'
                . $urlHelper('application',
                    [
                        'controller' => 'flightplans',
                        'action' => 'sar'
                    ])
                . '">SAR PLN</a></li>';
        }

        if ($this->view->isGranted('sarbeacons.read')) {
            $html .= '<li><a id="sarbeacons" href="'
                . $urlHelper('application',
                    [
                        'controller' => 'sarbeacons',
                        'action' => 'index'
                    ])
                . '">SAR Balises</a></li>';
        }

        // Determine custom tabs to be displayed
        $tabs = $em->getRepository('Application\Entity\Tab')->findBy(array(), array(
            'place' => 'ASC'
        ));
        ;
        foreach ($tabs as $tab) {
            if ($this->view->hasRole($tab->getReadRoleNames())) {
                $html .= '<li><a class="customtab" id="tab-' . $tab->getId() . '" href="' . $urlHelper('application', array(
                    'controller' => 'tabs',
                    'action' => 'index'
                ), array(
                    'query' => array(
                        'tabid' => $tab->getId()
                    )
                )) . '">';
                $html .= $tab->getName() . '</a></li>';
            }
        }
        $html .= '</ul>';
        $html .= '<form class="navbar-form navbar-right" role="search" id="search">';
        $html .= '<div class="form-group form-group-material-'.$color.'-500 has-feedback">';
        $html .= '<input type="text" class="form-control" placeholder="Chercher" name="search">';
        $html .= '<span class="glyphicon glyphicon-search form-control-feedback"></span>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '<div id="changeview" class="navbar-right" style="margin-top: 5px">Vue : <div class="btn-group" data-toggle="buttons">';
        $html .= '<label class="btn btn-xs btn-info active">';
        $html .= '<input name="viewOptions" id="viewsix" type="radio" autocomplete="off" value="six" checked><strong>6 h</strong>';
        $html .= '</label>';
        $html .= '<label class="btn btn-xs btn-info ">';
        $html .= '<input name="viewOptions" id="viewday" type="radio" autocomplete="off" value="day"><strong>24 h</strong>';
        $html .= '</label>';
        $html .= '<label class="btn btn-xs btn-info ">';
        $html .= '<input name="viewOptions" id="viewmonth" type="radio" autocomplete="off" value="month"><strong>7 j/+</strong>';
        $html .= '</label>';
        $html .= '</div></div>';
        $html .= '</div></div></nav>';
        
        return $html;
    }

    public function setServiceManager($servicemanager)
    {
        $this->sm = $servicemanager;
    }
}
