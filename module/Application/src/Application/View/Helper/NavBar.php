<?php
/*
 * This file is part of Epeires². Epeires² is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version. Epeires² is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details. You should have received a copy of the GNU Affero General Public License along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\Form;
use Zend\Form\Element\Select;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class NavBar extends AbstractHelper
{

    private $sm;

    public function __invoke($displayCreate = false)
    {
        $html = "";
        
        $auth = $this->sm->get('ZfcRbac\Service\AuthorizationService');
        $zfcuserauth = $this->sm->get('zfcuser_auth_service');
        $urlHelper = $this->view->plugin('url');
        $em = $this->sm->get('Doctrine\ORM\EntityManager');
        
        $html .= '<nav class="navbar navbar-default navbar-fixed-top navbar-lower navbar-material-purple-500">';
        
        $html .= '<div class="container-fluid">';
        $html .= '<div class="navbar-header">';
        $html .= '<button type="button" class="navbar-toggle collapsed"' . '	data-toggle="collapse" data-target="#navbar-collapse"' . '	aria-expanded="false">' . ' <span class="sr-only">Toggle navigation</span> <span' . ' class="icon-bar"></span> <span class="icon-bar"></span> <span' . ' class="icon-bar"></span>' . '</button>' . '</div>' . '<div class="collapse navbar-collapse" id="navbar-collapse">';
        
        if ($displayCreate && $this->auth->isGranted('events.create')) {
            echo '<a id="create-link" href="#" type="button" class="navbar-left btn-material-purple-300 btn btn-default btn-info mdi-action-raised btn-fab navbar-btn"><i class="mdi-image-edit"></i></a>';
        }
        
        $html .= '<ul class="nav navbar-nav navbar-center">';
        $html .= '<li class="dropdown active">';
        $html .= '<a id="home" href="';
        $html .= $urlHelper('application', array(
            'controller' => 'events',
            'action' => 'index'
        ));
        $html .= '" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-home"></span> Évènements <span class="caret"></span></a>' . '<ul class="dropdown-menu dropdown-menu-material-purple-300">' . '<li class="dropdown-header">Tri</li>' . '<li class="active"><a href="#" id="tri_cat">Par catégorie</a></li>' . '<li><a href="#" id="tri_deb">Par heure de début</a></li>' . '</ul>' . '</li>' . '<li role="separator" class="divider"></li>';
        
        if ($this->isGranted('radars.read')) {
            $html .= '<li><a id="radartab" href="' . $urlHelper('application', array(
                'controller' => 'radars',
                'action' => 'index'
            )) . '">Radars</a></li>';
        }
        
        if ($this->isGranted('frequencies.read')) {
            $html .= '<li><a id="frequency" href="' . $urlHelper('application', array(
                'controller' => 'frequencies',
                'action' => 'index'
            )) . '">Radio</a></li>';
        }
        
        // Determine custom tabs to be displayed
        $tabs = $em->getRepository('Application\Entity\Tab')->findBy(array(), array(
            'place' => 'ASC'
        ));
        ;
        foreach ($tabs as $tab) {
            if ($this->hasRole($tab->getReadRoleNames())) {
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
        $html .= '<div class="form-group form-group-material-purple-500 has-feedback">';
        $html .= '<input type="text" class="form-control" placeholder="Chercher" name="search">';
        $html .= '<span class="glyphicon glyphicon-search form-control-feedback"></span>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '<form class="navbar-form navbar-right">';
        $html .= '<div class="togglebutton togglebutton-material-purple-200">';
        $html .= '<label> Vue journée : <input id="zoom" type="checkbox" name="zoom-switch">';
        $html .= '</label></div></form></div></div></nav>';
        
        return $html;
    }

    public function setServiceManager($servicemanager)
    {
        $this->sm = $servicemanager;
    }
}