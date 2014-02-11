<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="Application\Repository\PredefinedEventRepository")
 **/
class PredefinedEvent extends AbstractEvent{

	/** @ORM\Column(type="string", nullable=true)
	 * @Annotation\Type("Zend\Form\Element\Text")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
 	
 	/** @ORM\Column(type="boolean")
	 * @Annotation\Type("Zend\Form\Element\Checkbox")
	 * @Annotation\Options({"label":"Liste :"})
	 */
 	protected $listable;
 	
 	/** @ORM\Column(type="boolean")
	 * @Annotation\Type("Zend\Form\Element\Checkbox")
	 * @Annotation\Options({"label":"Recherche :"})
	 */
 	protected $searchable;
 		 	
 	public function __construct(){
 		parent::__construct();
 	}
 	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function isListable(){
		return $this->listable;
	}
	
	public function setListable($listable){
		$this->listable = $listable;
	}
	
	public function isSearchable(){
		return $this->searchable;
	}
	
	public function setSearchable($searchable){
		$this->searchable = $searchable;
	}
	
	public function getPlace(){
		return $this->place;
	}
	
	public function setPlace($place){
		$this->place = $place;
	}

}