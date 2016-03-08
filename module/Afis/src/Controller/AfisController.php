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
 * TODO
 * faire un service pour l'affichage des messages
 * corriger la verrue setState(1) par defaut
 */
class AfisController extends AbstractActionController
{
    /*
     * Messages changement d'état
     */
    CONST FORMAT_SWITCH_SUCCESS = 'Nouvel état de l\'AFIS "%s" : %s.';
    CONST FORMAT_SWITCH_ERROR   = 'Impossible de modifier l\'état de l\'AFIS. %s';
    /*
     * Messages ajout Afis
     */
    CONST FORMAT_ADD_SUCCESS = 'L\'AFIS "%s" a bien été ajouté';
    CONST FORMAT_ADD_ERROR   = 'Impossible d\'ajouter l\'AFIS. %s';
    /*
     * Messages modif Afis
     */
    CONST FORMAT_EDIT_SUCCESS = 'L\'AFIS "%s" a bien été modifié';
    CONST FORMAT_EDIT_ERROR   = 'Impossible de modifier l\'AFIS. %s';
    /*
    * Messages del Afis
    */
    CONST FORMAT_DEL_SUCCESS = 'L\'AFIS "%s" a bien été supprimé';
    CONST FORMAT_DEL_ERROR   = 'Impossible de supprimer l\'AFIS. %s';
    /*
     * Page principale
     */
    public function indexAction()
    {
        $this->layout()->setTemplate('afis/layout');

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
    * Page d'administration
    */
    public function adminAction()
    {
        $this->layout()->setTemplate('afis/layout');

        $messages = [];
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $messages['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        if ($this->flashMessenger()->hasErrorMessages()) {
            $messages['error'] = $this->flashMessenger()->getErrorMessages();
        }

        return (new ViewModel())
            ->setTemplate('afis/admin')
            ->setVariables(
                [
                    'messages'  => $messages,
                    'allAfis'   => $this->getAllAfis(),
                ]);
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
     * Retourne une entité Afis suivant un ID, Si pas d'ID on retourne un new Afis.
     */
    private function getAfis($id = null)
    {
        if($id) {
            $em = $this->getServiceLocator()->get(EntityManager::class);
            $afis = $em->getRepository(Afis::class)->find($id);
            if($afis == null or !$afis->isValid()) return null;
        } else {
            $afis = new Afis();
        }
        return $afis;
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
    * Changement d'état 0/1
    */
    public function switchafisAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity() and $this->isGranted('afis.write')) {
            $request    = $this->getRequest();
            if ($request->isPost()) {
                $em     = $this->getServiceLocator()->get(EntityManager::class);
                $post   = $request->getPost();
                $afis   = $this->getAfis($post['afisid']);

                if(is_a($afis,Afis::class)) {
                    try {
                        $afis->setState((boolean) $post['state']);

                        $em->persist($afis);
                        $em->flush();

                        $this->flashMessenger()->addSuccessMessage(sprintf(self::FORMAT_SWITCH_SUCCESS, $afis->getName(), $afis->getStrState()));
                    } catch (\Exception $ex) {
                        $this->flashMessenger()->addErrorMessage(sprintf(self::FORMAT_SWITCH_ERROR, $ex));
                    }
                }
            }
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
            $em = $this->getServiceLocator()->get(EntityManager::class);
            $post = $request->getPost();

            $id     = $post['id'];
            $afis   = $this->getAfis($id);

            $form = AfisForm::newInstance($afis, $em);
            $form->setData($post);

            if ($form->isValid()) {
                try {
                    $afis = (new DoctrineHydrator($em))->hydrate($form->getData(), $afis);
                    /*
                     * VERRUE
                     * A voir pourquoi le default dans l'entite Afis ne fonctionne pas
                     */
                    (!$afis->getState()) ? $afis->setState(1) : null;

                    $em->persist($afis);
                    $em->flush();

                    if($id) {
                        $this->flashMessenger()->addSuccessMessage(sprintf(self::FORMAT_EDIT_SUCCESS, $afis->getName()));
                    } else {
                        $this->flashMessenger()->addSuccessMessage(sprintf(self::FORMAT_ADD_SUCCESS, $afis->getName()));
                    }
                } catch (\Exception $ex) {
                    if ($id) {
                        $this->flashMessenger()->addErrorMessage(sprintf(self::FORMAT_EDIT_ERROR, $ex));
                    } else {
                        $this->flashMessenger()->addErrorMessage(sprintf(self::FORMAT_ADD_ERROR, $ex));
                    }
                }
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
            $afis = $this->getAfis($id);
            if(is_a($afis,Afis::class)) {
                try {
                    $em->remove($afis);
                    $em->flush();
                    $this->flashMessenger()->addSuccessMessage(sprintf(self::FORMAT_DEL_SUCCESS, $afis->getName()));
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage(sprintf(self::FORMAT_DEL_ERROR, $ex));
                }
            }

        }
        return new JsonModel();
    }    

}