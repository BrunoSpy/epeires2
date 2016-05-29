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


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class OpSupsController extends AbstractActionController
{

    public function saveopsupAction()
    {
        $messages = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $opsupid = $post['nameopsup'];
            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $opsup = $em->getRepository('Application\Entity\OperationalSupervisor')->find($opsupid);
            if ($opsup) {
                // un seul op sup par organisation, par zone et par type
                $opsups = $em->getRepository('Application\Entity\OperationalSupervisor')->findBy(array(
                    'organisation' => $opsup->getOrganisation()
                        ->getId(),
                    'zone' => $opsup->getZone()
                        ->getId(),
                    'type' => $opsup->getType()->getId()
                ));
                foreach ($opsups as $i) {
                    $i->setCurrent(false);
                    $em->persist($i);
                }
                $opsup->setCurrent(true);
                $em->persist($opsup);
                try {
                    $em->flush();
                    $messages['success'][] = $opsup->getType()->getName()
                        . " ("
                        . $opsup->getZone()->getShortname()
                        . ")"
                        ." en fonction modifié";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de modifier le chef OP";
            }
        }
        return new JsonModel($messages);
    }

    public function opsupsAction() {
        $day = $this->params()->fromQuery('day', '');

        $viewmodel = new ViewModel();
        $request = $this->getRequest();

        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $qb = $em->createQueryBuilder();

        $daystart = new \DateTime($day);
        $daystart->setTime(0, 0, 0);
        $dayend = new \DateTime($day);
        $dayend->setTime(23, 59, 59);

        $qb->select('l')
            ->from('Application\Entity\Log', 'l')
            ->where($qb->expr()->eq('l.objectClass', '?1'))
            ->andWhere($qb->expr()->lte('l.loggedAt', '?2'))
            ->andWhere($qb->expr()->gte('l.loggedAt', '?3'))
            ->orderBy('l.id', 'DESC')
            ->setParameters(array(
                1 => 'Application\Entity\OperationalSupervisor',
                2 => $dayend->format("Y-m-d H:i:s"),
                3 => $daystart->format("Y-m-d H:i:s")
            ));

        $opsups = array();

        $query = $qb->getQuery();

        foreach ($query->getResult() as $log) {
            if(!$log->getData()["current"]) {
                $opsup = $em->getRepository('Application\Entity\OperationalSupervisor')->find($log->getObjectId());
                $entry = array('opsup' => $opsup, 'date' => $log->getLoggedAt());
                $opsups[] = $entry;
            }
        }
        
        $viewmodel->setVariables(array('opsups' => $opsups, 'day' => $daystart));
        
        return $viewmodel;

    }
}