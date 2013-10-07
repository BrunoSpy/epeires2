<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class CustomFieldValue extends AbstractHelper implements ServiceManagerAwareInterface {
	
	private $servicemanager;
	
	public function __invoke($customfieldvalue){

		return $this->servicemanager->get('CustomFieldService')->getFormattedValue($customfieldvalue->getCustomField(), $customfieldvalue->getValue());
		
	}
	
	public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceLocator){
		$this->servicemanager = $serviceLocator;
	}
	
}