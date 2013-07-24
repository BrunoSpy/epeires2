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
 * @ORM\Entity @ORM\Table(name="customfieldvalues")
 **/
class CustomFieldValue {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Event")
	 */
	protected $event;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomField")
	 */
	protected $customfield;
	
	/** @ORM\Column(type="string") */
	protected $value;
	
}