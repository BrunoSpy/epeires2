<?php
/** Epeires 2
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="files")
 * @Gedmo\Uploadable(path="/");
 */
class File {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(name="path", type="string")
	 * @Gedmo\UploadableFilePath
	 */
	protected $path;

	/**
	 * @ORM\Column(name="mime_type", type="string")
	 * @Gedmo\UploadableFileMimeType
	 */
	protected $mimetype;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Event", inversedBy="files")
	 * @ORM\JoinTable(name="file_event")
	 */
	protected $events;
	
	
	public function __construct(){
		$this->events = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
}