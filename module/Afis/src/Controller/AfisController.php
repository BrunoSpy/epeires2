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
namespace Afis\Controller;

use Core\Controller\AbstractEntityManagerAwareController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Afis\Form\AfisForm;

/**
 *
 * @author Loïc Perrin
 */
class AfisController extends AbstractEntityManagerAwareController
{
    /*
     * Entity Manager
     */
    protected $em;

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function getEntityManager()
    {
        return $this->em;
    }
    /*
     * Pages d'accueil
     */
    public function indexAction()
    {
        $this->layout()->setTemplate('afis/layout');
        return (new ViewModel())
            ->setTemplate('afis/index')
            ->setVariables(
                [
                    'messages'  => $this->afisMessages()->get(),
                    'allAfis'   => $this->afisSGBD($this->em)->getAll(['decommissionned' => 0])
                ]);
    }
    /*
    * Page d'administration
    */
    public function adminAction()
    {
        $this->layout()->setTemplate('afis/layout');
        return (new ViewModel())
            ->setTemplate('afis/admin')
            ->setVariables(
                [
                    'messages'  => $this->afisMessages()->get(),
                    'allAfis'   => $this->afisSGBD($this->em)->getAll()
                ]);
    }

    /*
     * Affiche formulaire d'ajout/modification
     */
    public function formAction()
    {
        $form = (new AfisForm($this->em))->getForm();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $afis = $this->afisSGBD($this->em)->get($request->getPost()['afisid']);
            $form->setData($afis->getArrayCopy());
        }
        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setTemplate('afis/form')
                ->setVariables([
                    'form' => $form
        ]);
    }

    /*
    * Changement d'état 0/1
    */
    public function switchafisAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.write'))
            return new JsonModel();

        $request    = $this->getRequest();
        if ($request->isPost()) {
            $post   = $request->getPost();
            $this->afisSGBD($this->em)->switchState($post);
        }
        return new JsonModel();
    }
    /*
     * Traitement ajout/modification si formulaire valide
     */
    public function saveAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.write'))
            return new JsonModel();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $this->afisSGBD($this->em)->save($post);
        }
        return new JsonModel();
    }
    /*
     * Suppression d'une entitée Afis
     */
    public function deleteAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.write'))
            return new JsonModel();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->getRequest()->getPost()['afisid'];
            if ($id) {
                $this->afisSGBD($this->em)->del($id);
            }
        }
        return new JsonModel();
    }
}