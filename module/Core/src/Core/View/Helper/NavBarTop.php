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
class NavBarTop extends AbstractHelper {
    
    
    private $sm;
    
    public function __invoke($color, $title, $return = null, $iponumber = null, $zoneform = null) {
        
        $auth = $this->sm->get('ZfcRbac\Service\AuthorizationService');
        $zfcuserauth = $this->sm->get('zfcuser_auth_service');

        $html = '<nav class="navbar navbar-default navbar-fixed-top navbar-material-'.$color.'-800" id="navbar-first">';
        $html .= '<div class="container-fluid">';

        $html .= '<div class="navbar-header">';
        $html .= '<button type="button"
                          class="navbar-toggle collapsed"
                          data-toggle="collapse"
                          data-target="#navbar-first-collapse"
                          aria-expanded="false">
                      <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
                  </button>';
        $html .= '<a class="navbar-brand visible-lg-block" href="'.($return !== null ? $this->view->url($return) : "#").'">';
        if($return !== null) {
            $html .= '<span class="glyphicon glyphicon-home"></span> ';
        }
        $html .= 'Epeires<sup>2</sup>'.$title.'</a>';
        $html .= '</div>';

        $html .= '<div class="collapse navbar-collapse" id="navbar-first-collapse">';
        $html .= '<ul class="nav navbar-nav navbar-left">';
        $html .= $this->view->userMenu($color);
        $html .= '</ul>';

        if($auth->getIdentity() && !$zoneform) {
            $html .= '<p class="navbar-text navbar-left visible-lg-block">';
            $html .= '<span class="glyphicon glyphicon-road" aria-hidden="true"></span><b> Organisation : </b>' . $auth->getIdentity()->getOrganisation()->getName();
            $html .= '</p>';
        }
        
        if($zoneform) {
            $form = $zoneform;
            $form->setAttributes(array('action' => $this->view->url('application', array('controller'=>'events', 'action'=>'savezone')),
                'class'=>'navbar-form navbar-left'));
            $form->prepare();
            $html .= $this->view->form()->openTag($form);
            $html .= '<div class="form-group visible-xs-block visible-lg-block">';
            $html .= '<label for="zoneInput"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span><b> Zone : </b></label>';
            $html .= $this->view->formSelect($form->get('zone')->setAttributes(array('id' => 'zoneInput', 'class' => 'form-control')));
            $html .= '</div>';
            $html .= $this->view->form()->closeTag();
        }
        
        if ($auth->getIdentity() && $zoneform) {
            $opsuptypes = array();
            foreach($auth->getIdentity()->getRoles() as $role) {
                foreach ($role->getOpsuptypes() as $type) {
                    if( ! array_key_exists($type->getId(), $opsuptypes)) {
                        $opsuptypes[$type->getId()] = $type->getName();
                    }
                }
            }
            foreach($opsuptypes as $id => $name) {
                $html .= $this->view->opsup($id);
            }
        }
        
        $html .= $this->view->ipo($iponumber);
        $html .= '<p class="navbar-text navbar-right visible-xs-block visible-lg-block" id="navbar-clock"><span id="day"></span>&nbsp;&nbsp;<span id="clock"></span>&nbsp;</p>';
                       
        $html .= '</div></div></nav>';
        
        return $html;
        
    }
    
    public function setServiceManager($servicemanager)
    {
        $this->sm = $servicemanager;
    }
    
}