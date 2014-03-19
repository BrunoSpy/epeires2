<?php

/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\Session\Container;

/**
 *
 * @author Bruno Spyckerelle
 */
class ZoneController extends FormController {

    public function indexAction() {
        $form = $this->getZoneForm();

        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $org = $user->getOrganisation();
            $zone = $user->getZone();

            $session = new Container('zone');
            $zonesession = $session->zoneshortname;

            if ($zonesession != null) { //warning: "all" == 0
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

    private function getZoneForm() {
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
