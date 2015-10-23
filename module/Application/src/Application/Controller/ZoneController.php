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

use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\Session\Container;

/**
 *
 * @author Bruno Spyckerelle
 */
class ZoneController extends FormController
{

    public function indexAction()
    {
        $form = $this->getZoneForm();
        
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $org = $user->getOrganisation();
            $zone = $user->getZone();
            
            $session = new Container('zone');
            $zonesession = $session->zoneshortname;
            
            if ($zonesession != null) { // warning: "all" == 0
                $values = $form->get('zone')->getValueOptions();
                if (array_key_exists($zonesession, $values)) {
                    $form->get('zone')->setValue($zonesession);
                }
            } else {
                if ($zone) {
                    $form->get('zone')->setValue($zone->getShortname());
                } else {
                    $form->get('zone')->setValue($org->getShortname());
                }
            }
        } else {
            $form->get('zone')->setValue('0');
        }
        
        $this->layout()->zoneform = $form;
    }

    private function getZoneForm()
    {
        $zoneElement = new Select('zone');
        $values = array();
        $values['0'] = "Tout";
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $values[$user->getOrganisation()->getShortname()] = $user->getOrganisation()->getName();
            foreach ($user->getOrganisation()->getZones() as $zone) {
                $values[$zone->getShortname()] = " > " . $zone->getName();
            }
        }
        $zoneElement->setValueOptions($values);
        $form = new Form('zoneform');
        $form->add($zoneElement);
        
        return $form;
    }
}
