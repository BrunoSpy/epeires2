<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity @ORM\Table(name="events")
 **/
class Event {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/** @ORM\Column(type="boolean") */
	protected $punctual;

	/** @ORM\ManyToOne(targetEntity="Status") */
	protected $status;
	
	/** @ORM\ManyToOne(targetEntity="Event") */
	protected $parent;
	
	/** @ORM\ManyToOne(targetEntity="Impact") */
	protected $impact;
	
	/** @ORM\Column(type="datetime") */
	protected $start_date;
	
	/** @ORM\Column(type="datetime") */
	protected $end_date;
	
	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
	/** @ORM\Column(type="datetime") */
	protected $last_modified_on;
	
	/** @ORM\ManyToOne(targetEntity="Category") */
	protected $category;
	
	public function isPunctual() {
		return $punctual;
	}
	
	public function setPunctual($punctual){
		$this->punctual = $punctual;
	}
	/** @ORM\PrePersist */
	public function setCreatedOn(){
		$this->created_on = new \DateTime('NOW');
	}
}