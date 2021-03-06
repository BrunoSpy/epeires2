<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Factories;

use Application\Entity\ATFCMCategory;
use Application\Entity\CustomFieldType;
use Application\Entity\SwitchObjectCategory;
use Application\Entity\CustomField;
use Application\Entity\AntennaCategory;
use Application\Entity\FrequencyCategory;
use Application\Entity\BrouillageCategory;
use Application\Entity\MilCategory;
use Application\Entity\AfisCategory;
use Application\Entity\FlightPlanCategory;
use Application\Entity\AlertCategory;
use Application\Entity\InterrogationPlanCategory;
use Application\Entity\FieldCategory;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class CategoryEntityFactory
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function createMilCategory()
    {
        $em = $this->getEntityManager();
        
        $stringtype = $em->getRepository('Application\Entity\CustomFieldType')->findOneBy(array(
            'type' => 'string'
        ));
        
        $milcat = new MilCategory();
        $namefield = new CustomField();
        $namefield->setCategory($milcat);
        $namefield->setName('Nom');
        $namefield->setType($stringtype);
        $namefield->setPlace(1);
        $namefield->setDefaultValue("");
        $namefield->setTooltip("");
        $milcat->setFieldname($namefield);
        
        $lower = new CustomField();
        $lower->setCategory($milcat);
        $lower->setName('Plancher');
        $lower->setType($stringtype);
        $lower->setPlace(2);
        $lower->setDefaultValue("");
        $lower->setTooltip("");
        $milcat->setLowerLevelField($lower);
        
        $upper = new CustomField();
        $upper->setCategory($milcat);
        $upper->setName("Plafond");
        $upper->setType($stringtype);
        $upper->setPlace(3);
        $upper->setDefaultValue("");
        $upper->setTooltip("");
        $milcat->setUpperLevelField($upper);
        
        $milcat->setZonesRegex('');
        
        $em->persist($upper);
        $em->persist($lower);
        $em->persist($namefield);
        return $milcat;
    }

    /**
     * To be persisted
     * 
     * @return \Application\Entity\SwitchObjectCategory
     */
    public function createSwitchObjectCategory()
    {
        $em = $this->getEntityManager();
        $switchcat = new SwitchObjectCategory();
        $switchobjectfield = new CustomField();
        $switchobjectfield->setCategory($switchcat);
        $switchobjectfield->setName('Objet');
        $switchobjectfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'switch'
        )));
        $switchobjectfield->setPlace(1);
        $switchobjectfield->setDefaultValue("");
        $switchobjectfield->setTooltip("");
        $statusfield = new CustomField();
        $statusfield->setCategory($switchcat);
        $statusfield->setName('Indisponible');
        $statusfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'boolean'
        )));
        $statusfield->setPlace(2);
        $statusfield->setDefaultValue("");
        $statusfield->setTooltip("");
        $switchcat->setFieldname($switchobjectfield);
        $switchcat->setSwitchObjectField($switchobjectfield);
        $switchcat->setStateField($statusfield);
        
        $em->persist($switchobjectfield);
        $em->persist($statusfield);
        return $switchcat;
    }

    public function createAntennaCategory()
    {
        $em = $this->getEntityManager();
        $antennacat = new AntennaCategory();
        $antennafield = new CustomField();
        $antennafield->setCategory($antennacat);
        $antennafield->setName('Antenne');
        $antennafield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'antenna'
        )));
        $antennafield->setPlace(1);
        $antennafield->setDefaultValue("");
        $antennafield->setTooltip("");
        $statusfield = new CustomField();
        $statusfield->setCategory($antennacat);
        $statusfield->setName('Indisponible');
        $statusfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'boolean'
        )));
        $statusfield->setPlace(2);
        $statusfield->setDefaultValue("");
        $statusfield->setTooltip("");
        
        $frequenciesfield = new CustomField();
        $frequenciesfield->setCategory($antennacat);
        $frequenciesfield->setName('Fréquences impactées');
        $frequenciesfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'frequency'
        )));
        $frequenciesfield->setPlace(3);
        $frequenciesfield->setDefaultValue("");
        $frequenciesfield->setMultiple(true);
        $frequenciesfield->setTooltip("Pas de valeur = toutes.");
        
        $antennacat->setFrequenciesField($frequenciesfield);
        $antennacat->setFieldname($antennafield);
        $antennacat->setAntennafield($antennafield);
        $antennacat->setStatefield($statusfield);
        $em->persist($antennafield);
        $em->persist($statusfield);
        $em->persist($frequenciesfield);
        // si aucune cat par défaut --> nouvelle catégorie par défaut
        $cats = $em->getRepository('Application\Entity\AntennaCategory')->findBy(array(
            'defaultantennacategory' => true
        ));
        $antennacat->setDefaultAntennaCategory((count($cats) == 0));
        
        return $antennacat;
    }

    public function createFrequencyCategory()
    {
        $em = $this->getEntityManager();
        $frequencycat = new FrequencyCategory();
        $frequencyfield = new CustomField();
        $frequencyfield->setCategory($frequencycat);
        $frequencyfield->setName('Fréquence');
        $frequencyfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'frequency'
        )));
        $frequencyfield->setPlace(1);
        $frequencyfield->setDefaultValue("");
        $frequencyfield->setTooltip("");
        $statefield = new CustomField();
        $statefield->setCategory($frequencycat);
        $statefield->setName('Indisponible');
        $statefield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'boolean'
        )));
        $statefield->setPlace(2);
        $statefield->setDefaultValue("");
        $statefield->setTooltip("");
        
        $currentAntenna = new CustomField();
        $currentAntenna->setCategory($frequencycat);
        $currentAntenna->setName('Couverture');
        $currentAntenna->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'select'
        )));
        $currentAntenna->setPlace(3);
        $currentAntenna->setDefaultValue("Normale\nSecours");
        $currentAntenna->setTooltip("");
        $otherfreq = new CustomField();
        $otherfreq->setCategory($frequencycat);
        $otherfreq->setName('Utiliser fréquence');
        $otherfreq->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'frequency'
        )));
        $otherfreq->setPlace(4);
        $otherfreq->setDefaultValue("");
        $otherfreq->setTooltip("");
        
        
        $cause = new CustomField();
        $cause->setCategory($frequencycat);
        $cause->setName("Cause");
        $cause->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'text'
            )));
        $cause->setPlace(5);
        $cause->setDefaultValue("");
        $cause->setTooltip("");
        
        $frequencycat->setFieldname($frequencyfield);
        $frequencycat->setFrequencyField($frequencyfield);
        $frequencycat->setCurrentAntennaField($currentAntenna);
        $frequencycat->setStateField($statefield);
        $frequencycat->setOtherFrequencyField($otherfreq);
        $frequencycat->setCauseField($cause);
        
        $em->persist($frequencyfield);
        $em->persist($statefield);
        $em->persist($currentAntenna);
        $em->persist($otherfreq);
        $em->persist($cause);
        
        // si aucune cat par défaut --> nouvelle catégorie par défaut
        $cats = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array(
            'defaultfrequencycategory' => true
        ));
        $frequencycat->setDefaultFrequencyCategory((count($cats) == 0));
        
        return $frequencycat;
    }

    public function createBrouillageCategory()
    {
        $em = $this->getEntityManager();
        $brouillagecat = new BrouillageCategory();
        $frequencyfield = new CustomField();
        $frequencyfield->setCategory($brouillagecat);
        $frequencyfield->setName('Fréquence');
        $frequencyfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'frequency'
        )));
        $frequencyfield->setPlace(1);
        $frequencyfield->setDefaultValue("");
        $frequencyfield->setTooltip("");

        $brouillagecat->setFieldname($frequencyfield);
        $brouillagecat->setFrequencyfield($frequencyfield);
        
        $em->persist($frequencyfield);

        // si aucune cat par défaut --> nouvelle catégorie par défaut
        $cats = $em->getRepository('Application\Entity\BrouillageCategory')->findBy(array(
            'defaultbrouillagecategory' => true
        ));
        $brouillagecat->setDefaultBrouillageCategory((count($cats) == 0));
        
        return $brouillagecat;
    }

    public function createAfisCategory()
    {
        $em = $this->getEntityManager();
        $afiscat = new AfisCategory();
        $afisfield = new CustomField();
        $afisfield->setCategory($afiscat);
        $afisfield->setName('Afis');
        $afisfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'afis'
        )));
        $afisfield->setPlace(1);
        $afisfield->setDefaultValue("");
        $afisfield->setTooltip("");
        $statusfield = new CustomField();
        $statusfield->setCategory($afiscat);
        $statusfield->setName('Ouvert');
        $statusfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
            'type' => 'boolean'
        )));
        $statusfield->setPlace(2);
        $statusfield->setDefaultValue("");
        $statusfield->setTooltip("");
        $afiscat->setFieldname($afisfield);
        $afiscat->setAfisfield($afisfield);
        $afiscat->setStatefield($statusfield);
        
        // si aucune cat par défaut --> nouvelle catégorie par défaut
        $cats = $em->getRepository('Application\Entity\AfisCategory')->findBy(array(
            'defaultafiscategory' => true
        ));
        $afiscat->setDefaultAfisCategory((count($cats) == 0));
        
        $em->persist($afisfield);
        $em->persist($statusfield);
        return $afiscat;
    }

    public function createFlightPlanCategory()
    {
        $em = $this->getEntityManager();
        $fpcat = new FlightPlanCategory();
        $aircraftidfield = new CustomField();
        $aircraftidfield->setPlace(1);
        $aircraftidfield->setDefaultValue("");
        $aircraftidfield->setTooltip("");
        $aircraftidfield->setCategory($fpcat);
        $aircraftidfield->setName('Aircraft-Id');
        $aircraftidfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));
    
        $startfield = new CustomField();
        $startfield->setPlace(2);
        $startfield->setDefaultValue("");
        $startfield->setTooltip("");
        $startfield->setCategory($fpcat);
        $startfield->setName('Terrain de départ');
        $startfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $destinationfield = new CustomField();
        $destinationfield->setPlace(3);
        $destinationfield->setDefaultValue("");
        $destinationfield->setTooltip("");
        $destinationfield->setCategory($fpcat);
        $destinationfield->setName('Terrain de destination');
        $destinationfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $estimatedtimeofarrivalfield = new CustomField();
        $estimatedtimeofarrivalfield->setPlace(5);
        $estimatedtimeofarrivalfield->setDefaultValue("");
        $estimatedtimeofarrivalfield->setTooltip("");
        $estimatedtimeofarrivalfield->setCategory($fpcat);
        $estimatedtimeofarrivalfield->setName('ETA');
        $estimatedtimeofarrivalfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $alertfield = new CustomField();
        $alertfield->setPlace(5);
        $alertfield->setDefaultValue("");
        $alertfield->setTooltip("");
        $alertfield->setCategory($fpcat);
        $alertfield->setName('Alerte');
        $alertfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'alert'
            )));

        $fpcat->setFieldname($aircraftidfield);
        $fpcat->setAircraftidfield($aircraftidfield);
        $fpcat->setDestinationfield($destinationfield);
        $fpcat->setStartfield($startfield);
        $fpcat->setAlertfield($alertfield);
        $fpcat->setEstimatedtimeofarrivalfield($estimatedtimeofarrivalfield);
    
        // si aucune cat par défaut --> nouvelle catégorie par défaut
        $cats = $em->getRepository('Application\Entity\FlightPlanCategory')->findBy(array(
            'defaultflightplancategory' => true
        ));
        $fpcat->setDefaultFlightPlanCategory((count($cats) == 0));
    
        $em->persist($aircraftidfield);
        $em->persist($startfield);
        $em->persist($destinationfield);
        $em->persist($estimatedtimeofarrivalfield);
        $em->persist($alertfield);
        return $fpcat;
    }

    public function createAlertCategory()
    {
        $em = $this->getEntityManager();
        $alertcat = new AlertCategory();
        $typefield = new CustomField();
        $typefield->setPlace(1);
        $typefield->setDefaultValue("");
        $typefield->setTooltip("");
        $typefield->setCategory($alertcat);
        $typefield->setName('Type');
        $typefield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $causefield = new CustomField();
        $causefield->setPlace(2);
        $causefield->setDefaultValue("");
        $causefield->setTooltip("");
        $causefield->setCategory($alertcat);
        $causefield->setName('Cause');
        $causefield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'text'
            )));

        $alertcat->setFieldname($typefield);
        $alertcat->setTypeField($typefield);
        $alertcat->setCauseField($causefield);

        $em->persist($typefield);
        $em->persist($causefield);
        return $alertcat;
    }

    public function createInterrogationPlanCategory()
    {
        $em = $this->getEntityManager();
        $intplancat = new InterrogationPlanCategory();
        $typefield = new CustomField();
        $typefield->setPlace(1);
        $typefield->setDefaultValue("PIO\nPIA");
        $typefield->setTooltip("");
        $typefield->setCategory($intplancat);
        $typefield->setName('Type');
        $typefield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $latfield = new CustomField();
        $latfield->setPlace(2);
        $latfield->setDefaultValue("");
        $latfield->setTooltip("");
        $latfield->setCategory($intplancat);
        $latfield->setName('Latitude');
        $latfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $longfield = new CustomField();
        $longfield->setPlace(3);
        $longfield->setDefaultValue("");
        $longfield->setTooltip("");
        $longfield->setCategory($intplancat);
        $longfield->setName('Longitude');
        $longfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $alertfield = new CustomField();
        $alertfield->setPlace(4);
        $alertfield->setDefaultValue("");
        $alertfield->setTooltip("");
        $alertfield->setCategory($intplancat);
        $alertfield->setName('Alerte');
        $alertfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'alert'
            )));

        $intplancat->setFieldname($typefield);
        $intplancat->setTypeField($typefield);
        $intplancat->setLatField($latfield);
        $intplancat->setLongField($longfield);
        $intplancat->setAlertField($alertfield);

        $em->persist($typefield);
        $em->persist($latfield);
        $em->persist($longfield);
        $em->persist($alertfield);
        return $intplancat;
    }

    public function createFieldCategory()
    {
        $em = $this->getEntityManager();
        $fieldcat = new FieldCategory();

        $namefield = new CustomField();
        $namefield->setPlace(1);
        $namefield->setDefaultValue("");
        $namefield->setTooltip("");
        $namefield->setCategory($fieldcat);
        $namefield->setName('Nom');
        $namefield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $codefield = new CustomField();
        $codefield->setPlace(2);
        $codefield->setDefaultValue("");
        $codefield->setTooltip("");
        $codefield->setCategory($fieldcat);
        $codefield->setName('Code OACI');
        $codefield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $latfield = new CustomField();
        $latfield->setPlace(3);
        $latfield->setDefaultValue("");
        $latfield->setTooltip("");
        $latfield->setCategory($fieldcat);
        $latfield->setName('Latitude');
        $latfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $longfield = new CustomField();
        $longfield->setPlace(4);
        $longfield->setDefaultValue("");
        $longfield->setTooltip("");
        $longfield->setCategory($fieldcat);
        $longfield->setName('Longitude');
        $longfield->setType($em->getRepository('Application\Entity\CustomFieldType')
            ->findOneBy(array(
                'type' => 'string'
            )));

        $fieldcat->setFieldname($codefield);
        $fieldcat->setNameField($namefield);
        $fieldcat->setCodeField($codefield);
        $fieldcat->setLatField($latfield);
        $fieldcat->setLongField($longfield);

        $em->persist($namefield);
        $em->persist($codefield);
        $em->persist($latfield);
        $em->persist($longfield);
        return $fieldcat;
    }

    public function createATFCMCategory() {
        $em = $this->entityManager;

        $atfcmcategory = new ATFCMCategory();

        $namefield = new CustomField();
        $namefield->setPlace(1);
        $namefield->setDefaultValue("");
        $namefield->setTooltip("");
        $namefield->setName("Nom");
        $namefield->setType($em->getRepository(CustomFieldType::class)->findOneBy(array('type'=>'string')));
        $namefield->setCategory($atfcmcategory);

        $reasonfield = new CustomField();
        $reasonfield->setPlace(2);
        $reasonfield->setDefaultValue("");
        $reasonfield->setTooltip("");
        $reasonfield->setName("Raison");
        $reasonfield->setType($em->getRepository(CustomFieldType::class)->findOneBy(array('type'=>'string')));
        $reasonfield->setCategory($atfcmcategory);

        $descriptionfield = new CustomField();
        $descriptionfield->setPlace(3);
        $descriptionfield->setDefaultValue("");
        $descriptionfield->setTooltip("");
        $descriptionfield->setName("Description");
        $descriptionfield->setType($em->getRepository(CustomFieldType::class)->findOneBy(array('type'=>'text')));
        $descriptionfield->setCategory($atfcmcategory);

        $internalidfield = new CustomField();
        $internalidfield->setPlace(4);
        $internalidfield->setDefaultValue("");
        $internalidfield->setTooltip("");
        $internalidfield->setName("Internal Id");
        $internalidfield->setHidden(true);
        $internalidfield->setType($em->getRepository(CustomFieldType::class)->findOneBy(array('type'=>'string')));
        $internalidfield->setCategory($atfcmcategory);

        $normalratefield = new CustomField();
        $normalratefield->setPlace(5);
        $normalratefield->setDefaultValue("");
        $normalratefield->setTooltip("");
        $normalratefield->setName("Taux");
        $normalratefield->setType($em->getRepository(CustomFieldType::class)->findOneBy(array('type'=>'string')));
        $normalratefield->setCategory($atfcmcategory);

        $statefield = new CustomField();
        $statefield->setPlace(6);
        $statefield->setDefaultValue("");
        $statefield->setHidden(true);
        $statefield->setTooltip("");
        $statefield->setName("Etat");
        $statefield->setType($em->getRepository(CustomFieldType::class)->findOneBy(array('type'=>'string')));
        $statefield->setCategory($atfcmcategory);

        $atfcmcategory->setFieldname($namefield);
        $atfcmcategory->setReasonField($reasonfield);
        $atfcmcategory->setInternalId($internalidfield);
        $atfcmcategory->setDescriptionField($descriptionfield);
        $atfcmcategory->setNormalRateField($normalratefield);
        $atfcmcategory->setRegulationStateField($statefield);

        $em->persist($statefield);
        $em->persist($normalratefield);
        $em->persist($descriptionfield);
        $em->persist($namefield);
        $em->persist($reasonfield);
        $em->persist($internalidfield);

        $atfcmcategory->setTvs('');
        $atfcmcategory->setRegex('');

        return $atfcmcategory;
    }
}