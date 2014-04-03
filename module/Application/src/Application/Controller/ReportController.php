<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DOMPDFModule\View\Model\PdfModel;

/**
 * Description of ReportController
 *
 * @author spyckerelle
 */
class ReportController extends AbstractActionController {
    
    public function fnebrouillageAction(){
        $pdf = new PdfModel();
        $pdf->setOption('filename', 'fne-brouillage');
        $pdf->setOption('parperSize', 'a4');
        
        return $pdf;
        
    }
    
}
