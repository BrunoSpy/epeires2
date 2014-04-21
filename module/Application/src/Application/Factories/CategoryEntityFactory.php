<?php
namespace Application\Factories;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Entity\RadarCategory;
use Application\Entity\CustomField;
use Application\Entity\AntennaCategory;
use Application\Entity\FrequencyCategory;
use Application\Entity\ActionCategory;
use Application\Entity\BrouillageCategory;

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
                
                //si aucune cat par défaut --> nouvelle catégorie par défaut
                $cats = $em->getRepository('Application\Entity\RadarCategory')->findBy(array('defaultradarcategory' => true));
                $radarcat->setDefaultRadarCategory((count($cats) == 0));
                
		$em->persist($radarfield);
		$em->persist($statusfield);
		return $radarcat;
	}
	
        public function createActionCategory(){
		$em = $this->getEntityManager();
		$actioncat = new ActionCategory();
		$namefield = new CustomField();
		$namefield->setCategory($actioncat);
		$namefield->setName('Nom');
		$namefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
		$namefield->setPlace(1);
		$namefield->setDefaultValue("");
		$textfield = new CustomField();
		$textfield->setCategory($actioncat); 
		$textfield->setName('Commentaire');
		$textfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'text')));
		$textfield->setPlace(2);
		$textfield->setDefaultValue("");
		$actioncat->setFieldname($namefield);
		$actioncat->setNamefield($namefield);
		$actioncat->setTextfield($textfield);
		$em->persist($namefield);
		$em->persist($textfield);
		return $actioncat;
	}
        
        public function createAlarmCategory(){
		$em = $this->getEntityManager();
		$alarmcat = new AlarmCategory();
		$namefield = new CustomField();
		$namefield->setCategory($alarmcat);
		$namefield->setName('Nom');
		$namefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
		$namefield->setPlace(1);
		$namefield->setDefaultValue("");
		$textfield = new CustomField();
		$textfield->setCategory($alarmcat); 
		$textfield->setName('Commentaire');
		$textfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'text')));
		$textfield->setPlace(2);
		$textfield->setDefaultValue("");
		$alarmcat->setFieldname($namefield);
		$alarmcat->setNamefield($namefield);
		$alarmcat->setTextfield($textfield);
		$em->persist($namefield);
		$em->persist($textfield);
		return $alarmcat;
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
                
                //si aucune cat par défaut --> nouvelle catégorie par défaut
                $cats = $em->getRepository('Application\Entity\AntennaCategory')->findBy(array('defaultantennacategory' => true));
                $antennacat->setDefaultAntennaCategory((count($cats) == 0));
                
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
                
                //si aucune cat par défaut --> nouvelle catégorie par défaut
                $cats = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array('defaultfrequencycategory' => true));
                $frequencycat->setDefaultFrequencyCategory((count($cats) == 0));
                
		return $frequencycat;
	}
	
	public function createBrouillageCategory(){
		$em = $this->getEntityManager();
		$brouillagecat = new BrouillageCategory();
		$frequencyfield = new CustomField();
		$frequencyfield->setCategory($brouillagecat);
		$frequencyfield->setName('Fréquence');
		$frequencyfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'frequency')));
		$frequencyfield->setPlace(1);
		$frequencyfield->setDefaultValue("");
		
		$levelfield = new CustomField();
		$levelfield->setCategory($brouillagecat);
		$levelfield->setName('Niveau');
		$levelfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
		$levelfield->setPlace(2);
		$levelfield->setDefaultValue("");
			
		$rnavfield = new CustomField();
		$rnavfield->setCategory($brouillagecat);
		$rnavfield->setName('Balise');
		$rnavfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
		$rnavfield->setPlace(3);
		$rnavfield->setDefaultValue("");
	
		$distancefield = new CustomField();
		$distancefield->setCategory($brouillagecat);
		$distancefield->setName('Distance');
		$distancefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
		$distancefield->setPlace(4);
		$distancefield->setDefaultValue("");
	
		$azimutfield = new CustomField();
		$azimutfield->setCategory($brouillagecat);
		$azimutfield->setName('Azimut');
		$azimutfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
		$azimutfield->setPlace(5);
		$azimutfield->setDefaultValue("");
		
		$originfield = new CustomField();
		$originfield->setCategory($brouillagecat);
		$originfield->setName('Plaignant');
		$originfield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'select')));
		$originfield->setPlace(6);
		$originfield->setDefaultValue("Sol\nBord\nSol+Bord");
		
		$typefield = new CustomField();
		$typefield->setCategory($brouillagecat);
		$typefield->setName('Type de bruit');
		$typefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'select')));
		$typefield->setPlace(7);
		$typefield->setDefaultValue("Brouillage\nInterférence");
		
		$causebrouillagefield = new CustomField();
		$causebrouillagefield->setCategory($brouillagecat);
		$causebrouillagefield->setName('Cause du brouillage');
		$causebrouillagefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'select')));
		$causebrouillagefield->setPlace(8);
		$causebrouillagefield->setDefaultValue("Radio FM\nBruit industriel\nAutre\nRien");
			
		$causeinterferencefield = new CustomField();
		$causeinterferencefield->setCategory($brouillagecat);
		$causeinterferencefield->setName('Cause interférence');
		$causeinterferencefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'select')));
		$causeinterferencefield->setPlace(10);
		$causeinterferencefield->setDefaultValue("Porteuse\nÉmission permanente\nAutre fréquence");
				
		$commentairebrouillagefield = new CustomField();
		$commentairebrouillagefield->setCategory($brouillagecat);
		$commentairebrouillagefield->setName('Cause du brouillage (commentaire)');
		$commentairebrouillagefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'text')));
		$commentairebrouillagefield->setPlace(9);
		$commentairebrouillagefield->setDefaultValue("");
			
		$commentaireinterferencefield = new CustomField();
		$commentaireinterferencefield->setCategory($brouillagecat);
		$commentaireinterferencefield->setName('Cause interférence (commentaire)');
		$commentaireinterferencefield->setType($em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'text')));
		$commentaireinterferencefield->setPlace(11);
		$commentaireinterferencefield->setDefaultValue("");
				
		$brouillagecat->setFieldname($frequencyfield);
		$brouillagecat->setFrequencyfield($frequencyfield);
		$brouillagecat->setLevelField($levelfield);
		$brouillagecat->setRnavField($rnavfield);
		$brouillagecat->setDistanceField($distancefield);
		$brouillagecat->setAzimutField($azimutfield);
		$brouillagecat->setOriginField($originfield);
		$brouillagecat->setTypeField($typefield);
		$brouillagecat->setCauseBrouillageField($causebrouillagefield);
		$brouillagecat->setCauseInterferenceField($causeinterferencefield);
		$brouillagecat->setCommentaireBrouillageField($commentairebrouillagefield);
		$brouillagecat->setCommentaireInterferenceField($commentaireinterferencefield);
		
		$em->persist($frequencyfield);
		$em->persist($levelfield);
		$em->persist($rnavfield);
		$em->persist($distancefield);
		$em->persist($azimutfield);
		$em->persist($originfield);
		$em->persist($typefield);
		$em->persist($causebrouillagefield);
		$em->persist($causeinterferencefield);
		$em->persist($commentairebrouillagefield);
		$em->persist($commentaireinterferencefield);
                
                //si aucune cat par défaut --> nouvelle catégorie par défaut
                $cats = $em->getRepository('Application\Entity\BrouillageCategory')->findBy(array('defaultbrouillagecategory' => true));
                $brouillagecat->setDefaultBrouillageCategory((count($cats) == 0));
                
		return $brouillagecat;
	}
}