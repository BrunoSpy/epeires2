<?php
/** 
 * Epeires 2
*
* Catégorie d'évènements.
* Peut avoir une catégorie parente.
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