<?php
namespace Application\Entity;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 */
class Log extends AbstractLogEntry{

	/**
	 * UTC
	 */
	public function setLoggedAt()
	{
		$this->loggedAt = new \DateTime();
		$this->loggedAt->setTimezone(new \DateTimeZone("UTC"));
	}
	
}