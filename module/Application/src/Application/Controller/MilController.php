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

use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class MilController extends AbstractActionController
{

    public function importNMB2BAction()
    {
        $request = $this->getRequest();
        
        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $j = $request->getParam('delta');
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));
        
        if (! $organisation) {
            throw new \RuntimeException('Unable to find organisation.');
        }
        
        $username = $request->getParam('username');
        
        $user = $objectManager->getRepository('Core\Entity\User')->findOneBy(array(
            'username' => $username
        ));
        
        if (! $user) {
            throw new \RuntimeException('Unable to find user.');
        }
        
        $day = new \DateTime('now');
        if ($j) {
            if ($j > 0) {
                $day->add(new \DateInterval('P' . $j . 'D'));
            } else {
                $j = - $j;
                $interval = new \DateInterval('P' . $j . 'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }
        
        $nmservice = $this->serviceLocator->get('nmb2b');
        
        $eaupchain = new \Core\NMB2B\EAUPChain($nmservice->getEAUPCHain($day));
        $lastSequence = $eaupchain->getLastSequenceNumber();
        $milcats = $objectManager->getRepository('Application\Entity\MilCategory')->findBy(array(
            'nmB2B' => true
        ));
        for ($i = 1; $i <= $lastSequence; $i ++) {
            $eauprsas = new \Core\NMB2B\EAUPRSAs($nmservice->getEAUPRSA(NULL, $day, $i));
            foreach ($milcats as $cat) {
                $objectManager->getRepository('Application\Entity\Event')->addZoneMilEvents($eauprsas, $cat, $organisation, $user);
            }
        }
    }
}