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
use Laminas\Json\Json;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Application\Entity\SwitchObject;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class RadarsController extends AbstractEntityManagerAwareController
{

    public function indexAction()
    {
        $viewmodel = new ViewModel();
    
        $this->layout()->title = "Centres > Radars";
        
        $objectManager = $this->getEntityManager();
        
        $radars = $objectManager->getRepository('Application\Entity\SwitchObject')->findAll();
    
        $return = array();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
    
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
    
        $this->flashMessenger()->clearMessages();
    
        $viewmodel->setVariables(array(
            'messages' => $return,
            'radars' => $radars
        ));
        
        return $viewmodel;
    }

    public function saveAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getForm($id);
            $form = $datas['form'];
            $form->setData($post);
            $radar = $datas['radar'];
            
            if ($form->isValid()) {
                
                $objectManager->persist($radar);
                $objectManager->flush();
                
                if ($radar->isDecommissionned()) {
                    // sets all related events read-only
                    $objectManager->getRepository('Application\Entity\Event')->setReadOnly($radar);
                }
            }
        }
        return new JsonModel();
    }

    public function decommissionAction() {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $radar = $objectManager->getRepository(Application\Entity\SwitchObject::class)->find($id);
        if($radar) {
            $radar->setDecommissionned(true);
            $objectManager->getRepository('Application\Entity\Event')->setReadOnly($radar);
            $objectManager->persist($radar);
            try{
                $objectManager->flush();
                $this->flashMessenger()->addSuccessMessage('Radar '.$radar->getName().' correctement archivé.');
            } catch(\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        return new JsonModel();
    }
    
    public function deleteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $radar = $objectManager->getRepository(Application\Entity\SwitchObject::class)->find($id);
        if ($radar) {
            $objectManager->remove($radar);
            $objectManager->flush();
        }
        return new JsonModel();
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getForm($id);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'id' => $id
        ));
        return $viewmodel;
    }

    private function getForm($id = null)
    {
        $objectManager = $this->getEntityManager();
        $radar = new Radar();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($radar);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($radar);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($id) {
            $radar = $objectManager->getRepository(Application\Entity\SwitchObject::class)->find($id);
            if ($radar) {
                $form->bind($radar);
                $form->setData($radar->getArrayCopy());
            }
        }
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary btn-small'
            )
        ));
        
        return array(
            'form' => $form,
            'radar' => $radar
        );
    }
    
    public function configAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Onglets > Radars";
        
        $viewmodel->setVariable(
            'radars', $this->getEntityManager()->getRepository(Application\Entity\SwitchObject::class)
                ->findBy(array(), array('name' => 'ASC'))
        );
        
        return $viewmodel;
    }
    
    public function formradarmodelAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormRadarModel($id);
        
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
    
    public function saveradarmodelAction()
    {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $getform = $this->getFormRadarModel($id);
            $form = $getform['form'];
            $form->setData($post);
            
            if ($form->isValid()) {
                $radar = $getform['radar'];
                $radar->setModel($this->getEntityManager()->getRepository('Application\Entity\PredefinedEvent')
                    ->find($form->get("models")
                        ->getValue()));
                $this->getEntityManager()->persist($radar);
                try {
                    $this->getEntityManager()->flush();
                    $this->flashMessenger()->addSuccessMessage("Modèle correctement associé.");
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        
        $json = array(
            'id' => $radar->getId(),
            'name' => $radar->getName()
        );
        
        return new JsonModel($json);
    }
    
    private function getFormRadarModel($id)
    {
        $datas = array();
        $form = null;
        if ($id) {
            $radar = $this->getEntityManager()->getRepository(Application\Entity\SwitchObject::class)->find($id);
            if ($radar) {
                $datas['radar'] = $radar;
                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb->select(array(
                    'p',
                    'c'
                ))
                    ->from('Application\Entity\PredefinedEvent', 'p')
                    ->leftJoin('p.category', 'c')
                    ->andWhere('c INSTANCE OF Application\Entity\RadarCategory');
                $models = array();
                foreach ($qb->getQuery()->getResult() as $model) {
                    foreach ($model->getCustomFieldsValues() as $value) {
                        if ($value->getCustomField()->getID() == $model->getCategory()
                                ->getRadarfield()
                                ->getId()) {
                            if ($value->getValue() == $id) {
                                $models[] = $model;
                            }
                        }
                    }
                }
                $form = new \Laminas\Form\Form("model");
                $hidden = new \Laminas\Form\Element\Hidden("id");
                $hidden->setValue($id);
                $form->add($hidden);
                $select = new \Laminas\Form\Element\Select("models");
                $optionsModels = array();
                foreach ($models as $model) {
                    $optionsModels[$model->getId()] = $model->getName();
                }
                $select->setValueOptions($optionsModels);
                if (count($optionsModels) == 0) {
                    $select->setEmptyOption("Aucun modèle à associer");
                } else {
                    $select->setEmptyOption("Choisir le modèle à associer.");
                }
                $select->setLabel("Modèle : ");
                $form->add($select);
                
                $datas['form'] = $form;
            }
        }
        return $datas;
    }
}
