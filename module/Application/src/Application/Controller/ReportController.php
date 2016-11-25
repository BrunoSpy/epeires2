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

use Zend\Mvc\Controller\AbstractActionController;
use DOMPDFModule\View\Model\PdfModel;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Doctrine\Common\Collections\Criteria;

/**
 *
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class ReportController extends AbstractActionController
{

    public function fnebrouillageAction()
    {
        $view = $this->params()->fromQuery('view', null);
        
        $brouillageid = $this->params()->fromQuery('id', null);
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $brouillage = $objectManager->getRepository('Application\Entity\Event')->find($brouillageid);
        
        if ($brouillage) {
            $fields = array();
            foreach ($brouillage->getCustomFieldsValues() as $values) {
                $fields[$values->getCustomField()->getId()] = $values->getValue();
            }
            $frequency = $objectManager->getRepository('Application\Entity\Frequency')->find($fields[$brouillage->getCategory()
                ->getFrequencyField()
                ->getId()]);
            
            if ($view == 'pdf') {
                $pdf = new PdfModel();
                $pdf->setVariable('event', $brouillage);
                
                $pdf->setVariables(array(
                    'frequency' => $frequency,
                    'fields' => $fields
                ));
                
                // $pdf->setOption('filename', 'fne-brouillage');
                $pdf->setOption('paperSize', 'a4');
                
                return $pdf;
            } else {
                $viewmodel = new ViewModel();
                $viewmodel->setVariable('event', $brouillage);
                $viewmodel->setVariables(array(
                    'frequency' => $frequency,
                    'fields' => $fields
                ));
                // disable layout if request by Ajax
                $viewmodel->setTerminal(true);
                return $viewmodel;
            }
        }
    }

    public function dailyAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $day = $this->params()->fromQuery('day', null);
        
        if ($day) {
            $daystart = new \DateTime($day);
            $offset = $daystart->getTimezone()->getOffset($daystart);
            $daystart->setTimezone(new \DateTimeZone('UTC'));
            $daystart->add(new \DateInterval("PT" . $offset . "S"));
            $daystart->setTime(0, 0, 0);

            $dayend = new \DateTime($day);
            $dayend->setTimezone(new \DateTimeZone('UTC'));
            $dayend->add(new \DateInterval("PT" . $offset . "S"));
            $dayend->setTime(23, 59, 59);

            $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'))
                ->andWhere(Criteria::expr()->eq('system', false))
                ->orderBy(array(
                'place' => Criteria::ASC
            ));
            
            $cats = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
            
            $eventsbycats = array();
            
            foreach ($cats as $cat) {
                $category = array();
                $category['name'] = $cat->getName();

                //évènements lisibles par l'utilisateur, du jour spécifié, de la catégorie et non supprimés
                $category['events'] = $objectManager
                    ->getRepository('Application\Entity\Event')
                    ->getEvents($this->zfcUserAuthentication(), $day, null, null, true, array($cat->getId()), array(1,2,3,4));
                $category['childs'] = array();
                foreach ($cat->getChildren() as $subcat) {
                    $subcategory = array();
                    $subcategory['events'] = $objectManager
                        ->getRepository('Application\Entity\Event')
                        ->getEvents($this->zfcUserAuthentication(), $day, null, null, true, array($subcat->getId()), array(1,2,3,4));
                    $subcategory['name'] = $subcat->getName();
                    $category['childs'][] = $subcategory;
                }
                $eventsbycats[] = $category;
            }
            
            $pdf = new PdfModel();
            $pdf->setVariables(array(
                'events' => $eventsbycats,
                'day' => $day,
                'logs' => $objectManager->getRepository('Application\Entity\Log'),
                'opsups' => $objectManager->getRepository('Application\Entity\Log')->getOpSupsChanges($daystart, $dayend, false, 'ASC')
            ));
            $pdf->setOption('paperSize', 'a4');
            
            $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');
            $pdf->setOption('filename', 'rapport_du_' . $formatter->format(new \DateTime($day)));
            
            return $pdf;
        } else {
            // erreur
        }
    }

    public function reportAction()
    {
        $request = $this->getRequest();
        
        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $j = $request->getParam('delta');
        
        $email = $request->getParam('email');
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findBy(array(
            'shortname' => $org
        ));
        
        if (! $organisation) {
            throw new \RuntimeException('Unable to find organisation.');
        } else {
            $emailIPO = $organisation[0]->getIpoEmail();
            if ($email && empty($emailIPO)) {
                throw new \RuntimeException('Unable to find IPO email.');
            }
        }
        
        $day = new \DateTime('now');
        if ($j) {
            if ($j > 0) {
                $day->add(new \DateInterval('P' . $j . 'D'));
            } else {
                $j = - $j;
                $interval = new \DateInterval('P' . $j . 'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }
        
        $day = $day->format(DATE_RFC2822);

        $daystart = new \DateTime($day);
        $offset = $daystart->getTimezone()->getOffset($daystart);
        $daystart->setTimezone(new \DateTimeZone('UTC'));
        $daystart->add(new \DateInterval("PT" . $offset . "S"));
        $daystart->setTime(0, 0, 0);

        $dayend = new \DateTime($day);
        $dayend->setTimezone(new \DateTimeZone('UTC'));
        $dayend->add(new \DateInterval("PT" . $offset . "S"));
        $dayend->setTime(23, 59, 59);

        $criteria = Criteria::create()
        ->where(Criteria::expr()->isNull('parent'))
        ->andWhere(Criteria::expr()->eq('system', false))
        ->orderBy(array('place' => Criteria::ASC));
        
        $cats = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
        
        $eventsByCats = array();
        foreach ($cats as $cat) {
            $category = array();
            $category['name'] = $cat->getName();
            $category['events'] = $objectManager
                ->getRepository('Application\Entity\Event')
                ->getEvents(null, $day, null, null, true, array($cat->getId()), array(1,2,3,4));
            $category['childs'] = array();
            foreach ($cat->getChildren() as $subcat) {
                $subcategory = array();
                $subcategory['events'] = $objectManager
                    ->getRepository('Application\Entity\Event')
                    ->getEvents(null, $day, null, null, true, array($subcat->getId()), array(1,2,3,4));
                $subcategory['name'] = $subcat->getName();
                $category['childs'][] = $subcategory;
            }
            $eventsByCats[] = $category;
        }
                
        $pdf = new PdfModel();
        $pdf->setOption('paperSize', 'a4');
        
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');
        
        $pdf->setOption('filename', 'rapport_du_' . $formatter->format(new \DateTime($day)));
        
        $pdfView = new ViewModel($pdf);
        $pdfView->setTerminal(true)
                ->setTemplate('application/report/daily')
                ->setVariables(array(
                    'events' => $eventsByCats,
                    'day' => $day,
                    'logs' => $objectManager->getRepository('Application\Entity\Log'),
                    'opsups' => $objectManager->getRepository('Application\Entity\Log')->getOpSupsChanges($daystart, $dayend, false, 'ASC')));

        $html = $this->getServiceLocator()->get('viewpdfrenderer')->getHtmlRenderer()->render($pdfView);
        $engine = $this->getServiceLocator()->get('viewpdfrenderer')->getEngine();

        $engine->load_html($html);
        $engine->render();
        
        // creating directory if it doesn't exists
        if (! is_dir('data/reports')) {
            mkdir('data/reports');
        }
        
        file_put_contents('data/reports/rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf', $engine->output());
        
        if ($email) {
            // prepare body with file attachment
            $text = new \Zend\Mime\Part('Veuillez trouver ci-joint le rapport automatique de la journée du ' . $formatter->format(new \DateTime($day)));
            $text->type = \Zend\Mime\Mime::TYPE_TEXT;
            $text->charset = 'utf-8';
            
            $fileContents = fopen('data/reports/rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf', 'r');
            $attachment = new \Zend\Mime\Part($fileContents);
            $attachment->type = 'application/pdf';
            $attachment->filename = 'rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf';
            $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
            
            $mimeMessage = new \Zend\Mime\Message();
            $mimeMessage->setParts(array(
                $text,
                $attachment
            ));
            
            $config = $this->serviceLocator->get('config');
            $message = new \Zend\Mail\Message();
            $message->addTo($organisation[0]->getIpoEmail())
                ->addFrom($config['emailfrom'])
                ->setSubject('Rapport automatique du ' . $formatter->format(new \DateTime($day)))
                ->setBody($mimeMessage);
            
            $transport = new \Zend\Mail\Transport\Smtp();
            $transportOptions = new \Zend\Mail\Transport\SmtpOptions($config['smtp']);
            $transport->setOptions($transportOptions);
            $transport->send($message);
        }
    }
}
