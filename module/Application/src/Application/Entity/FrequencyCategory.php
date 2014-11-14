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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 **/
class FrequencyCategory extends Category{
	
        /**
         * @ORM\Column(type="boolean")
         */
        protected $defaultfrequencycategory = false;


        /**
	 * Ref to the field used to store the state of the frequency
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $statefield;
	
	/**
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $currentcovfield;

	/**
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $frequencyfield;
	
	/**
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $otherfrequencyfield;
	
        public function isDefaultFrequencyCategory(){
            return $this->defaultfrequencycategory;
        }
        
        public function setDefaultFrequencyCategory($default){
            $this->defaultfrequencycategory = $default;
        }
        
        /**
         * True : unavailable
         * False : avalaible
         * @return type
         */
	public function getStateField(){
		return $this->statefield;
	}
	
	public function setStateField($statefield){
		$this->statefield = $statefield;
	}
	
	public function getFrequencyField(){
		return $this->frequencyfield;
	}
	
	public function setFrequencyField($frequencyfield){
		$this->frequencyfield = $frequencyfield;
	}
	
        /**
         * 0 : normale
         * 1 : secours
         */
	public function getCurrentAntennaField(){
		return $this->currentcovfield;
	}
	
	public function setCurrentAntennaField($currentcovfield){
		$this->currentcovfield = $currentcovfield;
	}
	
	public function getOtherFrequencyField(){
		return $this->otherfrequencyfield;
	}
	
	public function setOtherFrequencyField($otherfrequencyfield){
		$this->otherfrequencyfield = $otherfrequencyfield;
	}
}