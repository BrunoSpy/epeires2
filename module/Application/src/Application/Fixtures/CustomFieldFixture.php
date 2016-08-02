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


use Application\Entity\CustomField;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CustomFieldFixture extends AbstractFixture implements DependentFixtureInterface
{

    public function getDependencies()
    {
        return array('ApplicationFixtures\CategoryFixture');
    }

    public function load(ObjectManager $manager) {

        $stringType = $manager->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string'));
        $category = $this->getReference("category");

        $name = new CustomField();
        $name->setName("Nom");
        $name->setType($stringType);
        $name->setCategory($category);
        $name->setDefaultValue("");
        $name->setTooltip("");

        $category->setFieldname($name);
        $manager->persist($name);
        $manager->persist($category);
        $manager->flush();
    }
}