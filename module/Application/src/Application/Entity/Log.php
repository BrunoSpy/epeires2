<?php
namespace Application\Entity;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 * @ORM\HasLifecycleCallbacks
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
	
	/** 
	 * @ORM\PostLoad
	 */
	public function doCorrectUTC(){
		if($this->loggedAt){
			$this->loggedAt->setTimezone(new \DateTimeZone("UTC"));
			$offset = date("Z");
			$this->loggedAt->add(new \DateInterval("PT".$offset."S"));
		}
	}
	
}