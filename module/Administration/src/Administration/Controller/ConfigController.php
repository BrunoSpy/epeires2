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

use Core\Controller\AbstractEntityManagerAwareController;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;

/**
 * 
 * @author Bruno Spyckerelle
 *
 */
class ConfigController extends AbstractEntityManagerAwareController
{

    private $config;

    public function __construct(EntityManager $entityManager, $config)
    {
        parent::__construct($entityManager);
        $this->config = $config;
    }

    public function indexAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Paramètres";
        
        $objectManager = $this->getEntityManager();

        $params = array();

        if(array_key_exists('frequency_tab_colors', $this->config)) {
            $colors = $this->config['frequency_tab_colors'];
            if(array('warning', $colors)) {
                $params['Onglet Radio - Couleur Warning'] = $colors['warning'];
            }
            if(array('ok', $colors)) {
                $params['Onglet Radio - Couleur OK'] = $colors['ok'];
            }
        }

        if(array_key_exists('frequency_test_menu', $this->config)) {
            if($this->config('frequency_test_menu')) {
                $params['Onglet Radio - Menu test fréquences'] = "Actif";
            } else {
                $params['Onglet Radio - Menu test fréquences'] = "Inactif";
            }
        } else {
            $params['Onglet Radio - Menu test fréquences'] = "Inactif";
        }

        $viewmodel->setVariables(array(
            'status' => $objectManager->getRepository('Application\Entity\Status')
                ->findAll(),
            'impacts' => $objectManager->getRepository('Application\Entity\Impact')
                ->findAll(),
            'fields' => $objectManager->getRepository('Application\Entity\CustomFieldType')
                ->findAll(),
            'params' => $params
        ));
        
        return $viewmodel;
    }
}
