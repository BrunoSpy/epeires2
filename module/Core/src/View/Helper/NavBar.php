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

use Application\Entity\Tab;
use Laminas\Form\View\Helper\AbstractHelper;


/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class NavBar extends AbstractHelper
{

    private $sm;

    public function __invoke($color = 'epeires', $showHome = true, $IHMLight = false, $viewduration = 6)
    {
        $html = "";
        
        $urlHelper = $this->view->plugin('url');
        $em = $this->sm->get('Doctrine\ORM\EntityManager');
        
        $html .= '<nav id="navbar-tabs" class="navbar navbar-default navbar-fixed-top navbar-lower navbar-material-'.$color.'-500 shadow-z-2">';
        
        $html .= '<div class="container-fluid">';
        
        $html .= '<div class="navbar-header">';
        $html .= '<button type="button" class="navbar-toggle collapsed"	data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">'
              .  '<span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>'
              .  '</button>';
        $html .= '</div>';
        
        $html .= '<div class="collapse navbar-collapse" id="navbar-collapse">';

        $html .= '<ul class="nav navbar-nav">';
        if($showHome == true) {
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
            if ($this->view->isGranted('events.delete')) {
                $html .= '<li role="separator" class="divider"></li>'
                    . '<li class="dropdown-header">Filtre</li>'
                    . '<li class="filter active"><a href="#" id="filter_deleted">Évènements supprimés non affichés</a></li>'
                    . '<li class="filter"><a href="#" id="filter_none">Évènements supprimés affichés</a></li>';
            }
            $html .= '</ul>';
            $html .= '</li>';
        }
        if ($this->view->isGranted('frequencies.read')) {
            $html .= '<li class="entrytab"><a id="frequency" href="'
                . $urlHelper('application',
                    array(
                        'controller' => 'frequencies',
                        'action' => 'index'
                    )) 
                . '"><i class="fas fa-broadcast-tower entrytab-icon"></i> <span class="entrytab-text">Radio</span></a></li>';
        }

        if ($this->view->isGranted('afis.read')) {
            $html .= '<li class="entrytab"><a id="tab-afis" href="'
                . $urlHelper('application',
                    [
                        'controller' => 'afis',
                        'action' => 'index'
                    ])
                . '">AFIS</a></li>';
        }

        if ($this->view->isGranted('flightplans.read')) {
            $html .= '<li class="entrytab"><a id="tab-flightplans" href="'
                . $urlHelper('application',
                    [
                        'controller' => 'flightplans',
                        'action' => 'index'
                    ])
                . '">Gestion PLN</a></li>';
        }

        if ($this->view->isGranted('sarbeacons.read')) {
            $html .= '<li class="entrytab"><a id="tab-sarbeacons" href="'
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
        
        foreach ($tabs as $tab) {
            if (!$tab->isDefault() && $this->view->hasRole($tab->getReadRoleNames())) {

                switch ($tab->getType()) {
                    case Tab::TIMELINE:
                        $html .= '<li class="dropdown entrytab">' .
                            '<a class="customtab dropdown-toggle" id="tab-' . $tab->getId() . '" ' .
                            'href="' . $urlHelper('application', array(
                                'controller' => 'timelinetab',
                                'action' => 'index'
                            ), array(
                                'query' => array(
                                    'tabid' => $tab->getId()
                                )));
                        $html .= '" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="'.$tab->getName().'">';
                        if(strlen($tab->getIcon()) > 0) {
                            $html .= '<i class="entrytab-icon '.$tab->getIcon().'"></i> ';
                        }
                        $html .= '<span class="entrytab-text">'.$tab->getName() . '</span> <span class="caret"></span></a>'
                            . '<ul class="dropdown-menu dropdown-menu-material-purple-300">'
                            . '<li class="dropdown-header">Tri</li>'
                            . '<li class="tri active"><a href="#" id="tri_cat">Par catégorie</a></li>'
                            . '<li class="tri"><a href="#" id="tri_deb">Par heure de début</a></li>';
                        if ($this->view->isGranted('events.delete')) {
                            $html .= '<li role="separator" class="divider"></li>'
                                . '<li class="dropdown-header">Filtre</li>'
                                . '<li class="filter active"><a href="#" id="filter_deleted">Évènements supprimés non affichés</a></li>'
                                . '<li class="filter"><a href="#" id="filter_none">Évènements supprimés affichés</a></li>';
                        }
                        $html .= '</ul></li>';
                        break;
                    case Tab::SWITCHLIST:
                        $html .= '<li class="entrytab">' .
                            '<a class="customtab" id="tab-'.$tab->getId().'"' .
                            'href ="' . $urlHelper('application', array(
                                'controller' => 'switchlisttab',
                                'action' => 'index'
                            ), array('query' => array('tabid' => $tab->getId())));
                        $html .= '">';
                        if(strlen($tab->getIcon()) > 0) {
                            $html .= '<i class="entrytab-icon '.$tab->getIcon().'"></i> ';
                        }
                        $html .= '<span class="entrytab-text">'.$tab->getName()."</span></a></li>";
                        break;
                    case Tab::SPLITTIMELINE:
                        $html .= '<li class="entrytab">' .
                            '<a class="customtab" id="tab-'.$tab->getId().'"' .
                            'href ="' . $urlHelper('application', array(
                                'controller' => 'splittimelinetab',
                                'action' => 'index'
                            ), array('query' => array('tabid' => $tab->getId())));
                        $html .= '">';
                        if(strlen($tab->getIcon()) > 0) {
                            $html .= '<i class="entrytab-icon '.$tab->getIcon().'"></i> ';
                        }
                        $html .= '<span class="entrytab-text">'.$tab->getName()."</span></a></li>";
                        break;
                }

            }
        }
        $html .= '</ul>';

        $html .= $this->view->viewselector($viewduration);

        $html .= '</div>';
        $html .= '</div></div></nav>';
        
        return $html;
    }

    public function setServiceManager($servicemanager)
    {
        $this->sm = $servicemanager;
    }
}
