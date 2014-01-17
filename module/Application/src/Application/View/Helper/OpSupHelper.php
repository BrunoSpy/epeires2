<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;
use Zend\Form\Element\Select;

class OpSupHelper extends AbstractHelper {
	
	private $sm;
	
	public function __invoke(){
		
		$html = "";
		
		$auth = $this->sm->get('ZfcRbac\Service\AuthorizationService');

		$zfcuserauth = $this->sm->get('zfcuser_auth_service');
		
		$objectmanager = $this->sm->get('Doctrine\ORM\EntityManager');
		
		if($zfcuserauth->hasIdentity()) {
		
			$opsups = $objectmanager->getRepository('Application\Entity\OperationalSupervisor')->findBy(array('organisation' => $zfcuserauth->getIdentity()->getOrganisation()->getId()));
		
			$currentopsup = $objectmanager->getRepository('Application\Entity\OperationalSupervisor')->findOneBy(
									array('organisation' => $zfcuserauth->getIdentity()->getOrganisation()->getId(),
											'zone' => $zfcuserauth->getIdentity()->getZone()->getId(),
											'current' => true));
			if($auth->isGranted('events.mod-opsup')) {
				
				$form = new Form('opsup');
				$selectOpSup = new Select('nameopsup');
				$opsupArray = array();
				$opsupArray['-1'] = "Choisir Op Sup";
				foreach ($opsups as $opsup) {
					$opsupArray[$opsup->getId()] = $opsup->getName();
				}
				
				$selectOpSup->setValueOptions($opsupArray);
				if($currentopsup){
					$selectOpSup->setAttribute('value', $currentopsup->getId());
				}
				
				$form->add($selectOpSup);
				
				$formView = $this->view->form();
				
				$html .= "<li>";
				
				$html .= $formView->openTag($form);
				$html .= $this->view->formSelect($form->get('nameopsup'));
				$html .= $formView->closeTag();
				$html .= "</li>";
			} else {
				if($currentopsup) {
					$html .= $currentopsup->getName();
				} else {
					$html .= "<em>Aucun Op Sup configuré</em>";
				}
			}
		} else {
			$html .= "<em>Connexion nécessaire</em>";
		}
		return $html;
		
	}
	
    public function setServiceManager($servicemanager){
    	$this->sm = $servicemanager;
    }
	
}