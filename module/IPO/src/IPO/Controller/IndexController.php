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
namespace IPO\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $reports = $em->getRepository('IPO\Entity\Report')->findBy(array(), array(
            'created_on' => 'ASC'
        ), 10, 0);
        
        return array(
            'reports' => $reports
        );
    }

    public function getEventsAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $events = array();
        
        return new JsonModel($events);
    }

    private function getEventJson($event)
    {
        $eventservice = $this->getServiceLocator()->get('EventService');
        $customfieldservice = $this->getServiceLocator()->get('CustomFieldService');
        
        $e = array();
        $e['id'] = $event->getId();
        $e['name'] = $eventservice->getName($event);
        $e['category'] = $event->getCategory()->getName();
        $e['isroot'] = $event->getParent() ? false : true;
        $e['status'] = $event->getStatus() ? $event->getStatus()->getName() : '';
        $e['start_date'] = ($event->getStartdate() ? $event->getStartdate()->format(DATE_RFC2822) : null);
        $e['end_date'] = ($event->getEnddate() ? $event->getEnddate()->format(DATE_RFC2822) : null);
        if ($event->isPunctual()) {
            $e['duration'] = 'Ponctuel';
        } else 
            if ($event->getEnddate()) {
                $diff = \Core\DateTime\MyDateInterval::createFromDateInterval($event->getEnddate()->diff($event->getStartdate()));
                $e['duration'] = $diff->formatWithoutZeroes('%y année(s)', '%m mois', '%d jour(s)', '%h heure(s)', '%i minute(s)', '%s seconde(s)');
            } else {
                $e['duration'] = 'Non terminé';
            }
        
        return $e;
    }
}
