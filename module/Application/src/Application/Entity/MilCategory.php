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
     * Displayed zones, must be included in <$filter>*
     */
    protected $zonesRegex;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"Filtre d'import :"})
     * Filter applied at import
     */
    protected $filter;
    
    /** 
     * @ORM\Column(type="boolean")
     * @Annotation\Required(false)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Actualiser avec NM B2B :"})
     */
    protected $nmB2B = false;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastUpdateDate;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $lastUpdateSequence;
    
    /** 
     * @ORM\Column(type="boolean")
     * @Annotation\Required(false)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Page Zones Mil :"})
     */
    protected $onMilPage = false;
    
    /**
    * 
    * @ORM\OneToOne(targetEntity="CustomField")
    */
    protected $upperLevelField;
    
    /**
    * 
    * @ORM\OneToOne(targetEntity="CustomField")
    */
    protected $lowerLevelField;
    
    public function setNMB2B($nmb2b){
        $this->nmB2B = $nmb2b;
    }
    
    public function isNMB2B(){
        return $this->nmB2B;
    }
    
    public function isOnMilPage(){
        return $this->onMilPage;
    }
    
    public function setOnMilPage($onmilpage){
        $this->onMilPage = $onmilpage;
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
    
    public function setFilter($filter){
        $this->filter = $filter;
    }
    
    public function getFilter(){
        return $this->filter;
    }
    
    public function setUpperLevelField($upperlevelfield){
        $this->upperLevelField = $upperlevelfield;
    }
    
    public function getUpperLevelField(){
        return $this->upperLevelField;
    }
    
    public function setLowerLevelField($lowerlevelfield){
        $this->lowerLevelField = $lowerlevelfield;
    }
    
    public function getLowerLevelField(){
        return $this->lowerLevelField;
    }
    
    public function getArrayCopy() {
	$object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
	return $object_vars;
    }
	
}