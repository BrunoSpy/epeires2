<?php
/**
 * Epeires 2
 *
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="customfields")
 **/
class CustomField {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** @ORM\Column(type="string", unique=true) */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="customfields")
	 */
	protected $category;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomFieldType")
	 */
	protected $type;
	
	/**
	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="customfield", cascade={"remove"})
	 */
	protected $values;
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getType(){
		return $this->type;
	}
	
}