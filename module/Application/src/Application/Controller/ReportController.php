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

        if ($view == 'pdf') {
            $pdf = new PdfModel();
            $pdf->setOption('filename', 'fne-brouillage');
            $pdf->setOption('parperSize', 'a4');

            return $pdf;
        } else {
            $viewmodel = new ViewModel();
            $request = $this->getRequest();

            //disable layout if request by Ajax
            $viewmodel->setTerminal(true);
            return $viewmodel;
        }
    }

}
