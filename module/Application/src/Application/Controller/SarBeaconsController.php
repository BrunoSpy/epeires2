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

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;

use Doctrine\ORM\EntityManager;
use DOMPDFModule\View\Model\PdfModel;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;

use Core\Controller\AbstractEntityManagerAwareController;

use Application\Entity\Organisation;
use Application\Entity\Event;
use Application\Entity\EventUpdate;
use Application\Entity\CustomFieldValue;

use Application\Entity\InterrogationPlanCategory;
use Application\Entity\AlertCategory;
use Application\Entity\FieldCategory;
/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractEntityManagerAwareController
{
    const ACCES_REQUIRED = "Droits d'accès insuffisants";

    private $em, $viewpdfrenderer, $config;

    public function __construct(EntityManager $em, $viewpdfrenderer, $config)
    {
        parent::__construct($em);
        $this->em = $this->getEntityManager();

        $this->viewpdfrenderer = $viewpdfrenderer;
        $this->config = $config;
    }


    public function indexAction()
    {
        if (!$this->authSarBeacons('read')) {
            echo self::ACCES_REQUIRED;
            return new JsonModel();
        };

        $cats = [];
        foreach ($this->em->getRepository(InterrogationPlanCategory::class)->findAll() as $cat) {
            $cats[] = $cat->getId();
        }
 
        $alertcats = [];
        foreach ($this->em->getRepository(AlertCategory::class)->findAll() as $cat) {
            $alertcats[] = $cat->getId();
        }

        $fieldscats = [];
        foreach ($this->em->getRepository(FieldCategory::class)->findAll() as $cat) {
            $fieldscats[] = $cat->getId();
        }

        return (new ViewModel())
            ->setVariables([
                'cats' => $cats,
                'fieldcats' => $fieldscats,
                'alertcats' => $alertcats
            ]);
    }

    public function getnbcurrentipAction() 
    {
        return new JsonModel([
            'nbip' => count($this->em->getRepository(Event::class)->getCurrentIntPlanEvents())
        ]);
    }

    public function getnbendedipAction() 
    {
        return new JsonModel([
            'nbip' => count($this->em->getRepository(Event::class)->getEndedIntPlanEvents())
        ]);
    }

    public function getArrayCopy($ip) 
    {
        $ev = [
            'id' => $ip->getId(),
            'pdffilepath' => 'data/interrogation-plans/'.$ip->getId(),
            'pdffilename' => $ip->getId(),
            'start_date' => $ip->getStartDate(),
        ];

        foreach ($ip->getCustomFieldsValues() as $value) 
        {
            $namefield = $value->getCustomField()->getName();
            $valuefield = $value->getValue();
            $ev[$namefield] = $valuefield;
            if($namefield == 'Alerte') 
            {
                if($valuefield > 0) {
                    $ev[$namefield] = [];
                    $altEv = $this->em->getRepository(Event::class)->findOneBy(['id' => $valuefield]);
                    foreach ($altEv->getCustomFieldsValues() as $altvalue) {
                        $ev[$namefield][$altvalue->getCustomField()->getName()] = $altvalue->getValue();
                    } 
                }  
            }
        }

        foreach ($ip->getChildren() as $fieldEvent)
        {
            $field = [
                'idevent' => $fieldEvent->getId(),
                'start_date' => $fieldEvent->getStartDate(),
                'updates' => []
            ];

            foreach ($fieldEvent->getUpdates() as $update) {
                $field['updates'][] = [
                    'text' => $update->getText(),
                    'created_on' => $update->getCreatedOn()
                ];
            }

            foreach ($fieldEvent->getCustomFieldsValues() as $value) 
            {
                $namefield = $value->getCustomField()->getName();
                $valuefield = $value->getValue();
                $field[$namefield] = $valuefield;
            }
            $ev['fields'][] = $field;
        }
        return $ev;
    }

    private function getAlertIdFromIp($ip) 
    {
        $alertid = null;
        if (is_a($ip, Event::class)) 
        {
            $cat = $ip->getCategory();
            foreach ($ip->getCustomFieldsValues() as $customfieldvalue) 
            {
                if ($customfieldvalue->getCustomField()->getId() == $cat->getAlertfield()->getId())
                {
                    $alertid = $customfieldvalue->getValue();        
                }
            }            
        }
        return $alertid;
    } 

    public function showAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];
            
        $ip = $this->em->getRepository(Event::class)->find($id);  

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'ip' => $this->getArrayCopy($ip)
            ]);       
    }

    public function getipAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];
        $ip = null;

        if ($id > 0) { 
            $ip = $this->em->getRepository(Event::class)->find($id); 
            $ip = $this->getArrayCopy($ip);
        }

        return new JsonModel([
            'ip' => $ip
        ]); 

        // $lat = null;
        // $lon = null;
        // $fields = null;

        //     $cat = $ip->getCategory();  

        //     foreach ($ip->getCustomFieldsValues() as $customfieldvalue) 
        //     {
        //         switch ($customfieldvalue->getCustomField()->getId()) 
        //         {
        //             case $cat->getLatField()->getId() :
        //                 $lat = $customfieldvalue->getValue();
        //                 break;
        //             case $cat->getLongField()->getId() :
        //                 $lon = $customfieldvalue->getValue();
        //                 break;
        //         }
        //     }
        //     $fields = [];
        //     foreach ($ip->getChildren() as $fieldEvent)
        //     {
        //         $field = [
        //             'idevent' => $fieldEvent->getId(),
        //             'start_date' => $fieldEvent->getStartDate(),
        //             'updates' => []
        //         ];

        //         foreach ($fieldEvent->getUpdates() as $update) {
        //             $field['updates'][] = [
        //                 'text' => $update->getText(),
        //                 'created_on' => $update->getCreatedOn()
        //             ];
        //         }
        //         $codefield = $fieldEvent->getCategory()->getCodeField()->getId();
        //         foreach ($fieldEvent->getCustomFieldsValues() as $value) 
        //         {
        //             if ($value->getCustomField()->getId() == $codefield) $field['code'] = $value->getValue();
        //         }
        //         $fields[] = $field;
        //     }

        // }

        // return new JsonModel([
        //     'lat' => $lat,
        //     'lon' => $lon,
        //     'fields' => (count($fields) > 0) ? $fields : null
        // ]);        
    }

    public function startAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $msgType = 'error';

        $post = $this->getRequest()->getPost();
        // print_r($post);
        $id = (int) $post['id'];
        // modification
        if ($id > 0) 
        {
            $idEvent = $id;
            $ip = $this->em->getRepository(Event::class)->find($id);
            $cat = $ip->getCategory();
            // $typefield = $ip->getCustomField($cat->getTypeField());
            // $type

            foreach ($ip->getCustomFieldsValues() as $customfieldvalue) 
            {
                switch ($customfieldvalue->getCustomField()->getId()) 
                {
                    case $cat->getTypeField()->getId() :
                        $customfieldvalue->setValue($post['type']);
                        break;
                    case $cat->getLatField()->getId() :
                        $customfieldvalue->setValue($post['lat']);
                        break;
                    case $cat->getLongField()->getId() :
                        $customfieldvalue->setValue($post['lon']);
                        break;
                    case $cat->getAlertField()->getId() :
                        $alt = $this->em->getRepository(Event::class)->find($customfieldvalue->getValue());
                        $altcat = $alt->getCategory();
                        foreach ($alt->getCustomFieldsValues() as $altcustomfieldvalue) 
                        {
                            switch ($altcustomfieldvalue->getCustomField()->getId()) 
                            {
                                case $altcat->getTypeField()->getId() :
                                    $altcustomfieldvalue->setValue($post['typealerte']);
                                    break;
                                case $altcat->getCauseField()->getId() :
                                    $altcustomfieldvalue->setValue($post['cause']);
                                    break;
                            }
                            $this->em->persist($altcustomfieldvalue);
                        }
                        break;
                }
                $this->em->persist($customfieldvalue);
            }

            try 
            {
                $this->em->flush();
                // $idEvent = $event->getId();
                $msgType = 'success';
                $msg = "Plan d'interrogation modifié.";
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        // création
        } 
        else 
        {
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));
            // crétation de l'evenement d'alerte
            $event = new Event();
            $event->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
            $event->setStartdate($now);
            $event->setImpact($this->em->getRepository('Application\Entity\Impact')->find('3'));
            $event->setPunctual(false);
            $event->setOrganisation($this->zfcUserAuthentication()
                        ->getIdentity()
                        ->getOrganisation());
            $event->setAuthor($this->zfcUserAuthentication()->getIdentity());

            $categories = $this->em->getRepository('Application\Entity\InterrogationPlanCategory')->findAll();

            if ($categories) 
            {
                $intplancat = $categories[0];
                $event->setCategory($intplancat);

                $typefieldvalue = new CustomFieldValue();
                $typefieldvalue->setCustomField($intplancat->getTypeField());
                $typefieldvalue->setValue($post['type']);
                $typefieldvalue->setEvent($event);
                $event->addCustomFieldValue($typefieldvalue);
                $this->em->persist($typefieldvalue);

                $latfieldvalue = new CustomFieldValue();
                $latfieldvalue->setCustomField($intplancat->getLatField());
                $latfieldvalue->setValue($post['lat']);
                $latfieldvalue->setEvent($event);
                $event->addCustomFieldValue($latfieldvalue);
                $this->em->persist($latfieldvalue);

                $longfieldvalue = new CustomFieldValue();
                $longfieldvalue->setCustomField($intplancat->getLongField());
                $longfieldvalue->setValue($post['lon']);
                $longfieldvalue->setEvent($event);
                $event->addCustomFieldValue($longfieldvalue);
                $this->em->persist($longfieldvalue);

                $alertcats = $this->em->getRepository('Application\Entity\AlertCategory')->findAll();

                if ($alertcats) 
                {
                    $alertcat = $alertcats[0];
                    $alertevent = new Event();
                    $alertevent->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
                    $alertevent->setStartdate($now);
                    $alertevent->setImpact($this->em->getRepository('Application\Entity\Impact')->find('3'));
                    $alertevent->setPunctual(false);
                    $alertevent->setOrganisation($event->getOrganisation());
                    $alertevent->setAuthor($this->zfcUserAuthentication()->getIdentity());
                    $alertevent->setCategory($alertcat);

                    $typealertfieldvalue = new CustomFieldValue();
                    $typealertfieldvalue->setCustomField($alertcat->getTypeField());
                    $typealertfieldvalue->setValue($post['typealerte']);
                    $typealertfieldvalue->setEvent($alertevent);
                    $alertevent->addCustomFieldValue($typealertfieldvalue);
                    $this->em->persist($typealertfieldvalue);

                    $causefieldvalue = new CustomFieldValue();
                    $causefieldvalue->setCustomField($alertcat->getCauseField());
                    $causefieldvalue->setValue($post['cause']);
                    $causefieldvalue->setEvent($alertevent);
                    $alertevent->addCustomFieldValue($causefieldvalue);
                    $this->em->persist($causefieldvalue);
                    try 
                    {
                        $this->em->flush();
                        $alertfieldvalue = new CustomFieldValue();
                        $alertfieldvalue->setCustomField($intplancat->getAlertField());
                        $alertfieldvalue->setValue($alertevent->getId());
                        $alertfieldvalue->setEvent($event);
                        $event->addCustomFieldValue($alertfieldvalue);
                        $this->em->persist($alertfieldvalue);
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                    }
                }

                if (isset($post['custom_fields'])) 
                {
                    foreach ($post['custom_fields'] as $key => $value) {
                        // génération des customvalues si un customfield dont le nom est $key est trouvé
                        $customfield = $this->em->getRepository('Application\Entity\CustomField')->findOneBy(array(
                            'id' => $key
                        ));
                        if ($customfield) {
                            if (is_array($value)) {
                                $temp = "";
                                foreach ($value as $v) {
                                    $temp .= (string) $v . "\r";
                                }
                                $value = trim($temp);
                            }
                            $customvalue = new CustomFieldValue();
                            $customvalue->setEvent($event);
                            $customvalue->setCustomField($customfield);
                            $event->addCustomFieldValue($customvalue);
                            
                            $customvalue->setValue($value);
                            $this->em->persist($customvalue);
                        }
                    }
                }
                //et on sauve le tout
                $this->em->persist($event);
                try {
                    $this->em->flush();
                    $idEvent = $event->getId();
                    $msgType = 'success';
                    $msg = "Plan d'interrogation démarré.";
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                }
            }
        }
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg,
            'id' => $idEvent
        ]);
    }

    public function endAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();
        $msgType = 'error';
        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];    
        $end_date = $post['end_date'];

        if ($id > 0) 
        {
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));

            if (isset($end_date)) $end_date = new \DateTime($end_date);
            else $end_date = $now;

            $ip = $this->em->getRepository(Event::class)->find($id);
            $endstatus = $this->em->getRepository('Application\Entity\Status')->find('3');
            $ip->setStatus($endstatus);
            $ip->setEnddate($end_date);
            $this->em->persist($ip);

            foreach ($ip->getChildren() as $field) {
                $field->setStatus($endstatus);
                $field->setEnddate($end_date);
                $this->em->persist($field);
            }

            $alt = $this->em->getRepository(Event::class)->find($this->getAlertIdFromIp($ip));
            $alt->setStatus($endstatus);
            $alt->setEnddate($end_date);            
            $this->em->persist($alt);

            try 
            {
                $this->em->flush();
                $msgType = 'success';
                $msg = "Clôture du plan d'interrogation.";
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        } else $msg = "Impossible de trouver le plan d'interrogation.";
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg
        ]);
    }

    public function delfieldAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();
        $msgType = 'error';
        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];
        if ($id > 0) 
        {
            $ip = $this->em->getRepository(Event::class)->find($id);
            foreach ($ip->getChildren() as $field) 
            {
                $cat = $field->getCategory();

                foreach ($field->getCustomFieldsValues() as $value) 
                {
                    if ($value->getCustomField()->getId() == $cat->getCodeField()->getId() && $value->getValue() == $post['code']) 
                    {
                        $found = true;
                        foreach ($field->getChildren() as $child) {
                            $this->em->remove($child);
                        }
                        $this->em->remove($field);
                        try 
                        {
                            $this->em->flush();
                            $msgType = 'success';
                            $msg = "Terrain supprimé du plan d'interrogation.";
                        } catch (\Exception $e) {
                            $msg = $e->getMessage();
                        }
                    }
                }
                if (!isset($found)) $msg = "Ce terrain n'est pas dans le plan d'interrogation.";

            }
        } else $msg = "Impossible de trouver le plan d'interrogation.";
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg
        ]);
    }

    public function addfieldAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();
        $msgType = 'error';

        $post = $this->getRequest()->getPost();
        // print_r($post);
        $id = (int) $post['id'];
        if ($id > 0) 
        {
            $ip = $this->em->getRepository(Event::class)->find($id);
            $idfield = 0;
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));
            
            // crétation de l'evenement d'alerte
            $event = new Event();
            $event->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
            $event->setStartdate($now);
            $event->setImpact($this->em->getRepository('Application\Entity\Impact')->find('3'));
            $event->setPunctual(false);
            $event->setOrganisation($this->zfcUserAuthentication()
                        ->getIdentity()
                        ->getOrganisation());
            $event->setAuthor($this->zfcUserAuthentication()->getIdentity());
            $event->setParent($ip);

            $categories = $this->em->getRepository('Application\Entity\FieldCategory')->findAll();

            if ($categories) 
            {
                $fieldcat = $categories[0];
                $event->setCategory($fieldcat);

                $namefieldvalue = new CustomFieldValue();
                $namefieldvalue->setCustomField($fieldcat->getNameField());
                $namefieldvalue->setValue($post['name']);
                $namefieldvalue->setEvent($event);
                $event->addCustomFieldValue($namefieldvalue);
                $this->em->persist($namefieldvalue);

                $codefieldvalue = new CustomFieldValue();
                $codefieldvalue->setCustomField($fieldcat->getCodeField());
                $codefieldvalue->setValue($post['code']);
                $codefieldvalue->setEvent($event);
                $event->addCustomFieldValue($codefieldvalue);
                $this->em->persist($codefieldvalue);

                $latfieldvalue = new CustomFieldValue();
                $latfieldvalue->setCustomField($fieldcat->getLatField());
                $latfieldvalue->setValue($post['lat']);
                $latfieldvalue->setEvent($event);
                $event->addCustomFieldValue($latfieldvalue);
                $this->em->persist($latfieldvalue);

                $longfieldvalue = new CustomFieldValue();
                $longfieldvalue->setCustomField($fieldcat->getLongField());
                $longfieldvalue->setValue($post['lon']);
                $longfieldvalue->setEvent($event);
                $event->addCustomFieldValue($longfieldvalue);
                $this->em->persist($longfieldvalue);

                $ip->addChild($event);
                $this->em->persist($ip);
                $this->em->persist($event);
                try {
                    $this->em->flush();
                    $idfield = $event->getId();
                    $msgType = 'success';
                    $msg = "Terrain ajouté au plan d'interrogation.";
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                }
            }
        }
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg,
            'id' => $idfield
        ]);
    }

    public function addNoteAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();
        $msgType = 'error';

        $post = $this->getRequest()->getPost();
        // print_r($post);
        $id = (int) $post['id'];
        if ($id > 0) 
        {
            $field = $this->em->getRepository(Event::class)->find($id);
            if ($field && strlen(trim($post['text'])) > 0) 
            {
                $eventupdate = new EventUpdate();
                $eventupdate->setText($post['text']);
                $eventupdate->setEvent($field);
                $field->setLastModifiedOn();
                $this->em->persist($eventupdate);
                $this->em->persist($field);
                try 
                {
                    $this->em->flush();
                    $msgType = 'success';
                    $msg = "Note correctement ajoutée.";
                    // $messages['events'] = array(
                    //     $event->getId() => $this->getEventJson($event)
                    // );
                } catch (\Exception $ex) {
                    $msg = $ex->getMessage();
                }
            } else {
                $msg = "Impossible d'ajouter la note (évènement non trouvé).";
            }
        } else {
            $msg = "Impossible d'ajouter la note.";
        }
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg,
        ]);

    }

    public function formAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];
        if ($id > 0) {
            $ip = $this->em->getRepository(Event::class)->find($id);
            if ($ip) 
            {
                $formval = [];
                foreach ($ip->getCustomFieldsValues() as $customfieldvalue) 
                {
                    $formval[$customfieldvalue->getCustomField()->getName()] = $customfieldvalue->getValue();
                } 
                if ($formval['Alerte']) {
                    $alt = $this->em->getRepository(Event::class)->find($formval['Alerte']);
                    if ($alt) 
                    {
                        $formval['Alerte'] = [];
                        foreach ($alt->getCustomFieldsValues() as $customfieldvalue) 
                        {
                            $formval['Alerte'][$customfieldvalue->getCustomField()->getName()] = $customfieldvalue->getValue();
                        }   
                    }
                }  
            }
        }
        $form = new Form('sarbeacons');

        $idfield = new Element\Hidden('id');
        (isset($id)) ? $idfield->setValue($id):null;
        $form->add($idfield);

        $lat = new Element\Text('lat');
        $lat->setLabel('Latitude');
        (isset($formval['Latitude'])) ? $lat->setValue($formval['Latitude']):null;
        $form->add($lat);

        $lon = new Element\Text('lon');
        $lon->setLabel('Longitude');
        (isset($formval['Longitude'])) ? $lon->setValue($formval['Longitude']):null;
        $form->add($lon);

        $type = new Element\Select('type');
        $type->setLabel('Type');
        $type->setValueOptions([
            'PIO' => 'PIO',
            'PIA' => 'PIA'
        ]);
        (isset($formval['Type'])) ? $type->setValue($formval['Type']):null;
        $form->add($type);

        $typealerte = new Element\Select('typealerte');
        $typealerte->setLabel('Type d\'alerte');
        $typealerte->setValueOptions([
            // 'INCERFA' => 'INCERFA',
            'ALERFA' => 'ALERFA',
            'DETRESFA' => 'DETRESFA', 
        ]);
        (isset($formval['Alerte']['Type'])) ? $typealerte->setValue($formval['Alerte']['Type']):null;
        $form->add($typealerte);

        $cause = new Element\Textarea('cause');
        $cause->setAttributes(['rows' => 7]);
        $cause->setLabel('Raison');
        (isset($formval['Alerte']['Cause'])) ? $cause->setValue($formval['Alerte']['Cause']):null;
        $form->add($cause);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $form
            ]);
    }

    public function listAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $allIntPlans = $this->em->getRepository(Event::class)->getCurrentIntPlanEvents();
        $intPlans = [];
        foreach ($allIntPlans as $ip) {
            $intPlans[] = $this->getArrayCopy($ip);
        }
        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'intPlans' => array_reverse($intPlans)
            ]);
    }

    public function archivesAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $allIntPlans = $this->em->getRepository(Event::class)->getEndedIntPlanEvents();
        $intPlans = [];
        foreach ($allIntPlans as $ip) {
            $intPlans[] = $this->getArrayCopy($ip);
        }
        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setTemplate('application/sar-beacons/list')
            ->setVariables([
                'intPlans' => array_reverse($intPlans)
            ]);
    }

    private function saveToPdf($ipArray) 
    {
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4');                         

        $pdfView = (new ViewModel($pdf))
            ->setTerminal(true)
            ->setTemplate('application/sar-beacons/print')
            ->setVariables([
                'ip' => $ipArray
            ]);

        $html = $this->viewpdfrenderer->getHtmlRenderer()->render($pdfView);
        $engine = $this->viewpdfrenderer->getEngine();
        $engine->load_html($html);
        $engine->render();

        $dir = 'data/interrogation-plans';
        if(! is_dir($dir)) mkdir($dir);
        file_put_contents($ipArray['pdffilepath'], $engine->output());
    }

    public function validpdfAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $ip = $this->em->getRepository(Event::class)->find($this->params()->fromRoute('id'));
        $ipArray = $this->getArrayCopy($ip);
        $this->saveToPdf($ipArray);
        if (!file_exists($ipArray['pdffilepath'])) 
            return new JsonModel(['error', 'Problème lors du chargement du fichier pdf. Impossible de continuer.']); 
        else return new JsonModel();

    }

    public function printAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $ip = $this->em->getRepository(Event::class)->find($this->params()->fromRoute('id'));
        $ipArray = $this->getArrayCopy($ip);

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4')
            ->setOption('filename', $ipArray['pdffilepath'])
            ->setVariables([
                'ip' => $ipArray
            ]);

        return $pdf;
    }

    public function mailAction() {
        if (!array_key_exists('emailfrom', $this->config) | !array_key_exists('smtp', $this->config)) return new JsonModel(['error', 'Problème de configuration des adresses email.']);

        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];

        if ($id > 0) {
            $ip = $this->em->getRepository(Event::class)->find($id);
            $ipArray = $this->getArrayCopy($ip);

            $text = new MimePart('Veuillez trouver ci-joint un plan d\'interrogation.');
            $text->type = Mime::TYPE_TEXT;
            $text->charset = 'utf-8';
            
            $fileContents = fopen($ipArray['pdffilepath'], 'r');
            $attachment = new MimePart($fileContents);
            $attachment->type = 'application/pdf';

            $attachment->filename = $ipArray['pdffilename'];

            $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
            
            $body = new MimeMessage();
            $body
                ->setParts([
                    $text,
                    $attachment
                ]);

            $message = (new Message())
                ->setEncoding("UTF-8")
                ->addFrom($this->config['emailfrom'])
                ->addTo('loic.perrin@aviation-civile.gouv.fr')
                ->setSubject('Plan d\'interrogation')
                ->setBody($body);

            if($message->isValid()) 
            {
                $transportOptions = new SmtpOptions($this->config['smtp']);
                $transport = (new Smtp())
                    ->setOptions($transportOptions)
                    ->send($message);
            }
        }
        return new JsonModel(['success', 'Courriel(s) envoyé(s) avec succès.']);
    }

    private function authSarBeacons($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('sarbeacons.'.$action)) ? false : true;
    }

}