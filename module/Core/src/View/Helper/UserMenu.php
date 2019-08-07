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

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class UserMenu extends AbstractHelper implements ServiceManagerAwareInterface
{

    private $auth;

    private $servicemanager;

    public function __invoke($color)
    {
            
        $urlHelper = $this->getView()->plugin('url');
        
        $html = '<li class="dropdown">';
        $html .= '<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">';
        $html .= '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ';
        if ($this->auth->getIdentity() != null) {
            $html .= $this->auth->getIdentity()->getUserName();
        } else {
            $html .= "Non connecté";
        }
        $html .= '<b class="caret"></b>';
        $html .= '</a>';
        $html .= '<ul class="dropdown-menu dropdown-menu-material-'.$color.'-800">';
        if ($this->auth->getIdentity() != null) {
            $router = $this->servicemanager->get('router');
            $request = $this->servicemanager->get('request');
            
            if ($router->match($request) && $router->match($request)->getMatchedRouteName() == 'ipo') {
                $html .= "<li><a href=\"" . $urlHelper('application') . "\">Interface OPE</a></li>";
            } else {
                if ($this->auth->isGranted('ipo.read')) {
                    $html .= "<li><a href=\"" . $urlHelper('ipo') . "\">Interface ". $this->view->translate('IPO') ."</a></li>";
                }
            }
            if ($router->match($request) && $router->match($request)->getMatchedRouteName() == 'administration') {
                $html .= "<li><a href=\"" . $urlHelper('application') . "\">Interface OPE</a></li>";
            } else {
                if ($this->auth->isGranted('admin.access') || $this->auth->isGranted('admin.centre') || $this->auth->isGranted('admin.users') || $this->auth->isGranted('admin.categories') || $this->auth->isGranted('admin.models') || $this->auth->isGranted('admin.radio')) {
                    $html .= "<li><a href=\"" . $urlHelper('administration', array(
                        'controller' => 'home',
                        'action' => 'index'
                    )) . "\">Interface administration</a></li>";
                }
            }
            if($this->auth->isGranted('briefing.mod') && $router->match($request)->getMatchedRouteName() == 'application') {
                $html .= "<li><a href='#' id='usermenu-mod-briefing'>Modifier texte briefing</a></li>";
            }
            $html .= "<li><a href=\"" . $urlHelper('zfcuser/logout') . "?redirect=" . $urlHelper('application') . "\">Se déconnecter</a></li>";
        } else {
            $html .= "<li><a id=\"openloginwindow\" href=\"#loginwindow\" data-toggle=\"modal\" >Se connecter</a></li>";
        }
        $html .= '</ul>';
        $html .= '</li>';
        
        return $html;
    }

    public function setAuthService(\ZfcRbac\Service\AuthorizationService $auth)
    {
        $this->auth = $auth;
    }

    public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceLocator)
    {
        $this->servicemanager = $serviceLocator;
    }
}