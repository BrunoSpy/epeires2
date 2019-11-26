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
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 *
 * @author Bruno Spyckerelle
 */
class CustomFieldMultipleAllowed extends AbstractHelper implements ServiceManagerAwareInterface
{

    private $servicemanager;

    public function __invoke($customfield)
    {
        return $this->servicemanager->get('customfieldservice')->isMultipleAllowed($customfield);
    }

    public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceLocator)
    {
        $this->servicemanager = $serviceLocator;
    }
}