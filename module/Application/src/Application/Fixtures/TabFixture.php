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

namespace ApplicationFixtures;


use Application\Entity\Tab;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TabFixture extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager) {
    
        $category = $this->getReference("category");
        
        $tab = new Tab();
        $tab->setName('Timeline principale');
        $tab->setShortName('Timeline');
        $tab->setDefault(true);
        
        $categories = new ArrayCollection();
        $categories->add($category);
        $tab->addCategories($categories);
        
        $manager->persist($tab);
        $manager->flush();
        
    }
}