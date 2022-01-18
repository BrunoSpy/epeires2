<?php
namespace Application\Fixtures;
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
use Application\Entity\Event;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixture extends AbstractFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return array('Application\Fixtures\CategoryFixture',
                    'Application\Fixtures\RecurrenceFixture');
    }

    public function load(ObjectManager $manager)
    {
        $recurrence = $this->getReference("recurrence");
        $category = $this->getReference("category");

        $event = new Event();

        $event->setStartdate(new \DateTime("2016-08-02 09:00:00"));
        $event->setEnddate(new \DateTime("2016-08-02 10:00:00"));


    }
}