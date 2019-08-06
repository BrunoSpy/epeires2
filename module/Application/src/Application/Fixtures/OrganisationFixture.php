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

namespace Application\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Application\Entity\Organisation;

class OrganisationFixture extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $organisation = new Organisation();
        $organisation->setName('CRNA-T');
        $organisation->setShortname('LFTT');
        $organisation->setIpoNumber('0611223344');
        $organisation->setLongname('CRNA Test');
        $organisation->setAddress('1 rue de test\n12345 Test');
        $organisation->setIpoEmail('test@acgv.fr');
        
        $manager->persist($organisation);
        $manager->flush();
    }
}