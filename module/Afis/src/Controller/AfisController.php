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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use Afis\Entity\Afis;
use Afis\Form\AfisForm;

/**
 *
 * @author Loïc Perrin
 *        
 */
class AfisController extends AbstractActionController
{
    /*
     * Page principale
     */
    public function indexAction()
    {
        //var_dump($this->aform());
        //var_dump($this->afisForm($this->getServiceLocator(), new Afis()));
        //var_dump($this->today());
        //parent::indexAction();
        $this->layout()->setTemplate('afis/layout');
        return [
            'allAfis'  => $this->getAllAfis(['decommissionned' => 0])            
        ];
    }
    
    /*
     * Changement d'état 0/1
     */
    public function switchafisAction()
    {
        /*
         * TODO
         * Pas d'authentification pour l'instant
         * Ni de message
         */
         //$messages = array();
        //if ($this->isGranted('afis.write') && $this->zfcUserAuthentication()->hasIdentity()) {
            $request    = $this->getRequest();
            if ($request->isPost()) {
                //$em     = $this->getServiceLocator()->get(EntityManager::class);
                $post   = $request->getPost();
                $afis   = $this->getAfis($post['afisid']);
                $afis->setState((boolean) $post['state']);
                
                $this->em()->persist($afis);
                $this->em()->flush();
            }
        //}    
        return new JsonModel();
    }
    
    /*
     * Retourne toutes les entitées AFIS suivant un tableau de paramètres
     */  
    private function getAllAfis(array $params = [])
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        $allAfis = [];
        foreach ($em->getRepository(Afis::class)->findBy($params) as $afis) 
        {
            $allAfis[] = $afis;
        }
        return $allAfis;
    }  
    
    /*
     * Retourne une entité Afis suivant un ID
     */
    private function getAfis($id)
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        return $em->getRepository(Afis::class)->find($id);
    }

    /*
     * Page d'administration
     */
    public function adminAction()
    {
        $this->layout()->setTemplate('afis/layout');
        return [
            'allAfis'  => $this->getAllAfis()            
        ];
    }
    
    /*
     * Affiche formulaire d'ajout/modification
     */
    public function formAction()
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        $form = AfisForm::newInstance(new Afis(), $em);
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $afis = $this->getAfis($request->getPost()['afisid']);
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
     * Traitement ajout/modification si formulaire valide
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $em = $this->getServiceLocator()->get(EntityManager::class);
            $post = $request->getPost();
            if($post['id']){
                $afis = $em->getRepository(Afis::class)->find($post['id']);
            } else {
                $afis = new Afis();
            }
            $form = AfisForm::newInstance($afis, $em);
            $form->setData($request->getPost());
            if($form->isValid())
            {
                $afis = (new DoctrineHydrator($em))->hydrate($form->getData(), $afis);
                /*
                 * VERRUE
                 */
                (!$afis->getState()) ? $afis->setState(1) : null;
                $em->persist($afis);
                $em->flush();    
            }
        }
        return new JsonModel();
    }
    /*
     * Suppression d'une entité
     */
    public function deleteAction()
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        $id = $this->getRequest()->getPost()['afisid'];
        
        if ($id) {
            $afis = $em->getRepository(Afis::class)->find($id);
            $em->remove($afis);
            $em->flush();
        }
        return new JsonModel();
    }    

}