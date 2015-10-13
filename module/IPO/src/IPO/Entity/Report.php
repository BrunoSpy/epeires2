<?php

/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace IPO\Entity;

use Zend\Form\Annotation;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="reports")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * 
 * Rapport IPO
 *
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class Report {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Numéro de la semaine :"})
     */
    protected $week;
    
    /**
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Année :"})
     */
    protected $year;
    
    /**
     * @ORM\ManyToMany(targetEntity="Element")
     */
    protected $elements;

    /** @ORM\Column(type="datetime") */
    protected $created_on;
    
    public function __construct() {
        $this->elements = new ArrayCollection();
    }

    public function getId() {
    	return $this->id;
    }
    
    public function setName($name){
    	$this->name = $name;
    }
  
    public function getName(){
    	return $this->name;
    }
    
    public function setWeek($week){
    	$this->week = $week;
    }

    public function getWeek(){
    	return $this->week;
    }
    
    public function setYear($year){
    	$this->year = $year;
    }
    
    public function getYear(){
    	return $this->year;
    }
    
    public function getStartDate(){
    	$now = new \DateTime();
    	$now->setTimezone(new \DateTimeZone("UTC"));
    	$now->setISODate($this->year, $this->week);
    	$now->setTime(0, 0, 0);
    	return $now;
    }
    
    public function getEndDate(){
    	$now = new \DateTime();
    	$now->setTimezone(new \DateTimeZone("UTC"));
    	$now->setISODate($this->year, $this->week);
    	$now->setTime(23, 59, 59);
    	$now->modify('+ 6 days');
    	return $now;
    }
    
    public function getElements() {
    	return $this->elements;
    }
    
    public function addElement(Element $element) {
    	$this->elements->add($element);
    }
    
    public function addElements(Collection $elements){
    	foreach ($elements as $element) {
    		$this->elements->add($element);
    	}
    }
    
    public function removeElements(Collection $elements){
    	foreach ($elements as $element) {
    		$this->elements->removeElement($element);
    	}
    }
    
    /** @ORM\PrePersist */
    public function setCreatedOn() {
    	$this->created_on = new \DateTime('NOW');
    	$this->created_on->setTimeZone(new \DateTimeZone("UTC"));
    }
    
    public function getCreatedOn(){
    	return $this->created_on;
    }
    
    public function getArrayCopy() {
        $object_vars = get_object_vars($this);
        $elmts = array();
        foreach ($this->elements as $element) {
            $elmts[] = $element->getId();
        }
        $object_vars['elements'] = $elmts;
        return $object_vars;
    }

}
