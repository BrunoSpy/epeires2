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
    /*
     * Entity Manager
     */
    // protected $em;

    // public function setEntityManager($em)
    // {
    //     $this->em = $em;
    // }

    // public function getEntityManager()
    // {
    //     return $this->em;
    // }
    /*
     * Pages d'accueil
     */

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        return (new ViewModel())
            ->setVariables(
                [
                    'messages'  => $this->afMessages()->get(),
                    'allAfis'   => $this->afSGBD($this->entityManager)->getAll(['decommissionned' => 0])
                ]);
    }
    /*
     * Affiche formulaire d'ajout/modification
     */
    public function formAction()
    {
        $form = (new AfisForm($this->entityManager))->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $afis = $this->afSGBD($this->entityManager)->get($request->getPost()['id']);
            // print_r($form);
            print_r($afis->getArrayCopy());
            $form->setData($afis->getArrayCopy());
        }
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

        if (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.write'))
            return new JsonModel();

        $request    = $this->getRequest();
        if ($request->isPost()) {
            $post   = $request->getPost();
            $this->afisSGBD($this->entityManager)->switchState($post);
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
            $this->afSGBD($this->entityManager)->save($post);
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
                $this->afisSGBD($this->entityManager)->del($id);
            }
        }
        return new JsonModel();
    }

    public function getAllAction($params = []) 
    {
        return $this->afSGBD($this->entityManager)->getAll($params);
    }
}