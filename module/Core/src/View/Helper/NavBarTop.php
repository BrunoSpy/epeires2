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

use Laminas\Form\View\Helper\AbstractHelper;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class NavBarTop extends AbstractHelper {
    
    
    private $sm;
    
    public function __invoke($color, $title, $return = null, $iponumber = null, $IHMLight = false, $viewduration = 6) {
        
        $auth = $this->sm->get('LmcRbacMvc\Service\AuthorizationService');
        $zfcuserauth = $this->sm->get('lmcuser_auth_service');

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
        $html .= '<a class="navbar-brand" href="'.($return !== null ? $this->view->url($return) : "#").'">';
        if($return !== null) {
            $html .= '<span class="glyphicon glyphicon-home"></span> ';
        }
        $html .= $this->view->translate('Epeires²').'</a>';
        $html .= '</div>';

        $html .= '<div class="collapse navbar-collapse" id="navbar-first-collapse">';
        $html .= '<ul class="nav navbar-nav navbar-left">';
        $html .= $this->view->userMenu($color);
        $html .= '</ul>';

        if(!$IHMLight) { // Epeires Light : no supervisor and no IPO

            if ($auth->getIdentity()) {
                $opsuptypes = array();
                foreach ($auth->getIdentity()->getRoles() as $role) {
                    foreach ($role->getOpsuptypes() as $type) {
                        if (!array_key_exists($type->getId(), $opsuptypes)) {
                            $opsuptypes[$type->getId()] = $type->getName();
                        }
                    }
                }
                foreach ($opsuptypes as $id => $name) {
                    $html .= $this->view->opsup($id);
                }
            }

            $html .= $this->view->ipo($iponumber);

        }


        $html .= '<p class="navbar-text navbar-right" id="navbar-clock"><span id="day"></span>&nbsp;&nbsp;<span id="clock"></span>&nbsp;</p>';

        if($IHMLight) {
           $html .= $this->view->viewselector($viewduration);
        }

        $html .= '</div></div></nav>';
        
        return $html;
        
    }
    
    public function setServiceManager($servicemanager)
    {
        $this->sm = $servicemanager;
    }
    
}