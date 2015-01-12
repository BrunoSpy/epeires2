<?php
/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

/**
 * @author Bruno Spyckerelle
 */
class MaintenanceController extends AbstractActionController {
	
	
    public function deleteEventsAction() {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $org = $request->getParam('orgshortname');

        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findBy(array('shortname' => $org));

        if(!$organisation){
            throw new \RuntimeException('Unable to find organisation.');
        }

        $events = $objectManager->getRepository('Application\Entity\Event')->findBy(array('organisation' => $organisation));
        $number = count($events);
        foreach ($events as $event) {
            $objectManager->remove($event);
        }
        try{
            $objectManager->flush();
            return "Suppression des évènements réussie : "+$number+" évènements supprimés";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }
}