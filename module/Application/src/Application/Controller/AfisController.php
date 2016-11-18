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

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Application\Form\AfisForm;

/**
 *
 * @author Loïc Perrin
 */
class AfisController extends AbstractActionController
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    /*
     * Page d'accueil
     */
    public function indexAction()
    {
        if (!$this->authAfis('read')) return new JsonModel();
        
        return (new ViewModel())
            ->setVariables([
                'messages'  => $this->afMessages()->get(),
                'allAfis'   => $this->afSGBD($this->em)->getAll(['decommissionned' => 0])
            ]);
    }
    /*
     * Affiche formulaire d'ajout/modification
     */
    public function formAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $afis = $this->afSGBD($this->em)->get($id);
        $form = (new AfisForm($this->em))->getForm();

        ($afis->getId()) ? $form->bind($afis) : $form->setObject($afis);
        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setVariables([
                    'form' => $form
        ]);
    }

    /*
    * Changement d'état 0/1
    */
    public function switchafisAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();
        $post = $this->getRequest()->getPost();
        $id = intval($post['id']);
        $state = boolval($post['state']);

        $this->afSGBD($this->em)->switchState($id, $state);
        return new JsonModel();
    }
    /*
     * Traitement ajout/modification si formulaire valide
     */
    public function saveAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $this->afSGBD($this->em)->save($post);
        return new JsonModel();
    }
    /*
     * Suppression d'une entitée Afis
     */
    public function deleteAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $this->afSGBD($this->em)->del($id);
        return new JsonModel();
    }

    public function getAllAction($params = []) 
    {
        if (!$this->authAfis('write')) return new JsonModel();

        return $this->afSGBD($this->em)->getAll($params);
    }

    private function authAfis($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.'.$action)) ? false : true;
    }
}