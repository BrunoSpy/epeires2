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
            $pdf = new PdfModel();
            $pdf->setVariables(array('events' => $events, 'day' => $day));
            $pdf->setOption('paperSize', 'a4');
            
            $formatter = \IntlDateFormatter::create(\Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            'dd_LL_yyyy');
            $pdf->setOption('filename', 'rapport_du_'.$formatter->format(new \DateTime($day)));
            
            return $pdf;
        } else {
            //erreur
        }
        
    }
    
}
