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
namespace Administration\Controller;

use Core\Controller\AbstractEntityManagerAwareController;
use Zend\Console\Request as ConsoleRequest;

/**
 *
 * @author Bruno Spyckerelle
 */
class MaintenanceController extends AbstractEntityManagerAwareController
{

    public function deleteEventsAction()
    {
        $request = $this->getRequest();
        
        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectManager = $this->getEntityManager();
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));
        
        if (! $organisation) {
            throw new \RuntimeException('Unable to find organisation.');
        }
        
        $batchSize = 20;
        $i = 0;
        $q = $objectManager->createQuery('select e from Application\Entity\Event e where e.organisation = ?1');
        $q->setParameter(1, $organisation->getId());
        
        $iterable = $q->iterate();
        while (($row = $iterable->next()) !== false) {
            $objectManager->remove($row[0]);
            if (($i % $batchSize) === 0) {
                $objectManager->flush();
                $objectManager->clear();
            }
            ++ $i;
        }
        try {
            $objectManager->flush();
            return "Suppression des évènements réussie.";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }
}