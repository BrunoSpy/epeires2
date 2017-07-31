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
    
    /**
     * Supprime des logs les références aux éléments supprimés.
     * Particulièrement utile après un nettoyage de la base de données
     */
    public function cleanLogsAction()
    {
        $request = $this->getRequest();
        
        if(! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectmanager = $this->getEntityManager();
        
        //get the number of rows to delete
        $dql = $objectmanager->createQueryBuilder();
        $dql->select('count(log.id)')
            ->from('Application\Entity\Log', 'log')
            ->where($dql->expr()->eq('log.action', '?1'))
            ->setParameter(1, "remove");
        try {
            $removeRowsCount = $dql->getQuery()
                ->getSingleScalarResult();
        } catch(\Exception $ex) {
            error_log($ex->getMessage());
        }
        
        $q = $objectmanager->createQuery('select l from Application\Entity\Log l where l.action = ?1');
        $q->setParameter(1, "remove");
        $iterable = $q->iterate();
        $i = 0;
        $batchSize = 50;
        print("Nettoyage des logs en cours... Cette opération peut prendre plusieurs minutes selon la taille de la base de données.\n");
        while(($row = $iterable->next()) !== false) {
            $object = $row[0];
            $q2 = $objectmanager->createQuery('select l from Application\Entity\Log l where l.objectId = ?1 and l.objectClass = ?2');
            $q2->setParameters(array(1 => $object->getObjectId(), 2 => $object->getObjectClass()));
            $iterable2 = $q2->iterate();
            while(($row2 = $iterable2->next()) !== false) {
                $objectmanager->remove($row2[0]);
            }
            $objectmanager->remove($row[0]);
            if(($i % $batchSize) === 0) {
                printf( "%.1f %% effectué... \n",(($i / $removeRowsCount)*100) );
                $objectmanager->flush();
                $objectmanager->clear();
            }
            $i++;
        }
        
        try {
            $objectmanager->flush();
            return "Nettoyage des logs effectué.";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }
}