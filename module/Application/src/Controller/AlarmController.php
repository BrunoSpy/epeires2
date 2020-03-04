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
namespace Application\Controller;

use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Form\CustomFieldset;
use Application\Entity\Event;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class AlarmController extends FormController
{

    private $entityManager;
    private $eventservice;
    private $customfieldservice;

    public function __construct(EntityManager $entityManager,
                                EventService $eventService,
                                CustomFieldService $customfieldService)
    {
        $this->entityManager = $entityManager;
        $this->eventservice = $eventService;
        $this->customfieldservice = $customfieldService;
    }

    public function saveAction()
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
                $offset = date("Z");
                $startdate = clone $event->getStartDate();
                $startdate->setTimezone(new \DateTimeZone("UTC"));
                $startdate->add(new \DateInterval("PT" . $offset . "S"));
                $event->setStartDate($startdate);
                $alarm = array();
                $alarm['id'] = $event->getId();
                $alarm['datetime'] = $event->getStartDate()->format(DATE_RFC2822);
                $alarm['name'] = htmlspecialchars($post['custom_fields'][$event->getCategory()
                    ->getFieldname()
                    ->getId()]);
                $alarm['comment'] = htmlspecialchars($post['custom_fields'][$event->getCategory()
                    ->getTextfield()
                    ->getId()]);
                $alarm['deltabegin'] = $post['custom_fields'][$event->getCategory()
                    ->getDeltaBeginField()
                    ->getId()];
                $alarm['deltaend'] = $post['custom_fields'][$event->getCategory()
                    ->getDeltaEndField()
                    ->getId()];
                $json['alarm'] = $alarm;
                if ($event->getId() > 0) {
                    foreach ($post['custom_fields'] as $key => $value) {
                        // génération des customvalues si un customfield dont le nom est $key est trouvé
                        $customfield = $this->entityManager->getRepository('Application\Entity\CustomField')->findOneBy(array(
                            'id' => $key
                        ));
                        if ($customfield) {
                            $customvalue = $this->entityManager->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array(
                                'customfield' => $customfield->getId(),
                                'event' => $event->getId()
                            ));
                            if (! $customvalue) {
                                $customvalue = new \Application\Entity\CustomFieldValue();
                                $customvalue->setEvent($event);
                                $customvalue->setCustomField($customfield);
                                $event->addCustomFieldValue($customvalue);
                            }
                            $customvalue->setValue($value);
                            $this->entityManager->persist($customvalue);
                        }
                    }
                    // mod -> save it now
                    $this->entityManager->persist($event);
                    $this->entityManager->flush();
                } else {
                    // new : do nothing
                }
            } else {
                $this->processFormMessages($form->getMessages(), $messages);
            }
        }
        $json['messages'] = $messages;
        return new \Laminas\View\Model\JsonModel($json);
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $alarmid = $this->params()->fromQuery('id', null);
        
        $getform = $this->getForm($alarmid);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'alarmid' => $alarmid
        ));
        return $viewmodel;
    }

    private function getForm($alarmid = null)
    {
        $alarm = new Event();
        
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($alarm);
        $form->setHydrator(new DoctrineObject($this->entityManager))->setObject($alarm);
        
        $alarmcat = $this->entityManager->getRepository('Application\Entity\AlarmCategory')->findAll()[0]; // TODO
        
        $form->add(new CustomFieldset($this->entityManager, $this->customfieldservice, $alarmcat->getId()));
        $form->get('scheduled')->setValue(false);
        if ($alarmid) {
            $alarm = $this->entityManager->getRepository('Application\Entity\Event')->find($alarmid);
            if ($alarm) {
                // custom fields values
                foreach ($this->entityManager->getRepository('Application\Entity\CustomField')->findBy(array(
                    'category' => $alarm->getCategory()
                        ->getId()
                )) as $customfield) {
                    $customfieldvalue = $this->entityManager->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array(
                        'event' => $alarm->getId(),
                        'customfield' => $customfield->getId()
                    ));
                    if ($customfieldvalue) {
                        $form->get('custom_fields')
                            ->get($customfield->getId())
                            ->setAttribute('value', $customfieldvalue->getValue());
                    }
                }
                $form->bind($alarm);
                $form->setData($alarm->getArrayCopy());
            }
            $form->add(array(
                'name' => 'submit',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Modifier',
                    'class' => 'btn btn-primary'
                )
            ));
        } else {
            // alarm : punctual, impact : info, organisation, category : alarm, status : open (closed when aknowledged)
            // all these information are just here to validate form
            $form->get('impact')->setValue(5);
            $form->get('punctual')->setValue(true);
            $form->get('category')->setValue($alarmcat->getId());
            $form->get('status')->setValue($this->entityManager->getRepository('Application\Entity\Status')
                ->findOneBy(array(
                'open' => true,
                'defaut' => true
            ))
                ->getId());
            if ($this->zfcUserAuthentication()->hasIdentity()) {
                $form->get('organisation')->setValue($this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getOrganisation()
                    ->getId());
            } else {
                throw new \ZfcRbac\Exception\UnauthorizedException();
            }
            
            $form->add(array(
                'name' => 'submit',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Ajouter',
                    'class' => 'btn btn-primary'
                )
            ));
        }
        return array(
            'form' => $form,
            'alarm' => $alarm
        );
    }

    public function deleteAction()
    {
        $alarmid = $this->params()->fromQuery('id', null);
        $messages = array();
        
        if ($alarmid) {
            $alarm = $this->entityManager->getRepository('Application\Entity\Event')->find($alarmid);
            if ($alarm) {
                $this->entityManager->remove($alarm);
                try {
                    $this->entityManager->flush();
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

    /*
     * Mémos futurs non acquittés.
     * Seuls les mémos de l'organisation de l'utilisateur sont envoyés.
     * Si lastupdate contient une date valide, envoit les mémos modifiés depuis lastupdate, y compris ceux acquittés
     * Si deltaend existe mais pas de parent, ou avec un parent sans date de fin : l'alarme n'est pas renvoyée
     * Dans tous les cas : nécessite d'être identifié.
     */
    public function getalarmsAction()
    {
        $formatter = \IntlDateFormatter::create(
            \Locale::getDefault(), 
            \IntlDateFormatter::FULL, 
            \IntlDateFormatter::FULL, 
            'UTC', 
            \IntlDateFormatter::GREGORIAN, 
            'HH:mm'
        );
        $alarms = array();
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $organisation = $this->zfcUserAuthentication()
                ->getIdentity()
                ->getOrganisation()
                ->getId();
            $lastupdate = $this->params()->fromQuery('lastupdate', null);
            
            $userroles = array();
            foreach ($this->zfcUserAuthentication()
                ->getIdentity()
                ->getRoles() as $role) {
                $userroles[] = $role->getId();
            }
            $qbEvents = $this->entityManager->createQueryBuilder();
            $qbEvents->select(array(
                'e',
                'cat',
                'roles'
            ))
                ->from('Application\Entity\Event', 'e')
                ->innerJoin('e.category', 'cat')
                ->innerJoin('cat.readroles', 'roles')
                ->andWhere($qbEvents->expr()
                ->eq('e.organisation', $organisation))
                ->andWhere('cat INSTANCE OF Application\Entity\AlarmCategory')
                ->andWhere($qbEvents->expr()
                ->in('e.status', '?2'))
                ->
            // statut alarme
            andWhere($qbEvents->expr()
                ->in('roles.id', '?3'));
            if ($lastupdate && $lastupdate != 'undefined') {
                $from = new \DateTime($lastupdate);
                $from->setTimezone(new \DateTimeZone("UTC"));
                // uniquement les alarmes créés et modifiées à partir de lastupdate
                $qbEvents->andWhere($qbEvents->expr()
                    ->gte('e.last_modified_on', '?1'))
                    ->setParameters(array(
                    2 => array(
                        1,
                        2,
                        3,
                        4,
                        5
                    ),
                    1 => $from->format('Y-m-d H:i:s'),
                    3 => $userroles
                ));
            } else {
                $now = new \DateTime('NOW');
                $now->setTimezone(new \DateTimeZone("UTC"));
                $interval = new \DateInterval("PT60M");
                $interval->invert = 1;
                $now->add($interval);
                // toutes les alarmes non acquittées vielles de moins d'une heure
                // afin de ne pas perdre les alarmes non acquittées en cas de refresh
                $qbEvents->andWhere($qbEvents->expr()
                    ->gte('e.startdate', '?1'))
                    ->
                // date de début dans le futur
                setParameters(array(
                    1 => $now->format('Y-m-d H:i:s'),
                    2 => array(
                        1,
                        2
                    ),
                    3 => $userroles
                ));
            }
            $result = $qbEvents->getQuery()->getResult();
            foreach ($result as $alarm) {
                $alarm = $this->entityManager->getRepository('Application\Entity\Event')->find($alarm->getId());
                if ($alarm->getParent()) { // les alarmes ont forcément un parent
                    $deltaend = "";
                    $alarmcomment = "";
                    foreach ($alarm->getCustomFieldsValues() as $value) {
                        if ($value->getCustomField()->getId() === $alarm->getCategory()
                            ->getDeltaEndField()
                            ->getId()) {
                            $deltaend = $value->getValue();
                        } else 
                            if ($value->getCustomField()->getId() == $alarm->getCategory()
                                ->getTextfield()
                                ->getId()) {
                                $alarmcomment = nl2br($value->getValue());
                            }
                    }
                    if (strlen(trim($deltaend)) > 0 && ! $alarm->getParent()->getEnddate()) {
                        // do nothing : start date inaccurate
                    } else {
                        $startdate = $alarm->getStartDate();
                        $alarmjson = array();
                        $alarmjson['id'] = $alarm->getId();
                        $alarmjson['datetime'] = $startdate->format(DATE_RFC2822);
                        $alarmjson['status'] = $alarm->getStatus()->getId();
                        $parentname = $this->eventservice->getName($alarm->getParent());
                        $alarmname = $this->eventservice->getName($alarm);
                        $alarmjson['text'] = "<div id=\"alarmnoty-" . $alarm->getId() . "\" class=\"noty_big\"><b>" . $formatter->format($alarm->getStartDate()) . " : Mémo</b> pour <b>" . $parentname . "</b><br />" . $alarmname . (strlen($alarmcomment) > 0 ? " : <br />" . $alarmcomment : "");
                        
                        $alarms[] = $alarmjson;
                    }
                }
            }
        }
        if (empty($alarms)) {
            $this->getResponse()->setStatusCode(304);
            return new JsonModel();
        }
        $this->getResponse()
            ->getHeaders()
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');
        
        return new JsonModel($alarms);
    }

    /**
     * Change le statut d'un mémo à Terminé
     * Nécessite d'être identifié et d'avoir les droits events.write
     */
    public function confirmAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            if ($this->isGranted('events.write')) {
                $id = $this->params()->fromQuery('id', null);
                $messages = array();
                if ($id) {
                    $alarm = $this->entityManager->getRepository('Application\Entity\Event')->find($id);
                    if ($alarm) {
                        $status = $this->entityManager->getRepository('Application\Entity\Status')->findOneBy(array(
                            'open' => false,
                            'defaut' => true
                        ));
                        // ne pas enregistrer si pas de changement
                        if ($alarm->getStatus()->getId() != $status->getId()) {
                            $alarm->setStatus($status);
                            $this->entityManager->persist($alarm);
                            try {
                                $this->entityManager->flush();
                                $messages['success'][] = "Mémo acquitté";
                            } catch (\Exception $e) {
                                $messages['error'][] = $e->getMessage();
                            }
                        }
                    } else {
                        $messages['error'][] = "Aucun mémo trouvé";
                    }
                } else {
                    $messages['error'][] = "Argument incorrect";
                }
            } else {
                $messages['error'][] = "Droits insuffisants pour acquitter le mémo";
            }
        } else {
            $messages['error'][] = "Utilisateur non identifié, modification impossible";
        }
        return new JsonModel($messages);
    }
}
