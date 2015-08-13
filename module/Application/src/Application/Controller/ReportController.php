<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use DOMPDFModule\View\Model\PdfModel;

use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;

use OpentbsBundle\Factory\TBSFactory as TBS;

/**
 * Description of ReportController
 *
 * @author spyckerelle
 */
class ReportController extends AbstractActionController {

    public function fnebrouillageAction() {

        $view = $this->params()->fromQuery('view', null);

        $brouillageid = $this->params()->fromQuery('id', null);

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $brouillage = $objectManager->getRepository('Application\Entity\Event')->find($brouillageid);

        if ($brouillage) {
            $fields = array();
            foreach ($brouillage->getCustomFieldsValues() as $values) {
                $fields[$values->getCustomField()->getId()] = $values->getValue();
            }
            $frequency = $objectManager->getRepository('Application\Entity\Frequency')->find($fields[$brouillage->getCategory()->getFrequencyField()->getId()]);

            if ($view == 'pdf') {
                $pdf = new PdfModel();
                $pdf->setVariable('event', $brouillage);

                $pdf->setVariables(array('frequency' => $frequency, 'fields' => $fields));

                //   $pdf->setOption('filename', 'fne-brouillage');
                $pdf->setOption('paperSize', 'a4');

                return $pdf;
            } else {
                $viewmodel = new ViewModel();
                $viewmodel->setVariable('event', $brouillage);
                $viewmodel->setVariables(array('frequency' => $frequency, 'fields' => $fields));
                //disable layout if request by Ajax
                $viewmodel->setTerminal(true);
                return $viewmodel;
            }
        }
    }

    public function dailyAction(){       
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $day = $this->params()->fromQuery('day', null);
              
        if($day){
            $events = $objectManager->getRepository('Application\Entity\Event')->getEvents($this->zfcUserAuthentication(), $day, null, true);
//             $pdf = new PdfModel();
//             $pdf->setVariables(array('events' => $events, 'day' => $day));
//             $pdf->setOption('paperSize', 'a4');
            
//             $formatter = \IntlDateFormatter::create(\Locale::getDefault(),
//             \IntlDateFormatter::FULL,
//             \IntlDateFormatter::FULL,
//             'UTC',
//             \IntlDateFormatter::GREGORIAN,
//             'dd_LL_yyyy');
//             $pdf->setOption('filename', 'rapport_du_'.$formatter->format(new \DateTime($day)));
            
            
//             return $pdf;

            $tbs = new TBS();
            $tbs->LoadTemplate('data/templates/cr_ipo.odt');
            $tbs->MergeField('client', array('name' => 'Bruno'));
            // send the file
            $tbs->Show(OPENTBS_DOWNLOAD, 'test.odt');
        } else {
            //erreur
        }
        
    }
    
    public function reportAction(){
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $j = $request->getParam('delta');
        
        $email = $request->getParam('email');
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findBy(array('shortname' => $org));
        
        if(!$organisation){
            throw new \RuntimeException('Unable to find organisation.');
        } else {
            $email = $organisation[0]->getIpoEmail();
            if(empty($email)){
                throw new \RuntimeException('Unable to find IPO email.');
            }
        }
        
        $day = new \DateTime('now');
        if($j){
            if($j > 0){
                $day->add(new \DateInterval('P'.$j.'D'));
            } else {
                $j = -$j;
                $interval = new \DateInterval('P'.$j.'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }
        
        $day = $day->format(DATE_RFC2822);

        $events = $objectManager->getRepository('Application\Entity\Event')->getEvents(null, $day, null, true);
        $pdf = new PdfModel();
        $pdf->setVariables(array('events' => $events, 'day' => $day));
        $pdf->setOption('paperSize', 'a4');

        $formatter = \IntlDateFormatter::create(
                            \Locale::getDefault(),
                            \IntlDateFormatter::FULL,
                            \IntlDateFormatter::FULL,
                            'UTC',
                            \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');
        
        $pdf->setOption('filename', 'rapport_du_' . $formatter->format(new \DateTime($day)));

        $pdfView = new ViewModel($pdf);
        $pdfView->setTerminal(true)
                ->setTemplate('application/report/daily')
                ->setVariables(array('events' => $events, 'day' => $day));

        $html = $this->getServiceLocator()->get('viewpdfrenderer')->getHtmlRenderer()->render($pdfView);
        $engine = $this->getServiceLocator()->get('viewpdfrenderer')->getEngine();

        $engine->load_html($html);
        $engine->render();

        //creating directory if it doesn't exists
        if(!is_dir('data/reports')){
            mkdir('data/reports');
        }
        
        file_put_contents('data/reports/rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf', $engine->output());
        
        if($email){
            //prepare body with file attachment
            $text = new \Zend\Mime\Part('Veuillez trouver ci-joint le rapport automatique de la journée du '.$formatter->format(new \DateTime($day)));
            $text->type = \Zend\Mime\Mime::TYPE_TEXT;
            $text->charset = 'utf-8';
            
            $fileContents = fopen('data/reports/rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf', 'r');
            $attachment = new \Zend\Mime\Part($fileContents);
            $attachment->type = 'application/pdf';
            $attachment->filename = 'rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf';
            $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;

            $mimeMessage = new \Zend\Mime\Message();
            $mimeMessage->setParts(array($text, $attachment));
            
            $config = $this->serviceLocator->get('config');
            $message = new \Zend\Mail\Message();
            $message->addTo($organisation[0]->getIpoEmail())
                    ->addFrom($config['emailfrom'])
                    ->setSubject('Rapport automatique du '. $formatter->format(new \DateTime($day)))
                    ->setBody($mimeMessage);
            
            $transport = new \Zend\Mail\Transport\Smtp();
            $transportOptions = new \Zend\Mail\Transport\SmtpOptions($config['smtp']);
            $transport->setOptions($transportOptions);
            $transport->send($message);
        }
    }

}
