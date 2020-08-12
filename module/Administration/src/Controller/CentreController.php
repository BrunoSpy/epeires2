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

use Application\Entity\SectorsGroupsRelation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Laminas\Form\Element\Select;
use Laminas\InputFilter\Input;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Application\Entity\Organisation;
use Application\Entity\QualificationZone;
use Application\Entity\SectorGroup;
use Application\Entity\Sector;
use Application\Controller\FormController;
use Application\Entity\Stack;

/**
 * 
 * @author Bruno Spyckerelle
 *
 */
class CentreController extends FormController
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Centre > Général";
        
        $objectManager = $this->entityManager;
        
        $qualifzones = $objectManager->getRepository('Application\Entity\QualificationZone')->findAll();
        
        $sectors = $objectManager->getRepository('Application\Entity\Sector')->findAll();
        
        $centres = $objectManager->getRepository('Application\Entity\Organisation')->findAll();
        
        $groups = $objectManager->getRepository('Application\Entity\SectorGroup')->findAll();
        
        $stacks = $objectManager->getRepository('Application\Entity\Stack')->findAll();
        
        $viewmodel->setVariables(array(
            'qualifzones' => $qualifzones,
            'sectors' => $sectors,
            'centres' => $centres,
            'groups' => $groups,
            'stacks' => $stacks
        ));
        
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

    /* **************************** */
    /* Organisations */
    /* **************************** */
    public function formorganisationAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormOrganisation($id);
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

    public function saveorganisationAction()
    {
        $objectManager = $this->entityManager;
        $messages = array();
        $json = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormOrganisation($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $organisation = $datas['organisation'];
            
            if ($form->isValid()) {
                $objectManager->persist($organisation);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage("Organisation enregistrée.");
                    $org = array(
                        'id' => $organisation->getId(),
                        'name' => $organisation->getName()
                    );
                    $json['org'] = $org;
                    $json['success'] = true;
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $this->processFormMessages($form->getMessages());
                $this->flashMessenger()->addErrorMessage("Impossible d\'enregistrer l'organisation.");
            }
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function deleteorganisationAction()
    {
        $objectManager = $this->entityManager;
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $org = $objectManager->getRepository('Application\Entity\Organisation')->find($id);
            if ($org) {
                $objectManager->remove($org);
                $objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormOrganisation($id)
    {
        $objectManager = $this->entityManager;
        $organisation = new Organisation();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($organisation);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($organisation);
        
        if ($id) {
            $organisation = $objectManager->getRepository('Application\Entity\Organisation')->find($id);
            if ($organisation) {
                $form->bind($organisation);
                $form->setData($organisation->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'organisation' => $organisation
        );
    }

    /* **************************** */
    /* Qualif */
    /* **************************** */
    public function formqualifAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormQualif($id);
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

    public function savequalifAction()
    {
        $objectManager = $this->entityManager;
        $qualif = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormQualif($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            
            $qualif = $datas['qualif'];
            
            if ($form->isValid()) {
                $qualif->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')
                    ->find($post['organisation']));
                
                $objectManager->persist($qualif);
                $objectManager->flush();
                $this->flashMessenger()->addSuccessMessage("Zone de qualification enregistrée.");
            } else {
                $this->processFormMessages($form->getMessages());
                $this->flashMessenger()->addErrorMessage("Impossible d\'enregistrer la zone de qualification.");
            }
        }
        
        if ($qualif) {
            $json = array(
                'id' => $qualif->getId(),
                'name' => $qualif->getName()
            );
        }
        return new JsonModel($json);
    }

    public function deletequalifAction()
    {
        $objectManager = $this->entityManager;
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $qualif = $objectManager->getRepository('Application\Entity\QualificationZone')->find($id);
            if ($qualif) {
                $objectManager->remove($qualif);
                $objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormQualif($id)
    {
        $objectManager = $this->entityManager;
        $qualif = new QualificationZone();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($qualif);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($qualif);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($id) {
            $qualif = $objectManager->getRepository('Application\Entity\QualificationZone')->find($id);
            if ($qualif) {
                $form->bind($qualif);
                $form->setData($qualif->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'qualif' => $qualif
        );
    }

    /* **************************** */
    /* Groupes de secteurs */
    /* **************************** */
    public function getgroupsAction()
    {
        $objectManager = $this->entityManager;
        $zone = $this->params()->fromQuery('zone', null);
        $groups = array();
        if ($zone) {
            foreach ($objectManager->getRepository('Application\Entity\SectorGroup')->findBy(array(
                'zone' => $zone
            )) as $group) {
                $groups[$group->getId()] = $group->getName();
            }
        }
        return new JsonModel($groups);
    }

    public function formgroupAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormGroup($id);
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
            'form' => $form,
            'avalaiblesectors' => $getform['avalaiblesectors'],
            'sectors' => $getform['sectors']
        ));
        return $viewmodel;
    }

    public function savegroupAction()
    {
        $objectManager = $this->entityManager;
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormGroup($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $group = $datas['group'];
            if ($form->isValid()) {
                $group->setZone($objectManager->getRepository('Application\Entity\QualificationZone')
                    ->find($post['zone']));
                $sectorsgroupsrelation = new ArrayCollection();
                if(isset($post['sectors'])) {
                    $place = 0;
                    foreach ($post['sectors'] as $sectorid) {
                        $sector = $objectManager->getRepository('Application\Entity\Sector')->find($sectorid);
                        if($sector) {
                            $relation = null;
                            if($id) {
                                
                                $relation = $objectManager->getRepository('Application\Entity\SectorsGroupsRelation')
                                    ->findOneBy(array('sector' => $sectorid, 'sectorgroup' => $id));
                            }
                            if(!$relation) {
                                $relation = new SectorsGroupsRelation();
                                $relation->setSector($sector);
                                $relation->setSectorgroup($group);
                            }
                            $relation->setPlace($place);
                            $place++;
                            $sectorsgroupsrelation->add($relation);
                        }
                    }
                }
                $actualrelations = $group->getSectorsGroupsRelations();
                foreach ($sectorsgroupsrelation as $newrelation) {
                    $actualrelations->removeElement($newrelation);
                }
                foreach ($actualrelations as $relation) {
                    $objectManager->remove($relation);
                }
                $group->setSectorsGroupsRelations($sectorsgroupsrelation);
                $objectManager->persist($group);
                try{
                    $objectManager->flush();
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        
        $json = array(
            'id' => $group->getId(),
            'name' => $group->getName()
        );
        
        return new JsonModel($json);
    }

    public function deletegroupAction()
    {
        $objectManager = $this->entityManager;
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $group = $objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($group) {
                $objectManager->remove($group);
                $objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormGroup($id)
    {
        $objectManager = $this->entityManager;
        $group = new SectorGroup();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($group);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($group);
        
        $form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
            ->getAllAsArray());
        
        $avalaibleSectors = array();
        $sectors = array();
        
        if ($id) {
            $group = $objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($group) {
                $sectors = $group->getSectors();
                $allSectors = $objectManager->getRepository('Application\Entity\Sector')->findBy(array('zone' => $group->getZone()->getId()));
                foreach ($allSectors as $s) {
                    if(!$sectors->contains($s)) {
                        $avalaibleSectors[] = $s;
                    }
                }
                $form->bind($group);
                $form->setData($group->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'group' => $group,
            'avalaiblesectors' => $avalaibleSectors,
            'sectors' => $sectors
        );
    }

    /* **************************** */
    /* Secteurs */
    /* **************************** */
    public function getsectorsAction()
    {
        $objectManager = $this->entityManager;
        $zone = $this->params()->fromQuery('zone', null);
        $sectors = array();
        if ($zone) {
            foreach ($objectManager->getRepository('Application\Entity\Sector')->findBy(array(
                'zone' => $zone
            )) as $sector) {
                $sectors[$sector->getId()] = $sector->getName();
            }
        }
        return new JsonModel($sectors);
    }

    public function formsectorAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormSector($id);
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

    public function savesectorAction()
    {
        $objectManager = $this->entityManager;
        $messages = array();
        $sector = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormSector($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $sector = $datas['sector'];
            
            if ($form->isValid()) {
                $sector->setZone($objectManager->getRepository('Application\Entity\QualificationZone')
                    ->find($post['zone']));

                $sectorsgroupsrelations = new ArrayCollection();
                if(isset($post['sectorsgroups'])) {
                    foreach ($post['sectorsgroups'] as $sectorgroupId) {
                        $sectorGroup = $objectManager->getRepository('Application\Entity\SectorGroup')->find($sectorgroupId);
                        if($sectorGroup) {
                            $relation = null;
                            if ($id) {
                                $relation = $objectManager
                                    ->getRepository('Application\Entity\SectorsGroupsRelation')
                                    ->findOneBy(array('sector' => $id, 'sectorgroup' => $sectorgroupId));
                            }
                            if(!$relation) {
                                $relation = new SectorsGroupsRelation();
                                $relation->setSector($sector);
                                $relation->setSectorgroup($sectorGroup);
                            }
                            $sectorsgroupsrelations->add($relation);
                        }
                    }
                }
                $actualrelations = $sector->getSectorsGroupsRelations();
                foreach ($sectorsgroupsrelations as $newrelation) {
                    $actualrelations->removeElement($newrelation);
                }
                foreach ($actualrelations as $relation) {
                    $objectManager->remove($relation);
                }
                $sector->setSectorsGroupsRelations($sectorsgroupsrelations);
                $objectManager->persist($sector);
                try {
                    $objectManager->flush();
                    if ($sector->isDecommissionned()) {
                        // sets all related events read-only
                        $objectManager->getRepository('Application\Entity\Event')->setReadOnly($sector);
                    }
                    if(!$id) {
                        $this->flashMessenger()->addSuccessMessage('Nouveau secteur enregistré.');
                    } else {
                        $this->flashMessenger()->addSuccessMessage('Secteur '.$sector->getName().' modifié.');
                    }
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->flashMessenger()->addErrorMessage("Impossible d'enregistrer le secteur.");
                $this->processFormMessages($form->getMessages());
            }
        }
        if ($sector) {
            $json['id'] = $sector->getId();
            $json['name'] = $sector->getName();
        }
        $json['messages'] = $messages;
        
        return new JsonModel($json);
    }

    public function deletesectorAction()
    {
        $objectManager = $this->entityManager;
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sector = $objectManager->getRepository('Application\Entity\Sector')->find($id);
            if ($sector) {
                // TODO use cascading instead
                if ($sector->getFrequency()) {
                    $sector->getFrequency()->setDefaultsector(null);
                }
                $objectManager->remove($sector);
                $objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormSector($id)
    {
        $objectManager = $this->entityManager;
        $sector = new Sector();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($sector);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($sector);
        
        $form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
            ->getAllAsArray());
        
        $sectorsgroups = new Select('sectorsgroups');
        $sectorsgroups->setAttribute('multiple', 'true');
        $sectorsgroups->setLabel('Regroupements : ');
        $sectorsgroups->setDisableInArrayValidator(true);
        $filter = new Input('sectorsgroups');
        $filter->setRequired(false);
        $form->add($sectorsgroups);
        
        $form->getInputFilter()->add($filter);
        if ($id) {
            $sector = $objectManager->getRepository('Application\Entity\Sector')->find($id);
            if ($sector) {
                $groups = array();
                foreach ($objectManager->getRepository('Application\Entity\SectorGroup')->findBy(array(
                    'zone' => $sector->getZone()->getId()
                )) as $group) {
                    $groups[$group->getId()] = $group->getName();
                }
                $form->get('sectorsgroups')->setValueOptions($groups);
                
                $form->bind($sector);
                $form->setData($sector->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'sector' => $sector
        );
    }

    /* **************************** */
    /* Attentes */
    /* **************************** */
    public function formstackAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormStack($id);
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

    public function savestackAction()
    {
        $objectManager = $this->entityManager;
        $stack = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormStack($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $stack = $datas['stack'];
            
            if ($form->isValid()) {
                $stack->setZone($objectManager->getRepository('Application\Entity\QualificationZone')
                    ->find($post['zone']));
                
                $objectManager->persist($stack);
                try {
                    $objectManager->flush();
                    if ($stack->isDecommissionned()) {
                        // sets all related events read-only
                        $objectManager->getRepository('Application\Entity\Event')->setReadOnly($stack);
                    }
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage(addslashes($e->getMessage()));
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        
        if ($stack) {
            $json = array(
                'id' => $stack->getId(),
                'name' => $stack->getName()
            );
        } else {
            $json = array();
        }
        
        return new JsonModel($json);
    }

    public function deletestackAction()
    {
        $objectManager = $this->entityManager;
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $stack = $objectManager->getRepository('Application\Entity\Stack')->find($id);
            if ($stack) {
                $objectManager->remove($stack);
                $objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormStack($id)
    {
        $objectManager = $this->entityManager;
        $stack = new Stack();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($stack);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($stack);
        
        $form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
            ->getAllAsArray());
        
        if ($id) {
            $stack = $objectManager->getRepository('Application\Entity\Stack')->find($id);
            if ($stack) {
                $form->bind($stack);
                $form->setData($stack->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'stack' => $stack
        );
    }
}
