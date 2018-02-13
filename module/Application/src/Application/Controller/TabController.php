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
namespace Application\Controller;

use MattermostMessenger\Service\MattermostService;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Sets all variables needed to a controller
 * 
 * @author Bruno Spyckerelle
 */
class TabController extends ZoneController
{

    protected $viewmodel;

    protected $config;
    protected $mattermost;

    protected $messages;

    public function __construct($config, MattermostService $mattermost)
    {
        $this->config = $config;
        $this->mattermost = $mattermost;
    }

    public function indexAction()
    {
        parent::indexAction();

        $this->messages = array();

        $this->viewmodel = new ViewModel();
        
        $this->layout()->iponumber = "";
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $iponumber = $this->zfcUserAuthentication()
                ->getIdentity()
                ->getOrganisation()
                ->getIpoNumber();
            if ($iponumber != null && strlen($iponumber) > 0) {
                $this->layout()->iponumber = "(" . $iponumber . ")";
            }
        }

        $userauth = $this->zfcUserAuthentication();
        $this->layout()->showHome = true;
        //determine if there's a default tab to show home entry or not
        if ($userauth != null && $userauth->hasIdentity()) {
            $roles = $userauth->getIdentity()->getRoles();
            $hasDefaultTab = false;
            foreach ($roles as $r) {
                $tabs = $r->getReadtabs();
                foreach ($tabs as $t) {
                    if($t->isDefault()) {
                        $hasDefaultTab = true;
                        break;
                    }
                }
                if(!$hasDefaultTab) {
                    if(!empty($tabs)){
                        $this->layout()->showHome = false;
                    }

                }
            }
        }

        // initialisation de la session si utilisateur connecté
        $session = new Container('zone');
        if ($session->zoneshortname == null) {
            if ($this->zfcUserAuthentication()->hasIdentity()) {
                $session->zoneshortname = $this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getOrganisation()
                    ->getShortname();
            }
        }

        //session de la vue courante : day and 24/6
        $viewSession = $session->view;
        if($viewSession !== null) {
            $this->viewmodel->setVariable('view', $viewSession);
        }
        $daySession = $session->day;
        if($daySession !== null) {
            $this->viewmodel->setVariable('day', $daySession);
        }

        $this->layout()->lang = $this->config['lang'];

        //add mattermost chat
        if($this->zfcUserAuthentication()->hasIdentity()) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $mattermostLogin = $user->getMattermostUsername();
            try{
                if($mattermostLogin && strlen($mattermostLogin) > 0) {
                    $this->config['mattermost']['login'] = $mattermostLogin;
                    $configMattermost = $this->config['mattermost'];
                    $configMattermost['token'] = $this->mattermost->getToken();
                    $this->layout()->mattermost = $configMattermost;
                }
            } catch (\Exception $e) {
                $this->messages['error'][] = "Impossible de se connecter au serveur Mattermost : ".$e->getMessage();
            }
        }
    }

    public function savedayAction()
    {
        $day = $this->params()->fromQuery('day', 0);
        if($day !== 0) {
            $session = new Container('zone');
            $session->day = $day;
        }
        return new JsonModel(array('value' => $day));
    }

    public function saveviewAction()
    {
        $view = $this->params()->fromQuery('view', 0);
        if($view !== 0) {
            $session = new Container('zone');
            $session->view = $view;
        }
        return new JsonModel(array('value' => $view));
    }
}

