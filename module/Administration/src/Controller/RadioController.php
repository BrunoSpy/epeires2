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

use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject;
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

    private $objectManager;

    public function __construct($entityManager)
    {
        $this->objectManager = $entityManager;
    }

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

        $decommissionnedAntennas = $this->objectManager->getRepository('Application\Entity\Antenna')->findBy(array(
            'decommissionned' => true
        ));
        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('decommissionned', false));
        $criteria->andWhere(Criteria::expr()->orX(
            Criteria::expr()->in('mainantenna', $decommissionnedAntennas),
            Criteria::expr()->in('backupantenna', $decommissionnedAntennas),
            Criteria::expr()->in('mainantennaclimax', $decommissionnedAntennas),
            Criteria::expr()->in('backupantennaclimax', $decommissionnedAntennas)));
        
        $errorFrequencies = $this->objectManager->getRepository('Application\Entity\Frequency')->matching($criteria);
        
        if (count($errorFrequencies) > 0) {
            $return['error'][] = 'Attention, une ou plusieures fréquences ont des antennes qui ne sont plus en service.<br />Cette incohérence peut faire planter la page radio.';
        }
        
        $viewmodel->setVariables(array(
            'antennas' => $this->objectManager->getRepository('Application\Entity\Antenna')
                ->findAll(),
            'frequencies' => $this->objectManager->getRepository('Application\Entity\Frequency')
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
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormAntenna($id);
            $form = $datas['form'];
            $form->setData($post);
            
            $antenna = $datas['antenna'];
            
            if ($form->isValid()) {
                $antenna->setOrganisation($this->objectManager->getRepository('Application\Entity\Organisation')
                    ->find($post['organisation']));
                
                $this->objectManager->persist($antenna);
                $this->objectManager->flush();
                
                if ($antenna->isDecommissionned()) {
                    $this->objectManager->getRepository('Application\Entity\Event')->setReadOnly($antenna);
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
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $antenna = $this->objectManager->getRepository('Application\Entity\Antenna')->find($id);
            if ($antenna) {
                $this->objectManager->remove($antenna);
                $this->objectManager->flush();
            }
        }
        return new JsonModel();
    }

    private function getFormAntenna($id)
    {
        $antenna = new Antenna();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($antenna);
        $form->setHydrator(new DoctrineObject($this->objectManager))->setObject($antenna);
        
        $form->get('organisation')->setValueOptions($this->objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($id) {
            $antenna = $this->objectManager->getRepository('Application\Entity\Antenna')->find($id);
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
        $messages = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormFrequency($id);
            $form = $datas['form'];
            $form->setData($post);
            
            $frequency = $datas['frequency'];
            
            if ($form->isValid()) {
                // $antenna->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')->find($post['organisation']));
                
                $this->objectManager->persist($frequency);
                try {
                    $this->objectManager->flush();
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }

            }
        }


        $json = array(
            'id' => $frequency->getId(),
            'name' => $frequency->getValue()
        );
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function deletefrequencyAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $messages = array();
        if ($id) {
            $frequency = $this->objectManager->getRepository('Application\Entity\Frequency')->find($id);
            if ($frequency) {
                $this->objectManager->remove($frequency);
                try {
                    $this->objectManager->flush();
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
        $frequency = new Frequency();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($frequency);
        $form->setHydrator(new DoctrineObject($this->objectManager))->setObject($frequency);
        
        $form->get('mainantenna')->setValueOptions($this->objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('backupantenna')->setValueOptions($this->objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('mainantennaclimax')->setValueOptions($this->objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('backupantennaclimax')->setValueOptions($this->objectManager->getRepository('Application\Entity\Antenna')
            ->getAllAsArray());
        $form->get('organisation')->setValueOptions($this->objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        $form->get('backupfrequencies')->setValueOptions($this->objectManager->getRepository('Application\Entity\Frequency')
            ->getAllAsArray(array('decommissionned' => false)));
        
        $unsetsectors = $this->objectManager->getRepository('Application\Entity\Sector')->getUnsetSectorsAsArray();
        $form->get('defaultsector')->setValueOptions($unsetsectors);
        
        if ($id) {
            $frequency = $this->objectManager->getRepository('Application\Entity\Frequency')->find($id);
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
        $this->layout()->title = "Onglets > Radio";

        $viewmodel->setVariables(array(
            'sectorsgroups' => $this->objectManager->getRepository('Application\Entity\SectorGroup')
                ->findBy(array(), array(
                'position' => 'ASC'
            )),
            'antennas' => $this->objectManager->getRepository('Application\Entity\Antenna')
                ->findBy(array(), array(
                'name' => 'ASC'
            ))
        ));
        
        return $viewmodel;
    }

    public function groupdownAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sectorgroup = $this->objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($sectorgroup) {
                $sectorgroup->setPosition($sectorgroup->getPosition() + 1);
                $this->objectManager->persist($sectorgroup);
                try {
                    $this->objectManager->flush();
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
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sectorgroup = $this->objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($sectorgroup) {
                $sectorgroup->setPosition($sectorgroup->getPosition() - 1);
                $this->objectManager->persist($sectorgroup);
                try {
                    $this->objectManager->flush();
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
        $id = $this->params()->fromQuery('id', null);
        if ($id) {
            $sectorgroup = $this->objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
            if ($sectorgroup) {
                $sectorgroup->setDisplay(! $sectorgroup->isDisplay());
                $this->objectManager->persist($sectorgroup);
                try {
                    $this->objectManager->flush();
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
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $getform = $this->getFormAntennaModel($id);
            $form = $getform['form'];
            $form->setData($post);
            
            if ($form->isValid()) {
                $antenna = $getform['antenna'];
                $antenna->setModel($this->objectManager->getRepository('Application\Entity\PredefinedEvent')
                    ->find($form->get("models")
                    ->getValue()));
                $this->objectManager->persist($antenna);
                try {
                    $this->objectManager->flush();
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
        $datas = array();
        $form = null;
        if ($id) {
            $antenna = $this->objectManager->getRepository('Application\Entity\Antenna')->find($id);
            if ($antenna) {
                $datas['antenna'] = $antenna;
                $qb = $this->objectManager->createQueryBuilder();
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
