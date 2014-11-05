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
class AlarmCategory extends Category{
	
	/**
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $namefield;
	
	/**
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $textfield;
	
        /**
         * Field used to store delta relative to start date
         * @ORM\OneToOne(targetEntity="CustomField")
         */
        protected $deltabeginField;
        
        /**
         * Field used to store delta relative to end date
         * @ORM\OneToOne(targetEntity="CustomField")
         */
        protected $deltaendField;


        public function getNamefield(){
		return $this->namefield;
	}
	
	public function setNamefield($namefield){
		$this->namefield = $namefield;
	}
	
	public function getTextfield(){
		return $this->textfield;
	}
	
	public function setTextfield($textfield){
		$this->textfield = $textfield;
	}
	
        public function setDeltaBeginField($deltafield){
            $this->deltabeginField = $deltafield;
        }
        
        public function getDeltaBeginField(){
            return $this->deltabeginField;
        }
        
        public function setDeltaEndField($deltafield){
            $this->deltaendField = $deltafield;
        }
        
        public function getDeltaEndField(){
            return $this->deltaendField;
        }
}