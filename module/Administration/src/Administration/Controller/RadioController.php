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

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Entity\Antenna;
use Application\Entity\Frequency;
use Doctrine\Common\Collections\Criteria;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class RadioController extends \Application\Controller\FormController
{

    public function indexAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Centres > Radio";
        
        $return = array();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        $this->flashMessenger()->clearMessages();
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $decommissionnedAntennas = $objectManager->getRepository('Application\Entity\Antenna')->findBy(array(
            'decommissionned' => true
        ));
        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('decommissionned', false));
        $criteria->andWhere(Criteria::expr()->orX(Criteria::expr()->in('mainantenna', $decommissionnedAntennas), Criteria::expr()->in('backupantenna', $decommissionnedAntennas), Criteria::expr()->in('mainantennaclimax', $decommissionnedAntennas), Criteria::expr()->in('backupantennaclimax', $decommissionnedAntennas)));
        
        $errorFrequencies = $objectManager->getRepository('Application\Entity\Frequency')->matching($criteria);
        
        if (count($errorFrequencies) > 0) {
            $return['error'][] = 'Attention, une ou plusieures fréquences ont des antennes qui ne sont plus en service.<br />Cette incohérence peut faire planter la page radio.';
        }
        
        $viewmodel->setVariables(array(
            'antennas' => $objectManager->getRepository('Application\Entity\Antenna')
                ->findAll(),
            'frequencies' => $objectManager->getRepository('Application\Entity\Frequency')
                ->findAll(),
            'messages' => $return
        ));
        
        return $viewmodel;
    }

    /* **************************** */
    /* Antennes */
    /* **************************** */
    public function formantennaAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormAntenna($id);
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

    public function saveantennaAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormAntenna($id);
            $form = $datas['form'];
            $form->setData($post);
            
            $antenna = $datas['antenna'];
            
            if ($form->isValid()) {
                $antenna->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')
                    ->find($post['organisation']));
                
                $objectManager->persist($antenna);
                $objectManager->flush();
                
                if ($antenna->isDecommissionned()) {
                    $objectManager->getRepository('Application\Entity\Event')->setReadOnly($antenna);
                }
            }
        }
        
        $json = array(
            'id' => $antenna->getId(),
            'name' => $antenna->getName()
        );
        
        return new JsonModel($json);
    }

    public function deleteantennaAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($id);
            if ($antenna) {
                $objectManager->remove($antenna);
                $objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormAntenna($id)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $antenna = new Antenna();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($antenna);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($antenna);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($id) {
            $antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($id);
            if ($antenna) {
                
                $form->bind($antenna);
                $form->setData($antenna->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'antenna' => $antenna
        );
    }

    /* **************************** */
    /* Fréquences */
    /* **************************** */
    public function formfrequencyAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormFrequency($id);
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

    public function savefrequencyAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormFrequency($id);
            $form = $datas['form'];
            $form->setData($post);
            
            $frequency = $datas['frequency'];
            
            if ($form->isValid()) {
                // $antenna->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')->find($post['organisation']));
                
                $objectManager->persist($frequency);
                $objectManager->flush();
            }
        }
        
        $json = array(
            'id' => $frequency->getId(),
            'name' => $frequency->getValue()
        );
        
        return new JsonModel($json);
    }

    public function deletefrequencyAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $id = $this->params()->fromQuery('id', null);
        $messages = array();
        if ($id) {
            $frequency = $objectManager->getRepository('Application\Entity\Frequency')->find($id);
            if ($frequency) {
                $objectManager->remove($frequency);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Fréquence " . $frequency->getValue() . " correctement supprimée";
                } catch (\Exception $e) {
                    $messages['error'][] = "Impossible de supprimer la fréquence";
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de supprimer la fréquence";
            }
        }
        return new JsonModel($messages);
    }

    private function getFormFrequency($id)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $frequency = new Frequency();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($frequency);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($frequency);
        
        $form->get('mainantenna')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('backupantenna')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('mainantennaclimax')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('backupantennaclimax')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        $unsetsectors = $objectManager->getRepository('Application\Entity\Sector')->getUnsetSectorsAsArray();
        $form->get('defaultsector')->setValueOptions($unsetsectors);
        
        if ($id) {
            $frequency = $objectManager->getRepository('Application\Entity\Frequency')->find($id);
            if ($frequency) {
                
                if ($frequency->getDefaultsector()) {
                    $options = $unsetsectors;
                    $options[$frequency->getDefaultsector()->getId()] = $frequency->getDefaultsector()->getName();
                    $form->get('defaultsector')->setValueOptions($options);
                }
                
                $form->bind($frequency);
                $form->setData($frequency->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'frequency' => $frequency
        );
    }

    /* **************************** */
    /* Page Fréquences */
    /* **************************** */
    public function configAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Personnalisation > Page Fréquence";
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $viewmodel->setVariables(array(
            'sectorsgroups' => $objectManager->getRepository('Application\Entity\SectorGroup')
                ->findBy(array(), array(
                'position' => 'ASC'
            )),
            'antennas' => $objectManager->getRepository('Application\Entity\Antenna')
                ->findBy(array(), array(
                'name' => 'ASC'
            ))
        ));
        
        return $viewmodel;
    }

    public function groupdownAction()
    {
        $messages = array();
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sectorgroup = $em->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($sectorgroup) {
                $sectorgroup->setPosition($sectorgroup->getPosition() + 1);
                $em->persist($sectorgroup);
                try {
                    $em->flush();
                    $messages['success'][] = "Groupe correctement modifié.";
                } catch (\Exception $e) {
                    $messages['error'][] = "Impossible d'enregistrer la modification";
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver l'élément.";
            }
        }
        return new JsonModel($messages);
    }

    public function groupupAction()
    {
        $messages = array();
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sectorgroup = $em->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($sectorgroup) {
                $sectorgroup->setPosition($sectorgroup->getPosition() - 1);
                $em->persist($sectorgroup);
                try {
                    $em->flush();
                    $messages['success'][] = "Groupe correctement modifié.";
                } catch (\Exception $e) {
                    $messages['error'][] = "Impossible d'enregistrer la modification";
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver l'élément.";
            }
        }
        return new JsonModel($messages);
    }

    public function switchdisplayAction()
    {
        $messages = array();
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sectorgroup = $em->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($sectorgroup) {
                $sectorgroup->setDisplay(! $sectorgroup->isDisplay());
                $em->persist($sectorgroup);
                try {
                    $em->flush();
                    $messages['success'][] = "Groupe correctement modifié.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver l'élément.";
            }
        }
        return new JsonModel($messages);
    }

    public function formantennamodelAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormAntennaModel($id);
        
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

    public function saveantennamodelAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $getform = $this->getFormAntennaModel($id);
            $form = $getform['form'];
            $form->setData($post);
            
            if ($form->isValid()) {
                $antenna = $getform['antenna'];
                $antenna->setModel($objectManager->getRepository('Application\Entity\PredefinedEvent')
                    ->find($form->get("models")
                    ->getValue()));
                $objectManager->persist($antenna);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage("Modèle correctement associé.");
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        
        $json = array(
            'id' => $antenna->getId(),
            'name' => $antenna->getName()
        );
        
        return new JsonModel($json);
    }

    private function getFormAntennaModel($id)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $datas = array();
        $form = null;
        if ($id) {
            $antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($id);
            if ($antenna) {
                $datas['antenna'] = $antenna;
                $qb = $objectManager->createQueryBuilder();
                $qb->select(array(
                    'p',
                    'c'
                ))
                    ->from('Application\Entity\PredefinedEvent', 'p')
                    ->leftJoin('p.category', 'c')
                    ->andWhere('c INSTANCE OF Application\Entity\AntennaCategory');
                $models = array();
                foreach ($qb->getQuery()->getResult() as $model) {
                    foreach ($model->getCustomFieldsValues() as $value) {
                        if ($value->getCustomField()->getID() == $model->getCategory()
                            ->getAntennaField()
                            ->getId()) {
                            if ($value->getValue() == $id) {
                                $models[] = $model;
                            }
                        }
                    }
                }
                $form = new \Zend\Form\Form("model");
                $hidden = new \Zend\Form\Element\Hidden("id");
                $hidden->setValue($id);
                $form->add($hidden);
                $select = new \Zend\Form\Element\Select("models");
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
