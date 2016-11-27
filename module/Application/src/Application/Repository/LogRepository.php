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
namespace Application\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

/**
 *
 * @author Bruno Spyckerelle
 *
 */
class LogRepository extends LogEntryRepository
{

    /**
     * Returns an array of opsups changes
     * @param \DateTime $start
     * @param \DateTime $end
     * @param $inversed if true, returns opsups by end of shift
     */
    public function getOpSupsChanges(\DateTime $start, \DateTime $end, $inversed, $order = 'DESC')
    {

        $qb = $this->createQueryBuilder('l');

        $qb->where($qb->expr()->eq('l.objectClass', '?1'))
            ->andWhere($qb->expr()->lte('l.loggedAt', '?2'))
            ->andWhere($qb->expr()->gte('l.loggedAt', '?3'))
            ->orderBy('l.id', $order)
            ->setParameters(array(
                1 => 'Application\Entity\OperationalSupervisor',
                2 => $end->format("Y-m-d H:i:s"),
                3 => $start->format("Y-m-d H:i:s")
            ));

        $opsups = array();

        $query = $qb->getQuery();

        foreach ($query->getResult() as $log) {
            if($inversed) {
                if (!$log->getData()["current"]) {
                    $opsup = $this->getEntityManager()->getRepository('Application\Entity\OperationalSupervisor')->find($log->getObjectId());
                    $entry = array('opsup' => $opsup, 'date' => $log->getLoggedAt());
                    $opsups[] = $entry;
                }
            } else {
                if ($log->getData()["current"]) {
                    $opsup = $this->getEntityManager()->getRepository('Application\Entity\OperationalSupervisor')->find($log->getObjectId());
                    $entry = array('opsup' => $opsup, 'date' => $log->getLoggedAt());
                    $opsups[] = $entry;
                }
            }
        }

        return $opsups;
    }

}