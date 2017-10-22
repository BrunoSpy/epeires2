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
    const ERR_ACCES = "Droits d'accès insuffisants.";
    const ERR_NO_CONF_BTIV = "Configuration btiv inexistante. Vérifier dans local.php que le tableau ['btiv'] est bien défini.";
    const ERR_NO_CONF_MAIL = "Des paramétres requis ne sont pas configurés pour l'envoi de courriels. Vérifier dans local.php que ip_email_from/ip_email_to/ip_email_text/ip_email_subject sont bien définis.";
    const ERR_NO_IP = "Impossible de trouver le plan d'interrogation.";
    const ERR_EMAIL_INVALID = "Le message à envoyer n'est pas formaté correctement.";

    const OK_SEND_EMAIL = "Courriels envoyés avec succès.";


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
            echo self::ERR_ACCES;
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
                'alertcats' => $alertcats,
                'btivCONF' => $this->config['btiv']
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
        $dirname =  $this->config['btiv']['ip_dir'].'/';
        $filename = $ip->getStartDate()->format('Ymd').'_Alerte_id'.$ip->getId().'.pdf';
        $ev = [
            'id' => $ip->getId(),
            'pdffilepath' => $dirname.$filename,
            'pdffilename' => $filename,
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
         // vérification utilisateur autorisé
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

    /* création du formulaire pour le démarrage d'un plan */
    public function formAction() 
    {
         // vérification utilisateur autorisé
        if (!$this->authSarBeacons('read')) return new JsonModel();
        // récupération de l'id du plan d'interrogation à envoyer 
        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];

        // formulaire de modification => id > 0
        if ($id > 0)
        {
            // interrogation de la bdd
            $ip = $this->em->getRepository(Event::class)->find($id);
            // test si le plan existe bien
            if(!is_a($ip, Event::class)) {
                return new JsonModel(['error', self::ERR_NO_IP]);
            } 
            else 
            {
                // récupération des valeurs des customfield pour remplir le formulaire de modification
                $formval = [];
                foreach ($ip->getCustomFieldsValues() as $customfieldvalue) 
                {
                    $formval[$customfieldvalue->getCustomField()->getName()] = $customfieldvalue->getValue();
                } 
                if ($formval['Alerte']) 
                {
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
        // création du formulaire
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
            AlertCategory::TYPES_ALERT[1] => AlertCategory::TYPES_ALERT[1],
            AlertCategory::TYPES_ALERT[2] => AlertCategory::TYPES_ALERT[2], 
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

    /* accès aux plans en cours */
    public function listAction() 
    {
        // vérification utilisateur autorisé
        if (!$this->authSarBeacons('read')) return new JsonModel();
        // récupération de tous les plans en cours
        $allIntPlans = $this->em->getRepository(Event::class)->getCurrentIntPlanEvents();
        // on va stocker les versions tableau des plans dans un seul tableau     
        $intPlans = [];
        foreach ($allIntPlans as $ip) $intPlans[] = $this->getArrayCopy($ip);
        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                 // les derniers plans crées doivent être affichés en haut
                'intPlans' => array_reverse($intPlans)
            ]);
    }

    /* accès aux plans archivés */
    public function archivesAction() 
    {
        // vérification utilisateur autorisé
        if (!$this->authSarBeacons('read')) return new JsonModel();
        // récupération de tous les plans terminés
        $allIntPlans = $this->em->getRepository(Event::class)->getEndedIntPlanEvents();
        // on va stocker les versions tableau des plans dans un seul tableau
        $intPlans = [];
        foreach ($allIntPlans as $ip) $intPlans[] = $this->getArrayCopy($ip);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setTemplate('application/sar-beacons/list')
            ->setVariables([
                // les derniers plans crées doivent être affichés en haut
                'intPlans' => array_reverse($intPlans)
            ]);
    }

    /* génération du pdf */
    private function saveToPdf($ipArray) 
    {
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4');                         

        $pdfView = (new ViewModel($pdf))
            ->setTerminal(true)
            // vue particulière pour le pdf
            ->setTemplate('application/sar-beacons/print')
            ->setVariables([
                'ip' => $ipArray
            ]);

        $html = $this->viewpdfrenderer->getHtmlRenderer()->render($pdfView);
        $engine = $this->viewpdfrenderer->getEngine();
        $engine->load_html($html);
        $engine->render();

        $dir = $this->config['btiv']['ip_dir'];
        // vérification de l'existence du répertoire où les pdf sont sauvegardés. S'il n'existe pas, il est crée.
        if(!is_dir($dir)) mkdir($dir);
        // sauvegarde du fichier
        file_put_contents($ipArray['pdffilepath'], $engine->output());
    }

    /* création du pdf avant de l'imprimer / envoyer par email */
    public function validpdfAction() 
    {
        // vérification utilisateur autorisé
        if (!$this->authSarBeacons('read')) return new JsonModel();
        // récupération de l'id du plan d'interrogation à envoyer 
        $id = (int) $this->params()->fromRoute('id');
        $ip = $this->em->getRepository(Event::class)->find($id);
        // interrogation de la bdd
        if(!is_a($ip, Event::class)) {
            return new JsonModel(['error', self::ERR_NO_IP]);
        } 
        // récupération des données du plan en tableau
        $ipArray = $this->getArrayCopy($ip);
        // création du pdf
        $this->saveToPdf($ipArray);
        // vérification de l'existence du fichier pdf crée
        if (!file_exists($ipArray['pdffilepath'])) {
            return new JsonModel(['error', 'Problème lors du chargement du fichier pdf. Impossible de continuer.']);
        }
        else return new JsonModel();
    }

    /* 'Impression' (ou plutot sauvegarde en fichier pdf pour impression) */
    public function printAction()
    {
        // vérification utilisateur autorisé
        if (!$this->authSarBeacons('read')) return new JsonModel();
        // récupération de l'id du plan d'interrogation à envoyer 
        $id = (int) $this->params()->fromRoute('id');
        $ip = $this->em->getRepository(Event::class)->find($id);
        // interrogation de la bdd
        if(!is_a($ip, Event::class)) {
            return new JsonModel(['error', self::ERR_NO_IP]);
        } 
        // récupération des données du plan en tableau
        $ipArray = $this->getArrayCopy($ip);
        // récupération du fichier pdf préalablement crée dans validpdf puis savetopdf
        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4')
            ->setOption('filename', $ipArray['pdffilename'])
            ->setVariables([
                'ip' => $ipArray
            ]);
        return $pdf;
    }

    /* Envoi du plan d'interrogation par mail. Configuration dans local.php, clé btiv.  */
    public function mailAction() 
    {
        // vérification utilisateur autorisé
        if (!$this->authSarBeacons('read')) return new JsonModel();
        /* Vérification de la config */
        // clé btiv n'existe pas dans ['config']
        if (!array_key_exists('btiv', $this->config)) {
            return new JsonModel(['error', self::ERR_NO_CONF_BTIV]);
        } else {
            // clés pour l'envoi de mail n'existent pas 
            if( !array_key_exists('ip_email_from', $this->config['btiv']) | 
                !array_key_exists('ip_email_to', $this->config['btiv']) |
                !array_key_exists('ip_email_subject', $this->config['btiv']) |
                !array_key_exists('ip_email_text', $this->config['btiv']) 
            ) {
                return new JsonModel(['error', self::ERR_NO_CONF_MAIL]);
            } 
        }
        
        // récupération de l'id du plan d'interrogation à envoyer 
        $post = $this->getRequest()->getPost();
        $id = (int) $post['id'];
        // interrogation de la bdd
        $ip = $this->em->getRepository(Event::class)->find($id);
        // test si le plan existe bien
        if(!is_a($ip, Event::class)) {
            return new JsonModel(['error', self::ERR_NO_IP]);
        } 
        else 
        {
            $ipArray = $this->getArrayCopy($ip);
            /* préparation de l'email */
            // texte du message issue de la config
            $text = new MimePart($this->config['btiv']['ip_email_text']);
            $text->type = Mime::TYPE_TEXT;
            $text->charset = 'utf-8';          
            // attachement du pdf au message
            $fileContents = fopen($ipArray['pdffilepath'], 'r');
            $attachment = new MimePart($fileContents);
            $attachment->type = 'application/pdf';
            $attachment->filename = $ipArray['pdffilename'];
            $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
            // création du body
            $body = new MimeMessage();
            $body->setParts([$text, $attachment]);
            // enfin, création du message complet
            $message = (new Message())
                ->setEncoding("UTF-8")
                ->addFrom($this->config['btiv']['ip_email_from'])
                ->setSubject($this->config['btiv']['ip_email_subject'])
                ->setBody($body);
            // ajout des destinataires
            foreach($this->config['btiv']['ip_email_to'] as $receiver) {
                $message->addTo($receiver);
            }
            // vérification de la validité du message crée
            if($message->isValid()) 
            {
                $transportOptions = new SmtpOptions($this->config['smtp']);
                $transport = (new Smtp())
                    ->setOptions($transportOptions)
                    ->send($message);
                return new JsonModel(['success', self::OK_SEND_EMAIL]);
            } else {
                return new JsonModel(['error', self::ERR_EMAIL_INVALID]);
            }
        }
    }

    private function authSarBeacons($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('sarbeacons.'.$action)) ? false : true;
    }

}