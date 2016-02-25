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
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 *
 * @author Loïc Perrin
 *        
 */
class AfisController extends AbstractActionController
{
    CONST FORMAT_SWITCH_SUCCESS = 'Nouvel état de l\'AFIS %s : %s.';
    CONST FORMAT_SWITCH_ERROR   = 'Impossible de modifier l\'état de l\'AFIS %s. \n %s';
    /*
     * Page principale
     */
    public function indexAction()
    {
        $this->layout()->setTemplate('fp/layout');

        $messages = [];
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $messages['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        if ($this->flashMessenger()->hasErrorMessages()) {
            $messages['error'] = $this->flashMessenger()->getErrorMessages();
        }

        return (new ViewModel())
            ->setTemplate('afis/index')
            ->setVariables(
                [
                    'messages'  => $messages,
                    'allAfis'   => $this->getAllAfis(['decommissionned' => 0]),
                ]);
    }
    
    /*
     * Changement d'état 0/1
     */
    public function switchafisAction()
    {
        if ($this->isGranted('afis.write') && $this->zfcUserAuthentication()->hasIdentity()) {
            $request    = $this->getRequest();
            if ($request->isPost()) {
                $em     = $this->getServiceLocator()->get(EntityManager::class);
                $post   = $request->getPost();
                $afis   = $this->getAfis($post['afisid']);

                if(is_a($afis,Afis::class) and $afis->isValid()) {
                    try {
                        $afis->setState((boolean) $post['state']);

                        $afis = null;

                        $em->persist($afis);
                        $em->flush();

                        ($afis->getState() == true) ? $etat = 'actif' : $etat = 'inactif';
                        $this->flashMessenger()
                            ->addSuccessMessage(sprintf(self::FORMAT_SWITCH_SUCCESS, $afis->getName(),$etat));
                    } catch (\Exception $ex) {
                        $this->flashMessenger()
                            ->addErrorMessage(sprintf(self::FORMAT_SWITCH_ERROR, $ex->getMessage()));
                    }
                }
            }
        }
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
        $afis = $em->getRepository(Afis::class)->find($id);

        if($afis == null){
            return null;
        }
        return ($afis->isValid()) ? $afis : null;
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