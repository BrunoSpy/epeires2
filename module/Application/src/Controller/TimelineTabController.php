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
namespace Application\Controller;

use Application\Entity\Category;
use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\Log\Logger;
use Laminas\View\Model\ViewModel;

/**
 * Controller for a timeline tab
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class TimelineTabController extends TabController
{

    protected $entityManager;

    protected $logger;

    public function __construct(EntityManager $entityManager,
                                $config, $mattermost, $logger)
    {
        parent::__construct($config, $mattermost);
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function indexAction()
    {
        parent::indexAction();
        
        $return = $this->messages;

        if ($this->flashMessenger()->hasErrorMessages()) {
            foreach ($this->flashMessenger()->getErrorMessages() as $m) {
                $return['error'][] = $m;
            }
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            foreach ($this->flashMessenger()->getSuccessMessages() as $m) {
                $return['success'][] = $m;
            }
        }
        
        $this->flashMessenger()->clearMessages();

        $tabid = $this->params()->fromQuery('tabid', null);

        $userauth = $this->lmcUserAuthentication();

        if ($tabid) {
            $tab = $this->entityManager->getRepository('Application\Entity\Tab')->find($tabid);
            if ($tab) {
                $categories = $tab->getCategories();
                $cats = array();
                foreach ($categories as $cat) {
                    $cats[] = $cat->getId();
                }
                $this->viewmodel->setVariable('onlyroot', $tab->isOnlyroot());
                $this->viewmodel->setVariable('cats', $cats);
                $this->viewmodel->setVariable('default', false);
            } else {
                $return['error'][] = "Impossible de trouver l'onglet correspondant. Contactez votre administrateur.";
            }
        } else {
            $return['error'][] = "Aucun onglet défini. Contactez votre administrateur.";
        }
        $postitAllowed = false;
        if($userauth->hasIdentity()) {
            //determine if user can create postit
            $postitCategory = $this->entityManager->getRepository(Category::class)->findOneBy(array('name'=>'PostIt'));
            $userroles = $this->lmcUserAuthentication()->getIdentity()->getRoles();
            foreach ($userroles as $role) {
                foreach ($role->getReadCategories() as $cat) {
                    if($cat->getId() == $postitCategory->getId()) {
                        $postitAllowed = true;
                        break;
                    }
                }
                if($postitAllowed) {
                    break;
                }
            }
        }

        $this->viewmodel->setVariable('postitAllowed', $postitAllowed);
        
        $this->viewmodel->setVariable('messages', $return);

        $this->getLogger()->info('timeline index.html');

        return $this->viewmodel;
    }
    
    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return mixed
     */
    public function getLogger() : Logger
    {
        return $this->logger;
    }

}