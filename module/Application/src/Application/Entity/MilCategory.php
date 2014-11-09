<?php
/** 
 * Epeires 2
*
* Catégorie d'évènements : zones militaires.
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 **/
class MilCategory extends Category{
	
    /** 
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"Zones associées :"})
     */
    protected $zonesRegex;
    
    /** 
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Actualiser avec AUP :"})
     */
    protected $aup;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastUpdateDate;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $lastUpdateSequence;
    
    public function setAUP($aup){
        $this->aup = $aup;
    }
    
    public function isAUP(){
        return $this->aup;
    }
    
    public function setLastUpdateDate($update){
        $this->lastUpdateDate = $update;
    }
    
    public function getLastUpdateDate(){
        return $this->lastUpdateDate;
    }
    
    public function setLastUpdateSequence($sequence){
        $this->lastUpdateSequence = $sequence;
    }
    
    public function getLastUpdateSequence(){
        $this->lastUpdateSequence;
    }
    
    public function setZonesRegex($regex){
        $this->zonesRegex = $regex;
    }
    
    public function getZonesRegex(){
        return $this->zonesRegex;
    }
    
    public function getArrayCopy() {
	$object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
	return $object_vars;
    }
	
}