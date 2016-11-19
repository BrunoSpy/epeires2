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
namespace Application\Controller\Plugin;

use DateTime;

use Application\Entity\FlightPlan;
use Application\Controller\Plugin\SGBDPlugin;
/**
 *
 * @author Loïc Perrin
 */
class FlightPlansSGBD extends SGBDPlugin
{
    public function getByDate(DateTime $dt)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('fp')
            ->from(FlightPlan::class,'fp')
            ->where('DATE_DIFF(fp.estimatedtimeofarrival, :date) = 0')
            ->setParameter('date', $dt);
        return $qb->getQuery()->getResult();
    }
}