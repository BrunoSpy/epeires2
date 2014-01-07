<?php
namespace Application\Factories;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Entity\RadarCategory;
use Application\Entity\CustomField;
use Application\Entity\AntennaCategory;

class CategoryEntityFactory implements ServiceLocatorAwareInterface{
		

	private $sm;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator){
		$this->sm = $serviceLocator;
	}

	public function getServiceLocator(){
		return $this->sm;
	}
	
	/**
	 * To be persisted
	 * @return \Application\Entity\RadarCategory
	 */
	public function createRadarCategory(){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$radarcat = new RadarCategory();
		$radarfield = new CustomField();
		$radarfield->setCategory($radarcat);
		$radarfield->setName('Radar');
		$radarfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'radar')));
		$radarfield->setPlace(1);
		$radarfield->setDefaultValue("");
		$statusfield = new CustomField();
		$statusfield->setCategory($radarcat);
		$statusfield->setName('Indisponible');
		$statusfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'boolean')));
		$statusfield->setPlace(2);
		$statusfield->setDefaultValue("");
		$radarcat->setFieldname($radarfield);
		$radarcat->setRadarfield($radarfield);
		$radarcat->setStatefield($statusfield);
		$em->persist($radarfield);
		$em->persist($statusfield);
		return $radarcat;
	}
	
	public function createAntennaCategory(){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$antennacat = new AntennaCategory();
		$antennafield = new CustomField();
		$antennafield->setCategory($antennacat);
		$antennafield->setName('Antenne');
		$antennafield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'antenna')));
		$antennafield->setPlace(1);
		$antennafield->setDefaultValue("");
		$statusfield = new CustomField();
		$statusfield->setCategory($antennacat);
		$statusfield->setName('Indisponible');
		$statusfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'boolean')));
		$statusfield->setPlace(2);
		$statusfield->setDefaultValue("");
		$antennacat->setFieldname($antennafield);
		$antennacat->setAntennafield($antennafield);
		$antennacat->setStatefield($statusfield);
		$em->persist($antennafield);
		$em->persist($statusfield);
		return $antennacat;
	}
}