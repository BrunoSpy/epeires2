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
use Doctrine\Common\Collections\Criteria;
use Application\Entity\PredefinedEvent;
use Application\Entity\CustomFieldValue;
use Application\Form\CustomFieldset;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Controller\FormController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class ModelsController extends FormController
{

    public function indexAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Personnalisation > Modèles";
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
        $models = $objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria);
        
        $actions = array();
        $alerts = array();
        $files = array();
        foreach ($models as $model) {
            $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('parent', $model));
            $countactions = 0;
            $countalerts = 0;
            foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria) as $child) {
                if ($child->getCategory() instanceof \Application\Entity\ActionCategory) {
                    $countactions ++;
                } elseif ($child->getCategory() instanceof \Application\Entity\AlarmCategory) {
                    $countalerts ++;
                }
            }
            $files[$model->getId()] = count($model->getFiles());
            $alerts[$model->getId()] = $countalerts;
            $actions[$model->getId()] = $countactions;
        }
        
        $viewmodel->setVariables(array(
            'models' => $models,
            'actions' => $actions,
            'alerts' => $alerts,
            'files' => $files
        ));
        
        $return = array();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['errorMessages'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['successMessages'] = $this->flashMessenger()->getSuccessMessages();
        }
        
        $this->flashMessenger()->clearMessages();
        
        $viewmodel->setVariables(array(
            'messages' => $return
        ));
        
        return $viewmodel;
    }

    public function deleteAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($id) {
            $pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
            if ($pevent) {
                // cas particulier des antennes
                $antenna = $objectManager->getRepository('Application\Entity\Antenna')->findOneBy(array(
                    'model' => $pevent
                ));
                if ($antenna) {
                    $antenna->setModel(null);
                }
                $objectManager->remove($pevent);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Modèle correctement supprimé.";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        $redirect = $this->params()->fromQuery('redirect', false);
        if ($redirect) {
            return $this->redirect()->toRoute('administration', array(
                'controller' => 'models'
            ));
        } else {
            return new JsonModel($messages);
        }
    }

    public function upAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($id) {
            $pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
            if ($pevent) {
                // on recherche le modèle précédent
                $place = $pevent->getPlace();
                $criteria = Criteria::create()->where(Criteria::expr()->lt('place', $place))
                    ->orderBy(array(
                    'place' => Criteria::DESC
                ))
                    ->setMaxResults(1);
                $previous = $objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria);
                if (count($previous) === 0) {
                    // pas de résultat, en toute logique on ne devrait pas être dans ce cas
                    $pevent->setPlace(0);
                } else {
                    $prev = $previous->first();
                    $pevent->setPlace($prev->getPlace());
                }
                $objectManager->persist($pevent);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Élément correctement modifié.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver l'élément";
            }
        }
        return new JsonModel($messages);
    }

    public function downAction()
    {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($id) {
            $pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
            if ($pevent) {
                // on recherche le modèle suivent
                $place = $pevent->getPlace();
                $criteria = Criteria::create()->where(Criteria::expr()->gt('place', $place))
                    ->orderBy(array(
                    'place' => Criteria::ASC
                ))
                    ->setMaxResults(1);
                $previous = $objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria);
                if (count($previous) === 0) {
                    // pas de résultat, en toute logique on ne devrait pas être dans ce cas
                    $pevent->setPlace(- 1);
                } else {
                    $prev = $previous->first();
                    $pevent->setPlace($prev->getPlace());
                }
                $objectManager->persist($pevent);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Élément correctement modifié.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver l'élément";
            }
        }
        return new JsonModel($messages);
    }

    public function saveAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $messages = array();
        
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $catid = $this->params()->fromQuery('catid', null);
            
            if (isset($post['custom_fields']) && isset($post['custom_fields']['category_id'])) {
                $catid = $post['custom_fields']['category_id'];
            }
            
            $datas = $this->getForm($id, null, $catid, $post['organisation']);
            
            $form = $datas['form'];
            $pevent = $datas['pevent'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            
            // remove required inputfilter on custom fields (default to true for select elements...)
            foreach ($form->getInputFilter()->getInputs() as $input) {
                if ($input instanceof \Zend\InputFilter\InputFilter) {
                    foreach ($input->getInputs() as $i) {
                        $i->setRequired(false);
                    }
                }
            }
            
            if ($form->isValid()) {
                // category, may be disable
                if ($post['category']) {
                    $category = $post['category'];
                    $pevent->setCategory($objectManager->getRepository('Application\Entity\Category')
                        ->find($post['category']));
                } else 
                    if ($pevent->getCategory()) {
                        $category = $pevent->getCategory()->getId();
                    } else { // last chance cat id passed by query
                        $category = $catid;
                        $pevent->setCategory($objectManager->getRepository('Application\Entity\Category')
                            ->find($catid));
                    }
                if (! $id) { // if modification : link to parent and calculate position
                             // link to parent
                    if (isset($post['parent'])) {
                        $pevent->setParent($objectManager->getRepository('Application\Entity\PredefinedEvent')
                            ->find($post['parent']));
                        // calculate order (order by parent)
                        $qb = $objectManager->createQueryBuilder();
                        $qb->select('MAX(f.place)')
                            ->from('Application\Entity\PredefinedEvent', 'f')
                            ->where('f.parent = ' . $post['parent']);
                        $result = $qb->getQuery()->getResult();
                        if ($result[0][1]) {
                            $pevent->setPlace($result[0][1] + 1);
                        } else {
                            $pevent->setPlace(1);
                        }
                    } else {
                        // no parent => model => order by category
                        $qb = $objectManager->createQueryBuilder();
                        $qb->select('MAX(f.place)')
                            ->from('Application\Entity\PredefinedEvent', 'f')
                            ->where('f.category = ' . $category);
                        $result = $qb->getQuery()->getResult();
                        if ($result[0][1]) {
                            $pevent->setPlace($result[0][1] + 1);
                        } else {
                            $pevent->setPlace(1);
                        }
                    }
                }
                $pevent->setImpact($objectManager->getRepository('Application\Entity\Impact')
                    ->find($post['impact']));
                
                // alarms
                if (isset($post['alarm']) && is_array($post['alarm'])) {
                    foreach ($post['alarm'] as $key => $alarmpost) {
                        $alarm = new PredefinedEvent();
                        $alarm->setCategory($objectManager->getRepository('Application\Entity\AlarmCategory')
                            ->findAll()[0]);
                        $alarm->setOrganisation($pevent->getOrganisation());
                        $alarm->setParent($pevent);
                        $alarm->setListable(false);
                        $alarm->setSearchable(false);
                        $alarm->setPunctual(true);
                        $alarm->setImpact($objectManager->getRepository('Application\Entity\Impact')
                            ->find(5));
                        $name = new CustomFieldValue();
                        $name->setCustomField($alarm->getCategory()
                            ->getNamefield());
                        $name->setValue($alarmpost['name']);
                        $name->setEvent($alarm);
                        $alarm->addCustomFieldValue($name);
                        $comment = new CustomFieldValue();
                        $comment->setCustomField($alarm->getCategory()
                            ->getTextfield());
                        $comment->setValue($alarmpost['comment']);
                        $comment->setEvent($alarm);
                        $alarm->addCustomFieldValue($comment);
                        $deltabegin = new CustomFieldValue();
                        $deltabegin->setCustomField($alarm->getCategory()
                            ->getDeltaBeginField());
                        $deltabegin->setValue(preg_replace('/\s+/', '', $alarmpost['deltabegin']));
                        $deltabegin->setEvent($alarm);
                        $alarm->addCustomFieldValue($deltabegin);
                        $deltaend = new CustomFieldValue();
                        $deltaend->setCustomField($alarm->getCategory()
                            ->getDeltaEndField());
                        $deltaend->setValue(preg_replace('/\s+/', '', $alarmpost['deltaend']));
                        $deltaend->setEvent($alarm);
                        $alarm->addCustomFieldValue($deltaend);
                        $objectManager->persist($name);
                        $objectManager->persist($comment);
                        $objectManager->persist($deltabegin);
                        $objectManager->persist($deltaend);
                        $objectManager->persist($alarm);
                    }
                }
                
                // fichiers
                if (isset($post['fichiers']) && is_array($post['fichiers'])) {
                    foreach ($post['fichiers'] as $key => $value) {
                        $file = $objectManager->getRepository("Application\Entity\File")->find($key);
                        if ($file) {
                            $file->addEvent($pevent);
                            $objectManager->persist($file);
                        }
                    }
                }
                
                $objectManager->persist($pevent);
                // predefined custom field values
                if (isset($post['custom_fields'])) {
                    foreach ($post['custom_fields'] as $key => $value) {
                        $customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array(
                            'id' => $key
                        ));
                        if ($customfield) {
                            $customfieldvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array(
                                'customfield' => $customfield->getId(),
                                'event' => $id
                            ));
                            if (! $customfieldvalue) {
                                $customfieldvalue = new CustomFieldValue();
                                $customfieldvalue->setEvent($pevent);
                                $customfieldvalue->setCustomField($customfield);
                                $pevent->addCustomFieldValue($customfieldvalue);
                            }
                            if (is_array($value)) { // transformation des champs multiples
                                $temp = "";
                                foreach ($value as $v) {
                                    $temp .= (string) $v . "\r";
                                }
                                $value = trim($temp);
                            }
                            $customfieldvalue->setValue($value);
                            $objectManager->persist($customfieldvalue);
                        }
                    }
                }
                try {
                    $start = microtime(true);
                    $objectManager->flush();
                    error_log(microtime(true) - $start);
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
                $this->flashMessenger()->addSuccessMessage("Modèle " . $pevent->getName() . " enregistré.");
                $this->processFormMessages($form->getMessages());
            } else {
                // traitement des erreurs de validation
                $pevent = null;
                $this->processFormMessages($form->getMessages(), $messages);
            }
        }
        
        $json = array();
        
        if ($pevent) {
            $json['id'] = $pevent->getId();
            $json['name'] = $this->getServiceLocator()
                ->get('EventService')
                ->getName($pevent);
            $json['impactstyle'] = $pevent->getImpact()->getStyle();
            $json['impactname'] = $pevent->getImpact()->getName();
            if ($pevent->getParent()) {
                $json['parentid'] = $pevent->getParent()->getId();
            }
        }
        
        $json['messages'] = $messages;
        
        return new JsonModel($json);
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        $action = $this->params()->fromQuery('action', false);
        $parentid = $this->params()->fromQuery('parentid', null);
        $catid = $this->params()->fromQuery('catid', null);
        
        if ($id) { // fiche reflexe et fichiers
            $childs = $objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                'parent' => $id
            ), array(
                'place' => 'asc'
            ));
            $viewmodel->setVariables(array(
                'childs' => $childs
            ));
            
            $files = $objectManager->getRepository('Application\Entity\PredefinedEvent')
                ->find($id)
                ->getFiles();
            $viewmodel->setVariable('files', $files);
        }
        
        $getform = $this->getForm($id, $parentid, $catid, null, $action);
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
            'action' => $action
        ));
        return $viewmodel;
    }

    private function getForm($id = null, $parentid = null, $catid = null, $orgid = null, $action = false)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $pevent = new PredefinedEvent();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($pevent);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($pevent);
        
        $form->get('impact')->setValueOptions($objectManager->getRepository('Application\Entity\Impact')
            ->getAllAsArray());
        
        $form->get('category')->setValueOptions($objectManager->getRepository('Application\Entity\Category')
            ->getAllAsArray());
        
        $form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\PredefinedEvent')
            ->getRootsAsArray());
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if (! $action) {
            $form->get('name')->setAttribute('required', 'required');
        }
        
        if ($catid || $action) {
            if ($catid) {
                // set category
                $form->get('category')->setAttribute('value', $catid);
                // add custom fields input
                $form->add(new CustomFieldset($this->getServiceLocator(), $catid));
            } else {
                $catactions = $objectManager->getRepository('Application\Entity\ActionCategory')->findAll();
                // TODO rendre paramétrable
                $cataction = $catactions[0];
                // add custom fields input
                $form->get('category')->setAttribute('value', $cataction->getId());
                $form->add(new CustomFieldset($this->getServiceLocator(), $cataction->getId(), !$action));
                $colorfield = $form->get('custom_fields')->get($cataction->getColorfield()
                    ->getId());
                $colorfield->setAttribute('class', 'pick-a-color');
            }
            // disable category modification
            $form->get('category')->setAttribute('disabled', 'disabled');
            // and change validator
            $form->getInputFilter()
                ->get('category')
                ->setRequired(false);
        }
        
        if ($orgid) {
            $form->get('zonefilters')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
                ->getAllAsArray($objectManager->getRepository('Application\Entity\Organisation')
                ->find($orgid)));
        }
        
        if ($id) { // modification d'un evt
            $pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
            if ($pevent) {
                $form->get('zonefilters')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
                    ->getAllAsArray($pevent->getOrganisation()));
                $form->bind($pevent);
                // disable category modification
                $form->get('category')->setAttribute('disabled', 'disabled');
                // and change validator
                $form->getInputFilter()
                    ->get('category')
                    ->setRequired(false);
                $form->setData($pevent->getArrayCopy());
                // custom field values
                $customfields = $objectManager->getRepository('Application\Entity\CustomField')->findBy(array(
                    'category' => $pevent->getCategory()
                        ->getId()
                ));
                if (count($customfields) > 0) {
                    if (! ($catid || $action)) { // customfieldset already added
                        $form->add(new CustomFieldset($this->getServiceLocator(), $pevent->getCategory()
                            ->getId(), !$action));
                    }
                    foreach ($customfields as $customfield) {
                        $customfieldvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array(
                            'event' => $pevent->getId(),
                            'customfield' => $customfield->getId()
                        ));
                        if ($customfieldvalue) {
                            $form->get('custom_fields')
                                ->get($customfield->getId())
                                ->setAttribute('value', $customfieldvalue->getValue());
                        }
                    }

                }
            }
        }
        
        if ($parentid) { // action reflexe
            $form->get('parent')->setAttribute('value', $parentid);
            $parent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($parentid);
            if ($parent) {
                $form->get('organisation')->setAttribute('value', $parent->getOrganisation()
                    ->getId());
            } else {
                // TODO throw exception
            }
        }
        
        return array(
            'form' => $form,
            'pevent' => $pevent
        );
    }

    public function listAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $id = $this->params()->fromQuery('id', null); // categoryid
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        if ($id) {
            $models = $objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                'category' => $id,
                'parent' => null
            ), array(
                'place' => 'ASC'
            ));
            $viewmodel->setVariables(array(
                'models' => $models,
                'catid' => $id
            ));
        }
        return $viewmodel;
    }

    public function customfieldsAction()
    {
        $request = $this->getRequest();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null); // categoryid
        
        $pevent = new PredefinedEvent();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($pevent);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($pevent);
        $form->add(new CustomFieldset($this->getServiceLocator(), $id, true));
        $viewmodel->setVariables(array(
            'form' => $form
        ));
        return $viewmodel;
    }

    public function getzonefiltersAction()
    {
        $orgid = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $org = $objectManager->getRepository('Application\Entity\Organisation')->find($orgid);
        $zonefilters = null;
        if ($org) {
            $zonefilters = $objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray($org);
        } else {
            // TODO
        }
        return new JsonModel($zonefilters);
    }

    public function deletefileAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $fileid = $this->params()->fromQuery('id', null);
        $modelid = $this->params()->fromQuery('modelid', null);
        $messages = array();
        
        if ($fileid) {
            $file = $objectManager->getRepository('Application\Entity\File')->find($fileid);
            if ($modelid && $file) {
                $model = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($modelid);
                if ($model) {
                    $file->removeEvent($model);
                    $objectManager->persist($file);
                } else {
                    $messages['error'][] = "Impossible d'enlever le fichier de l'évènement";
                }
            } else {
                if ($file) {
                    $objectManager->remove($file);
                    $messages['success'][] = "Fichier correctement ajouté";
                } else {
                    $messages['error'][] = "Impossible de supprimer le fichier : aucun fichier correspondant.";
                }
            }
            try {
                $objectManager->flush();
            } catch (\Exception $ex) {
                $messages['error'][] = $ex->getMessage();
            }
        } else {
            $messages['error'][] = "Impossible de supprimer le fichier : aucun paramètre trouvé.";
        }
        return new JsonModel($messages);
    }

    public function validatealarmAction()
    {
        $json = array();
        $messages = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            $datas = $this->getForm();
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            if ($form->isValid()) {
                $event = $form->getData();
                $alarm = array();
                $alarm['name'] = $post['custom_fields'][$event->getCategory()
                    ->getFieldname()
                    ->getId()];
                $alarm['comment'] = $post['custom_fields'][$event->getCategory()
                    ->getTextfield()
                    ->getId()];
                $alarm['deltabegin'] = $post['custom_fields'][$event->getCategory()
                    ->getDeltaBeginField()
                    ->getId()];
                $alarm['deltaend'] = $post['custom_fields'][$event->getCategory()
                    ->getDeltaEndField()
                    ->getId()];
                $json['alarm'] = $alarm;
            } else {
                $this->processFormMessages($form->getMessages(), $messages);
            }
        }
        $json['messages'] = $messages;
        return new \Zend\View\Model\JsonModel($json);
    }

    public function formalarmAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $alarmid = $this->params()->fromQuery('id', null);
        
        $getform = $this->getAlarmForm($alarmid);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'alarmid' => $alarmid
        ));
        return $viewmodel;
    }

    private function getAlarmForm($alarmid = null)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $alarm = new PredefinedEvent();
        
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($alarm);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($alarm);
        
        $alarmcat = $objectManager->getRepository('Application\Entity\AlarmCategory')->findAll()[0]; // TODO
        
        $form->add(new CustomFieldset($this->getServiceLocator(), $alarmcat->getId()));
        
        if ($alarmid) {
            $alarm = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($alarmid);
            if ($alarm) {
                $form->bind($alarm);
                $form->setData($alarm->getArrayCopy());
            }
        } else {
            // alarm : punctual, impact : info, organisation, category : alarm, status : open (closed when aknowledged)
            // all these information are just here to validate form
            $form->get('impact')->setValue(5);
            $form->get('punctual')->setValue(true);
            $form->get('category')->setValue($alarmcat->getId());
            if ($this->zfcUserAuthentication()->hasIdentity()) {
                $form->get('organisation')->setValue($this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getOrganisation()
                    ->getId());
            } else {
                throw new \ZfcRbac\Exception\UnauthorizedException();
            }
        }
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Ajouter',
                'class' => 'btn btn-primary'
            )
        ));
        
        return array(
            'form' => $form,
            'alarm' => $alarm
        );
    }

    public function deletealarmAction()
    {
        $alarmid = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $messages = array();
        
        if ($alarmid) {
            $alarm = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($alarmid);
            if ($alarm) {
                $objectManager->remove($alarm);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Mémo supprimé";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Aucun mémo correspondant trouvé";
            }
        } else {
            $messages['error'][] = "Aucun mémo à supprimer";
        }
        return new JsonModel($messages);
    }

    public function listableAction()
    {
        $messages = array();
        $json = array();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $modelid = $this->params()->fromQuery('id', null);
        $listable = $this->params()->fromQuery('listable', null);
        
        if ($modelid != null && $listable != null) {
            $model = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($modelid);
            if ($model) {
                $model->setListable($listable === 'true');
                $objectManager->persist($model);
                try {
                    $objectManager->flush();
                    $json['listable'] = $model->isListable();
                    $messages['success'][] = "Modèle correctement modifié.";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver le modèle à modifier";
            }
        } else {
            $messages['error'][] = "Impossible de modifier le modèle : paramètres incorrects.";
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }
}
