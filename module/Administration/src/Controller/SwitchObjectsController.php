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
use Application\Entity\Category;
use Doctrine\ORM\EntityManager;
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
class SwitchObjectsController extends FormController
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
    
        $this->layout()->title = "Centres > Objets commutables";
        
        $objectManager = $this->getEntityManager();
        
        $repo = $objectManager->getRepository('Application\Entity\SwitchObject');

        $types = $objectManager->createQueryBuilder()->select('so.type')
            ->from('Application\Entity\SwitchObject', 'so')
            ->distinct(true)
            ->getQuery()->getResult();

        $types = array_column($types, "type");

        $return = array();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
    
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
    
        $this->flashMessenger()->clearMessages();
    
        $viewmodel->setVariables(array(
            'types' => $types,
            'messages' => $return,
            'repo' => $repo
        ));
        
        return $viewmodel;
    }

    public function saveAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getForm($id, $post['type']);
            $form = $datas['form'];
            $form->setData($post);
            $radar = $datas['switchobject'];
            if ($form->isValid()) {
                
                $objectManager->persist($radar);
                $objectManager->flush();
                
                if ($radar->isDecommissionned()) {
                    // sets all related events read-only
                    $objectManager->getRepository('Application\Entity\Event')->setReadOnly($radar);
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }

        }
        return new JsonModel();
    }

    public function decommissionAction() {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $radar = $objectManager->getRepository(SwitchObject::class)->find($id);
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
        $object = $objectManager->getRepository(SwitchObject::class)->find($id);
        if ($object) {
            $objectManager->remove($object);
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
        $type = $this->params()->fromQuery('type', null);
        
        $getform = $this->getForm($id, $type);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'id' => $id
        ));
        return $viewmodel;
    }

    private function getForm($id = null, $type)
    {
        $objectManager = $this->getEntityManager();
        $object = new SwitchObject();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($object);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($object);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());

        $parents = array();
        foreach ($this->getEntityManager()
                     ->getRepository(SwitchObject::class)
                     ->findBy(array('type'=>$type, 'parent' => null, 'decommissionned' => false), array('name' => 'ASC')) as $parent) {
            $parents[$parent->getId()] = $parent->getName();
        }
        $form->get('parent')->setValueOptions($parents);
        if ($id) {
            unset($parents[$id]);
            $form->get('parent')->setValueOptions($parents);
            $object = $objectManager->getRepository(SwitchObject::class)->find($id);
            if ($object) {
                $form->bind($object);
                $form->setData($object->getArrayCopy());
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
            'switchobject' => $object
        );
    }

    public function formcategoryAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        $viewmodel->setTerminal($request->isXmlHttpRequest());

        $id = $this->params()->fromQuery('id', null);

        $form = $this->getFormCategory($id);

        $viewmodel->setVariables(array(
            'objects' => $form['objects'],
            'availableobjects' => $form['availableobjects'],
            'form' => $form['form']
        ));

        return $viewmodel;
    }

    /**
     * @param $id Not NULL
     * @return array
     */
    private function getFormCategory($id)
    {
        $object = new Category();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($object);
        $form->setHydrator(new DoctrineObject($this->getEntityManager()))->setObject($object);
        $objects = array();
        $availableobjects = array();
        $object = $this->getEntityManager()->getRepository(Category::class)->find($id);
        if($object) {
            $objects = $object->getSwitchObjects();
            $allobjects = $this->getEntityManager()->getRepository(SwitchObject::class)->findBy(array('decommissionned'=>false));
            foreach ($allobjects as $o) {
                if(!$objects->contains($o)) {
                    $availableobjects[] = $o;
                }
            }
            $form->bind($object);
            $form->setData($object->getArrayCopy());
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
            'objects' => $objects,
            'availableobjects' => $availableobjects,
            'category' => $object
        );
    }

    public function savecategoryAction()
    {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];

            $category = $this->getEntityManager()->getRepository(Category::class)->find($id);

            if($category && isset($post['objects'])) {
                $category->clearSwitchObjects();
                foreach ($post['objects'] as $objectid) {
                    $object = $this->getEntityManager()->getRepository(SwitchObject::class)->find($objectid);
                    if($object) {
                        $category->addSwitchObject($object);
                    }
                }
            }
            $this->getEntityManager()->persist($category);
            try {
                $this->getEntityManager()->flush();
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }

        }

        $json = array(

        );
        return new JsonModel($json);
    }

    public function configAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Onglets > Objets commutables";

        $types = $this->getEntityManager()->createQueryBuilder()->select('so.type')
            ->from('Application\Entity\SwitchObject', 'so')
            ->distinct(true)
            ->getQuery()->getResult();

        $types = array_column($types, "type");

        $qb = $this->getEntityManager()->createQueryBuilder();
        $categories = $qb
            ->select('c')
            ->from('Application\Entity\Category', 'c')
            ->where($qb->expr()->isInstanceOf('c', 'Application\Entity\SwitchObjectCategory'))
            ->andWhere($qb->expr()->eq('c.archived', 0))
            ->getQuery()->getResult();

        $viewmodel->setVariables(array(
            'repo' => $this->getEntityManager()->getRepository(SwitchObject::class),
            'types' => $types,
            'categories' => $categories
        ));
        
        return $viewmodel;
    }
    
    public function formobjectmodelAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormObjectModel($id);
        
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
    
    public function saveobjectmodelAction()
    {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $getform = $this->getFormObjectModel($id);
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
    
    private function getFormObjectModel($id)
    {
        $datas = array();
        $form = null;
        if ($id) {
            $radar = $this->getEntityManager()->getRepository(SwitchObject::class)->find($id);
            if ($radar) {
                $datas['radar'] = $radar;
                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb->select(array(
                    'p',
                    'c'
                ))
                    ->from('Application\Entity\PredefinedEvent', 'p')
                    ->leftJoin('p.category', 'c')
                    ->andWhere('c INSTANCE OF Application\Entity\SwitchObjectCategory');
                $models = array();
                foreach ($qb->getQuery()->getResult() as $model) {
                    foreach ($model->getCustomFieldsValues() as $value) {
                        if ($value->getCustomField()->getID() == $model->getCategory()
                                ->getSwitchObjectField()
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
