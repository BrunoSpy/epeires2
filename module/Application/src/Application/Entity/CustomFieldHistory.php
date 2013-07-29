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
 * @ORM\Table(name="customfieldhistory")
 * @ORM\HasLifecycleCallbacks
 **/
class CustomFieldHistory {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomFieldValue")
	 */
	protected $customfieldvalue;
	
	/** @ORM\Column(type="string") */
	protected $old_value;
	
	/** @ORM\Column(type="string") */
	protected $new_value;
	
	/** @ORM\Column(type="datetime") */
	protected $modified_on;
	
	/** @ORM\PrePersist */
	public function setModifiedOn(){
		$this->modified_on = new \DateTime('NOW');
	}
	
}