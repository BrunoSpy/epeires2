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


use Application\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixture extends AbstractFixture implements FixtureInterface
{

    public function load(ObjectManager $manager) {
        $category = new Category();

        $category->setName("TestCat");
        $category->setShortName("Test");
        $category->setColor("#FF0000");
        $category->setCompactMode(false);
        $category->setTimelineConfirmed(false);

        $tab = $manager->getRepository('Application\Entity\Tab')->findOneBy(array('isDefault' => true));
        $categories = new ArrayCollection();
        $categories->add($category);
        
        $manager->persist($category);
        $manager->persist($tab);
        $manager->flush();

        $this->addReference("category", $category);

    }
}