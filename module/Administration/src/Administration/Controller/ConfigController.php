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
namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * 
 * @author Bruno Spyckerelle
 *
 */
class ConfigController extends AbstractActionController
{

    public function indexAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Paramètres";
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $viewmodel->setVariables(array(
            'status' => $objectManager->getRepository('Application\Entity\Status')
                ->findAll(),
            'impacts' => $objectManager->getRepository('Application\Entity\Impact')
                ->findAll(),
            'fields' => $objectManager->getRepository('Application\Entity\CustomFieldType')
                ->findAll()
        ));
        
        return $viewmodel;
    }
}
