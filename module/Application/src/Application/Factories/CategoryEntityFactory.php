<?php
namespace Application\Factories;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Entity\RadarCategory;
use Application\Entity\CustomField;
use Application\Entity\AntennaCategory;
use Application\Entity\FrequencyCategory;

class CategoryEntityFactory implements ServiceLocatorAwareInterface{
		

	private $sm;
	
	private $em = null;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator){
		$this->sm = $serviceLocator;
	}

	public function getServiceLocator(){
		return $this->sm;
	}
	
	private function getEntityManager(){
		if(!$this->em){
			$this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		}
		return $this->em;
	}
	
	/**
	 * To be persisted
	 * @return \Application\Entity\RadarCategory
	 */
	public function createRadarCategory(){
		$em = $this->getEntityManager();
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
		$em = $this->getEntityManager();
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
	
	public function createFrequencyCategory(){
		$em = $this->getEntityManager();
		$frequencycat = new FrequencyCategory();
		$frequencyfield = new CustomField();
		$frequencyfield->setCategory($frequencycat);
		$frequencyfield->setName('Fréquence');
		$frequencyfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'frequency')));
		$frequencyfield->setPlace(1);
		$frequencyfield->setDefaultValue("");
		$statefield = new CustomField();
		$statefield->setCategory($frequencycat);
		$statefield->setName('Indisponible');
		$statefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'boolean')));
		$statefield->setPlace(2);
		$statefield->setDefaultValue("");
		$currentAntenna = new CustomField();
		$currentAntenna->setCategory($frequencycat);
		$currentAntenna->setName('Couverture');
		$currentAntenna->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type' => 'select')));
		$currentAntenna->setPlace(3);
		$currentAntenna->setDefaultValue("Normale\nSecours");
		$otherfreq = new CustomField();
		$otherfreq->setCategory($frequencycat);
		$otherfreq->setName('Utiliser fréquence');
		$otherfreq->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type' => 'frequency')));
		$otherfreq->setPlace(4);
		$otherfreq->setDefaultValue("");
		$frequencycat->setFieldname($frequencyfield);
		$frequencycat->setFrequencyfield($frequencyfield);
		$frequencycat->setCurrentAntennafield($currentAntenna);
		$frequencycat->setStatefield($statefield);
		$frequencycat->setOtherFrequencyfield($otherfreq);
		$em->persist($frequencyfield);
		$em->persist($statefield);
		$em->persist($currentAntenna);
		$em->persist($otherfreq);
		return $frequencycat;
	}
}