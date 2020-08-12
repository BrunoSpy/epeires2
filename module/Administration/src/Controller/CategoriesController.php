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

use Application\Entity\ATFCMCategory;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\Common\Collections\Criteria;
use Application\Entity\Category;
use Application\Entity\CustomField;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Application\Controller\FormController;
use Laminas\Form\Element\Select;
use Application\Entity\RadarCategory;
use Application\Entity\AntennaCategory;
use Application\Entity\FrequencyCategory;
use Application\Entity\BrouillageCategory;
use Application\Entity\MilCategory;
use Application\Entity\AfisCategory;
use Application\Entity\FlightPlanCategory;
use Application\Entity\InterrogationPlanCategory;
use Application\Entity\FieldCategory;
use Application\Entity\AlertCategory;

/**
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 * @author Bruno Spyckerelle
 */
class CategoriesController extends FormController
{

    private $entityManager;
    private $categoryFactory;

    public function __construct(EntityManager $entityManager, $categoryfactory)
    {
        $this->entityManager = $entityManager;
        $this->categoryFactory = $categoryfactory;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function indexAction()
    {
        $viewmodel = new ViewModel();
        
        $return = array();
        
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        
        $this->flashMessenger()->clearMessages();
        
        $objectManager = $this->getEntityManager();
        
        $criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
        $criteria->orderBy(array(
            "place" => Criteria::ASC
        ));
        
        $rootcategories = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
        
        $subcategories = array();
        foreach ($rootcategories as $category) {
            $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('parent', $category));
            $criteria->orderBy(array(
                "place" => Criteria::ASC
            ));
            $subcategories[$category->getId()] = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
        }
        
        $events = array();
        $models = array();
        $fields = array();
        foreach ($objectManager->getRepository('Application\Entity\Category')->findAll() as $cat) {
            $models[$cat->getId()] = count($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                'category' => $cat->getId(),
                'parent' => null
            )));
            $fields[$cat->getId()] = count($objectManager->getRepository('Application\Entity\CustomField')->findBy(array(
                'category' => $cat->getId()
            )));
        }
        
        $viewmodel->setVariables(array(
            'categories' => $rootcategories,
            'subcategories' => $subcategories,
            'events' => $events,
            'models' => $models,
            'fields' => $fields,
            'messages' => $return
        ));
        
        $this->layout()->title = "Personnalisation > Catégories";
        
        return $viewmodel;
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getEntityManager();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $category = new Category();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($category);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($category);
        
        $form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\Category')
            ->getRootsAsArray($id, null, false));
        
        $form->get('readroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')
            ->getAllAsArray());
        
        $type = new Select('type');
        $type->setValueOptions(Category::getTypeValueOptions());
        $type->setLabel('Type : ');
        $form->add($type);
        
        if ($id) {
            // bind to the category
            $category = $objectManager->getRepository('Application\Entity\Category')->find($id);
            if ($category) {
                if ($category instanceof RadarCategory) {
                    $form->get('type')->setValue('radar');
                } elseif ($category instanceof AntennaCategory) {
                    $form->get('type')->setValue('antenna');
                } elseif ($category instanceof FrequencyCategory) {
                    $form->get('type')->setValue('frequency');
                } elseif ($category instanceof BrouillageCategory) {
                    $form->get('type')->setValue('brouillage');
                } elseif ($category instanceof MilCategory) {
                    $form->get('type')->setValue('mil');
                } elseif ($category instanceof AfisCategory) {
                    $form->get('type')->setValue('afis');
                } elseif ($category instanceof AlertCategory) {
                    $form->get('type')->setValue('alert');
                } elseif ($category instanceof InterrogationPlanCategory) {
                    $form->get('type')->setValue('intplan');
                } elseif ($category instanceof FieldCategory) {
                    $form->get('type')->setValue('field');
                } elseif ($category instanceof FlightPlanCategory) {
                    $form->get('type')->setValue('flightplan');
                } elseif ($category instanceof ATFCMCategory) {
                    $form->get('type')->setValue('atfcm');
                }
                $form->get('type')->setAttribute('disabled', true);
                
                // select parent
                if ($category->getParent()) {
                    $form->get('parent')->setAttribute('value', $category->getParent()
                        ->getId());
                }
                // fill title fields available
                $customfields = array();
                foreach ($category->getCustomfields() as $field) {
                    $customfields[$field->getId()] = $field->getName();
                }
                
                $form->get('fieldname')->setValueOptions($customfields);
                
                $form->bind($category);
                $form->setData($category->getArrayCopy());
            }
        }
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary'
            )
        ));
        
        $viewmodel->setVariables(array(
            'form' => $form,
            'system' => ($category ? $category->isSystem() : false)
        ));
        return $viewmodel;
    }

    public function saveAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $fieldname = null;
            if ($post['id']) {
                $category = $objectManager->getRepository('Application\Entity\Category')->find($post['id']);
            } else {
                if ($post['type'] == 'radar') {
                    $category = $this->categoryFactory->createRadarCategory();
                } elseif ($post['type'] == 'antenna') {
                    $category = $this->categoryFactory->createAntennaCategory();
                } elseif ($post['type'] == 'frequency') {
                    $category = $this->categoryFactory->createFrequencyCategory();
                } elseif ($post['type'] == 'brouillage') {
                    $category = $this->categoryFactory->createBrouillageCategory();
                } elseif ($post['type'] == 'mil') {
                    $category = $this->categoryFactory->createMilCategory();
                } elseif ($post['type'] == 'afis') {
                    $category = $this->categoryFactory->createAfisCategory();
                } elseif ($post['type'] == 'alert') {
                    $category = $this->categoryFactory->createAlertCategory();
                } elseif ($post['type'] == 'flightplan') {
                    $category = $this->categoryFactory->createFlightPlanCategory();
                } elseif ($post['type'] == 'intplan') {
                    $category = $this->categoryFactory->createInterrogationPlanCategory();
                } elseif ($post['type'] == 'field') {
                    $category = $this->categoryFactory->createFieldCategory();
                } elseif ($post['type'] == 'atfcm') {
                    $category = $this->categoryFactory->createATFCMCategory();
                } else {
                    $category = new Category();
                    $fieldname = new CustomField();
                    $fieldname->setCategory($category);
                    $fieldname->setName('Nom');
                    $fieldname->setType($objectManager->getRepository('Application\Entity\CustomFieldType')
                        ->findOneBy(array(
                        'type' => 'string'
                    )));
                    $fieldname->setPlace(1);
                    $fieldname->setDefaultvalue("");
                    $fieldname->setTooltip("");
                    $objectManager->persist($fieldname);
                    $category->setFieldname($fieldname);
                }
                // force fieldname value
                $fieldname = $category->getFieldname();
            }
            
            $builder = new AnnotationBuilder();
            $form = $builder->createForm($category);
            $form->setHydrator(new DoctrineObject($objectManager))->setObject($category);
            $form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\Category')
                ->getRootsAsArray($post['id']));
            $form->bind($category);
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            
            if ($form->isValid()) {
                if (! $post['id']) {
                    // if new cat, force fieldname
                    $category->setFieldname($fieldname);
                }
                if (! (strpos($category->getColor(), "#") === 0)) {
                    $category->setColor("#" . $category->getColor());
                }
                $objectManager->persist($category);
                $objectManager->flush();
                $this->flashMessenger()->addSuccessMessage("Catégorie modifiée");
            } else {
                $this->flashMessenger()->addErrorMessage("Impossible de modifier la catégorie.");
                // traitement des erreurs de validation
                $this->processFormMessages($form->getMessages());
            }
        }
        
        return $this->redirect()->toRoute('administration', array(
            'controller' => 'categories'
        ));
    }

    public function deleteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $category = $objectManager->getRepository('Application\Entity\Category')->find($id);
        
        if ($category) {
            $objectManager->getRepository(Category::class)->delete($category);
        }
        
        return $this->redirect()->toRoute('administration', array(
            'controller' => 'categories'
        ));
    }

    public function fieldsAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getEntityManager();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $fields = $objectManager->getRepository('Application\Entity\CustomField')->findBy(array(
            'category' => $id
        ), array(
            'place' => 'asc'
        ));
        
        $viewmodel->setVariables(array(
            'fields' => $fields,
            'categoryid' => $id
        ));
        return $viewmodel;
    }

    public function upcategoryAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        if ($id) {
            $cat = $objectManager->getRepository('Application\Entity\Category')->find($id);
            if ($cat) {
                $cat->setPlace($cat->getPlace() - 1);
                $objectManager->persist($cat);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie correctement modifiée.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver la catégorie";
            }
        }
        return new JsonModel($messages);
    }

    public function downcategoryAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        if ($id) {
            $cat = $objectManager->getRepository('Application\Entity\Category')->find($id);
            if ($cat) {
                $cat->setPlace($cat->getPlace() + 1);
                $objectManager->persist($cat);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie correctement modifiée.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver la catégorie";
            }
        }
        return new JsonModel($messages);
    }

    public function setplacecategoryAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $place = $this->params()->fromQuery('place', null);
        $objectManager = $this->getEntityManager();
        if ($id) {
            $cat = $objectManager->getRepository('Application\Entity\Category')->find($id);
            if ($cat) {
                $cat->setPlace($place);
                $objectManager->persist($cat);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie ".$cat->getName()." correctement modifiée en place ".$cat->getPlace().".";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver la catégorie";
            }
        }
        return new JsonModel($messages);
    }

    public function archiveAction(){
        $id = $this->params()->fromQuery('id', null);
        $archive = $this->params()->fromQuery('archive', null);
        if($id != null && $archive != null) {
            $cat = $this->getEntityManager()->getRepository('Application\Entity\Category')->find($id);
            if($cat) {
                if(strcmp($archive, 'true') == 0) {
                    $now = new \DateTime('NOW');
                    $now->setTimezone(new \DateTimeZone('UTC'));
                    $tomorrow = new \DateTime('NOW');
                    $tomorrow->setTimezone(new \DateTimeZone('UTC'));
                    $tomorrow->add(new \DateInterval('P1D'));
                    $cat->setArchiveDate($tomorrow);
                    $cat->setArchived(true);
                    $status = $this->getEntityManager()->getRepository('Application\Entity\Status')->find(3);
                    $cancelstatus = $this->getEntityManager()->getRepository('Application\Entity\Status')->find(4);
                    foreach ($this->getEntityManager()->getRepository('Application\Entity\Event')->getCurrentEventsCategory($cat->getName()) as $e) {
                        $e->close($status, $now);
                        $this->getEntityManager()->persist($e);
                    }
                    foreach ($this->getEntityManager()->getRepository('Application\Entity\Event')->getFutureEventsCategory($cat->getName()) as $e) {
                        $e->cancelEvent($cancelstatus);
                        $this->getEntityManager()->persist($e);
                    }
                } else {
                    $cat->setArchived(false);
                }
                $this->getEntityManager()->persist($cat);
                try {
                    $this->getEntityManager()->flush();
                    $this->flashMessenger()->addSuccessMessage("Catégorie correctement modifiée");
                } catch(\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->flashMessenger()->addErrorMessage('Impossible de trouver la catégorie');
            }
        }
        return new JsonModel();
    }
    
    public function defaultindexAction()
    {
        $this->layout()->title = "Personnalisation > Catégories par défaut";
        
        $objectManager = $this->getEntityManager();
        $freqcategories = $objectManager->getRepository('Application\Entity\FrequencyCategory')->findAll();
        
        $radarcategories = $objectManager->getRepository('Application\Entity\RadarCategory')->findAll();
        
        $antennacategories = $objectManager->getRepository('Application\Entity\AntennaCategory')->findAll();
        
        $brouillagecategories = $objectManager->getRepository('Application\Entity\BrouillageCategory')->findAll();
        
        return array(
            'freqcategories' => $freqcategories,
            'radarcategories' => $radarcategories,
            'antennacategories' => $antennacategories,
            'brouillagecategories' => $brouillagecategories
        );
    }

    public function changedefaultfrequencyAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $messages = array();
        if ($id) {
            $freq = $objectManager->getRepository('Application\Entity\FrequencyCategory')->find($id);
            if ($freq) {
                foreach ($objectManager->getRepository('Application\Entity\FrequencyCategory')->findAll() as $freqcat) {
                    $freqcat->setDefaultFrequencyCategory(($freqcat->getId() == $freq->getId()));
                    $objectManager->persist($freqcat);
                }
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie fréquence par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }

    public function changedefaultradarAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $messages = array();
        if ($id) {
            $radar = $objectManager->getRepository('Application\Entity\RadarCategory')->find($id);
            if ($radar) {
                foreach ($objectManager->getRepository('Application\Entity\RadarCategory')->findAll() as $radarcat) {
                    $radarcat->setDefaultRadarCategory(($radarcat->getId() == $radar->getId()));
                    $objectManager->persist($radarcat);
                }
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie radar par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }

    public function changedefaultantennaAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $messages = array();
        if ($id) {
            $antenna = $objectManager->getRepository('Application\Entity\AntennaCategory')->find($id);
            if ($antenna) {
                foreach ($objectManager->getRepository('Application\Entity\AntennaCategory')->findAll() as $antennacat) {
                    $antennacat->setDefaultAntennaCategory(($antennacat->getId() == $antenna->getId()));
                    $objectManager->persist($antennacat);
                }
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie antenne par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }

    public function changedefaultbrouillageAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $messages = array();
        if ($id) {
            $brouillage = $objectManager->getRepository('Application\Entity\BrouillageCategory')->find($id);
            if ($brouillage) {
                foreach ($objectManager->getRepository('Application\Entity\BrouillageCategory')->findAll() as $brouillagecat) {
                    $brouillagecat->setDefaultBrouillageCategory(($brouillagecat->getId() == $brouillage->getId()));
                    $objectManager->persist($brouillagecat);
                }
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie brouillage par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }
}
