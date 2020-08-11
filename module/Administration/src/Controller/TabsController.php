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

use Application\Controller\FormController;
use Application\Entity\Tab;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 * Gestion des onglets personnalisés
 * 
 * @author Bruno Spyckerelle
 *        
 */
class TabsController extends FormController 
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }
    
    public function indexAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Onglets > Gestion";
        
        $objectManager = $this->getEntityManager();
        
        $viewmodel->setVariables(array(
            'tabs' => $objectManager->getRepository('Application\Entity\Tab')
                ->findAll()
        ));

        //find if a role is affected to more than one default tab
        $defaulttabs = $objectManager->getRepository(Tab::class)->findBy(array('isDefault'=>true));
        $tempdefectroles = array();
        foreach ($defaulttabs as $t){
            $readroles = $t->getReadroles();
            foreach ($readroles as $r){
                $tempdefectroles[$r->getName()][] = $t->getName();
            }
        }
        $defectroles = array_filter($tempdefectroles, function($v, $k){return count($v) > 1;}, ARRAY_FILTER_USE_BOTH);
        $viewmodel->setVariable('defectroles', $defectroles);

        // gestion des erreurs lors d'un reload
        $return = array();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        
        $this->flashMessenger()->clearMessages();
        
        $viewmodel->setVariables(array(
            'messages' => $return
        ));
        return $viewmodel;
    }

    public function saveAction()
    {
        $objectManager = $this->getEntityManager();
        $messages = array();
        $json = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getForm($id);
            $form = $datas['form'];
    
            if(!isset($post['isDefault'])) {
                $post['isDefault'] = 1;
            }
            
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $tab = $datas['tab'];
            
            if ($form->isValid()) {
                
                $objectManager->persist($tab);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage("Onglet enregistré.");
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $this->processFormMessages($form->getMessages());
                $this->flashMessenger()->addErrorMessage("Impossible de modifier l'onglet.");
            }
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getForm($id);
        $form = $getform['form'];
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary'
            )
        ));
        
        $viewmodel->setVariables(array(
            'form' => $form
        ));
        return $viewmodel;
    }

    private function getForm($id)
    {
        $objectManager = $this->getEntityManager();
        $tab = new \Application\Entity\Tab();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($tab);
        
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($tab);
        
        $form->get('readroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')
            ->getAllAsArray());
        $form->get('categories')->setValueOptions($objectManager->getRepository('Application\Entity\Category')
            ->getAllAsArray(array('system'=>false)));
        
        if ($id) {
            $tab = $objectManager->getRepository('Application\Entity\Tab')->find($id);
            if ($tab) {
                $form->bind($tab);
                $form->setData($tab->getArrayCopy());
            }
        }
        
        return array(
            'form' => $form,
            'tab' => $tab
        );
    }

    public function removeAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $tab = $objectManager->getRepository('Application\Entity\Tab')->find($id);
        if ($tab) {
            $objectManager->remove($tab);
            try {
                $objectManager->flush();
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        return new JsonModel();
    }
    
    public function setdefaultAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $messages = array();
        if($id) {
            $objectManager = $this->getEntityManager();
            $tab = $objectManager->getRepository('Application\Entity\Tab')->find($id);
            if($tab) {
                $tab->setDefault(true);
            } else {
                $messages['error'][] = "Impossible de trouver l'onglet correspondant.";
            }
            try{
                $objectManager->flush();
                $messages['success'][] = "Onglet correctement passé par défaut.";
            } catch (\Exception $e) {
                $messages['error'][] = "Une erreur est survenue.";
                $messages['error'][] = $e->getMessage();
            }
        }
        $json = array();
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function unsetdefaultAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $messages = array();
        if($id) {
            $objectManager = $this->getEntityManager();
            $tab = $objectManager->getRepository('Application\Entity\Tab')->find($id);
            if($tab) {
                $tab->setDefault(false);
            } else {
                $messages['error'][] = "Impossible de trouver l'onglet correspondant.";
            }
            try{
                $objectManager->flush();
                $messages['success'][] = "Onglet correctement passé par défaut.";
            } catch (\Exception $e) {
                $messages['error'][] = "Une erreur est survenue.";
                $messages['error'][] = $e->getMessage();
            }
        }
        $json = array();
        $json['messages'] = $messages;
        return new JsonModel($json);
    }
}