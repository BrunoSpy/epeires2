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

class IPOHelper extends AbstractHelper {
	
	private $sm;
	
	public function __invoke(){
		
		$html = "";
		
		$auth = $this->sm->get('ZfcRbac\Service\AuthorizationService');

		$zfcuserauth = $this->sm->get('zfcuser_auth_service');
		
		$objectmanager = $this->sm->get('Doctrine\ORM\EntityManager');
		
		if($zfcuserauth->hasIdentity()) {
		
			$ipos = $objectmanager->getRepository('Application\Entity\IPO')->findBy(array('organisation' => $zfcuserauth->getIdentity()->getOrganisation()->getId()), array('name' => 'asc'));
		
			$currentipo = $objectmanager->getRepository('Application\Entity\IPO')->findOneBy(
									array('organisation' => $zfcuserauth->getIdentity()->getOrganisation()->getId(),
											'current' => true));
			if($auth->isGranted('events.mod-ipo')) {
				
				$form = new Form('ipo');
				$selectIPO = new Select('nameipo');
				$ipoArray = array();
				$ipoArray['-1'] = "Choisir IPO";
				foreach ($ipos as $ipo) {
					$ipoArray[$ipo->getId()] = $ipo->getName();
				}
				
				$selectIPO->setValueOptions($ipoArray);
				if($currentipo){
					$selectIPO->setAttribute('value', $currentipo->getId());
				}
				
				$form->add($selectIPO);
				
				$formView = $this->view->form();
				
				$html .= "<li>";
				
				$html .= $formView->openTag($form);
				$html .= $this->view->formSelect($form->get('nameipo'));
				$html .= $formView->closeTag();
				$html .= "</li>";
			} else {
				if($currentipo) {
					$html .= '<span id="iponame">'.$currentipo->getName().'</span>';
				} else {
					$html .= "<em>Aucun IPO configuré</em>";
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