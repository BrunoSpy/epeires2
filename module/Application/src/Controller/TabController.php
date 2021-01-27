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
use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Sets all variables needed to a controller
 * 
 * @author Bruno Spyckerelle
 */
class TabController extends FormController
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
        if ($this->lmcUserAuthentication()->hasIdentity()) {
            $iponumber = $this->lmcUserAuthentication()
                ->getIdentity()
                ->getOrganisation()
                ->getIpoNumber();
            if ($iponumber != null && strlen($iponumber) > 0) {
                $this->layout()->iponumber = "(" . $iponumber . ")";
            }
        }

        $userauth = $this->lmcUserAuthentication();
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


        $this->viewmodel->setVariable("sunrise", array_key_exists("sunrise", $this->config));

        $this->layout()->lang = $this->config['lang'];

        //add mattermost chat
        if($this->lmcUserAuthentication()->hasIdentity() && $this->isGranted('chat.access')) {
            $user = $this->lmcUserAuthentication()->getIdentity();
            $mattermostLogin = $user->getMattermostUsername();
            try{
                if($mattermostLogin && strlen($mattermostLogin) > 0 && array_key_exists('mattermost', $this->config)) {
                    $this->config['mattermost']['login'] = $mattermostLogin;
                    $configMattermost = $this->config['mattermost'];
                    $this->layout()->mattermost = $configMattermost;
                } else {
                    $this->messages['error'][] = "Impossible de se connecter au serveur Mattermost : configuration incomplète.";
                }
            } catch (\Exception $e) {
                $this->messages['error'][] = "Impossible de se connecter au serveur Mattermost : ".$e->getMessage();
            }
        }
    }

    private function getZoneForm()
    {
        $zoneElement = new Select('zone');
        $values = array();
        $values['0'] = "Tout";
        $countZones = 0;
        if ($this->lmcUserAuthentication()->hasIdentity()) {
            $user = $this->lmcUserAuthentication()->getIdentity();
            $values[$user->getOrganisation()->getShortname()] = $user->getOrganisation()->getName();
            foreach ($user->getOrganisation()->getZones() as $zone) {
                $values[$zone->getShortname()] = " > " . $zone->getName();
                $countZones++;
            }
        }
        $zoneElement->setValueOptions($values);
        $form = new Form('zoneform');
        $form->add($zoneElement);
        if($countZones > 1) {
            return $form;
        } else {
            //une seule zone : le champ est inutile
            return null;
        }
    }

}

