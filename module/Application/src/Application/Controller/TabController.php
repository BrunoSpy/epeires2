<?php

namespace Application\Controller;

use Zend\Session\Container;


/**
 * Sets all variables needed to a controller
 * @author Bruno Spyckerelle
 */
class TabController extends ZoneController {

    public function indexAction(){
        parent::indexAction();
        
        $this->layout()->iponumber = "";
        if($this->zfcUserAuthentication()->hasIdentity()){
            $iponumber = $this->zfcUserAuthentication()->getIdentity()->getOrganisation()->getIpoNumber();
            if($iponumber != null && strlen($iponumber) > 0) {
                $this->layout()->iponumber = "(".$iponumber.")";
            } 
        }
    	
        //initialisation de la session si utilisateur connecté
        $session = new Container('zone');
        if($session->zoneshortname == null){
            if($this->zfcUserAuthentication()->hasIdentity()){
                $session->zoneshortname = $this->zfcUserAuthentication()->getIdentity()->getOrganisation()->getShortname();
            }
        }
        
        //Determine if ZoneMil tab is usefull
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $zonesmils = $em->getRepository('Application\Entity\MilCategory')->findBy(array('onMilPage' => true));
        if(count($zonesmils) > 0){
            $this->layout()->zonesmil = true;
        } else {
            $this->layout()->zonesmil = false;
        }
    }
    
}

