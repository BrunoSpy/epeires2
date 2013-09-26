<?php
/** Epeires 2
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="eventupdates")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 */
class EventUpdate {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string")
	 * @Gedmo\Versioned
	 */
	protected $text;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Event", inversedBy="updates")
	 */
	protected $event;

	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
	/** @ORM\PrePersist */
	public function setCreatedOn(){
		$this->created_on = new \DateTime('NOW');
		$this->created_on->setTimeZone(new \DateTimeZone("UTC"));
	}
}