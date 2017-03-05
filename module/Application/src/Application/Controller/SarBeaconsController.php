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

use Core\Controller\AbstractEntityManagerAwareController;

use Application\Entity\InterrogationPlan;
use Application\Entity\Field;
use Application\Entity\Organisation;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractEntityManagerAwareController
{
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
        if (!$this->authSarBeacons('read')) return new JsonModel();
    }

    public function startAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $msgType = 'error';

        $post = $this->getRequest()->getPost();
        echo $post['id'];
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));

        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['id' => 1]);
        // crétation de l'evenement d'alerte
        $event = new Event();
        $event->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
        $event->setStartdate($now);
        $event->setImpact($this->em->getRepository('Application\Entity\Impacts')->find('3'));
        $event->setPunctual(false);
        $event->setOrganisation($organisation);
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
                $alertevent->setOrganisation($organisation);
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
                $msgType = 'success';
                $msg = "Plan d'interrogation démarré.";
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        }
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg
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
            'INCERFA' => 'INCERFA',
            'ALERTFA' => 'ALERTFA',
            'DETRESSFA' => 'DETRESSFA', 
        ]);
        (isset($formval['Alerte']['Type'])) ? $typealerte->setValue($formval['Alerte']['Type']):null;
        $form->add($typealerte);

        $cause = new Element\Textarea('cause');
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

        $allIntPlans = $this->em->getRepository(Event::class)->getIntPlanEvents();

        $intPlans = [];

        foreach ($allIntPlans as $ipEvent) {
            $ev = [
                'id' => $ipEvent->getId(),
                'start_date' => $ipEvent->getStartDate(),
            ];

            foreach ($ipEvent->getCustomFieldsValues() as $value) 
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
            $intPlans[] = $ev;
        }


        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'intPlans' => array_reverse($intPlans)
            ]);
    }
    // TODO BOF BOF
    public function saveAction()
    {
        if (!$this->authSarBeacons('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $pdatas = $post['datas'];
        $ppio = (is_array($post['iP'])) ? $post['iP'] : [];
        $datasIntPlan = [];
        parse_str($pdatas, $datasIntPlan);

        if (is_array($post['iP']) && count($post['iP']) > 0) {
            $fields = [];
            foreach ($ppio as $i => $field) {
                $f = new Field($field);
                if ($f->isValid()) $fields[] = $f;
            }
            $datasIntPlan['fields'] = $fields;
        }

        $id = intval($datasIntPlan['id']);
        $iP = ($id) ? $this->repo->find($id) : new InterrogationPlan();
        $this->form->setData($datasIntPlan);

        if (!$this->form->isValid()) $iP = false;
        else 
        { 
            $iP = $this->repo->hydrate($this->form->getData(), $iP);
        }
        if (is_a($iP, InterrogationPlan::class)) {
            $this->repo->save($iP);    
            $this->saveToPdf($iP);       
        }

        return new JsonModel([
            'id' => $iP->getId(),
            'type' => 'success',
            'msg' => 'Le plan d\'interrogation a bien été enregistré.'
        ]);
    }

    private function saveToPdf($iP) 
    {
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4');                         

        $pdfView = (new ViewModel($pdf))
            ->setTerminal(true)
            ->setTemplate('application/sar-beacons/print')
            ->setVariables([
                'iP' => $iP
            ]);

        $html = $this->viewpdfrenderer->getHtmlRenderer()->render($pdfView);
        $engine = $this->viewpdfrenderer->getEngine();
        $engine->load_html($html);
        $engine->render();

        $dir = 'data/interrogation-plans';
        if(! is_dir($dir)) mkdir($dir);
        file_put_contents($iP->getPdfFilePath(), $engine->output());
    }

    public function getAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $iP = $this->repo->find($post['id']);
        
        return new JsonModel($iP->getArrayCopy());
    }

    public function validpdfAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $iP = $this->repo->find($this->params()->fromRoute('id'));
        if (!file_exists($iP->getPdfFilePath())) 
            return new JsonModel(['error', 'Problème lors du chargement du fichier pdf. Impossible de continuer.']); 
        else return new JsonModel();

    }

    public function printAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $iP = $this->repo->find($this->params()->fromRoute('id'));

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4')
            ->setOption('filename', $iP->getPdfFileName())
            ->setVariables([
                'iP' => $iP
            ]);

        return $pdf;
    }

    public function mailAction() {
        if (!array_key_exists('emailfrom', $this->config) | !array_key_exists('smtp', $this->config)) return new JsonModel(['error', 'Problème de configuration des adresses email.']);

        $post = $this->getRequest()->getPost();
        $iP = $this->repo->find($post['id']);

        $text = new MimePart('Veuillez trouver ci-joint un plan d\'interrogation.');
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        
        $fileContents = fopen($iP->getPdfFilePath(), 'r');
        $attachment = new MimePart($fileContents);
        $attachment->type = 'application/pdf';

        $attachment->filename = $iP->getPdfFileName();

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
        return new JsonModel(['success', 'Courriel(s) envoyé(s) avec succès.']);
    }

    private function authSarBeacons($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('sarbeacons.'.$action)) ? false : true;
    }

}