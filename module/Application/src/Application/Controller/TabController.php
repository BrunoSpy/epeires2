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
    
    public function indexAction()
    {
        parent::indexAction();
        
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

        $config = $this->getServiceLocator()->get('config');
        
        $this->layout()->lang = $config['lang'];
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

