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
use Doctrine\Common\Collections\Collection;
use Zend\Filter\File\RenameUpload;

/**
 * @ORM\Entity
 * @ORM\Table(name="files")
 */
class File {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(name="path", type="string", unique=true)
	 */
	protected $path;

	/**
	 * @ORM\Column(name="mime_type", type="string")
	 */
	protected $mimetype;
	
	/**
	 * @ORM\Column(name="size", type="decimal")
	 */
	protected $size;
	
	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $name;
	
	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $reference;
	
	/** 
	 * @ORM\Column(type="string")
	 */
	protected $filename;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Event", inversedBy="files")
	 * @ORM\JoinTable(name="file_event")
	 */
	protected $events;
	
	/**
	 * 
	 * @param unknown $fileinfo
	 * @param unknown $already_exist
	 * @throws Exception
	 */
	public function __construct($fileinfo, $already_exist){
		$this->events = new \Doctrine\Common\Collections\ArrayCollection();
		$this->setSize($fileinfo['size']);
		$this->setMimetype($fileinfo['type']);
		$filter = new RenameUpload("./public/files/");
		$filter->setUseUploadName(true);
		$filter->setRandomize($already_exist);
		try {
			$targetname = $filter->filter($fileinfo);
		} catch (\Exception $e) {
			throw $e;
		}
		$name = substr($targetname['tmp_name'], 15);
		$this->setFilename($name);
		$this->setPath('/files/'.$name);
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getEvents(){
		return $this->events;
	}
	
	public function addEvent(Event $event){
		$this->events->add($event);
	}
	
	public function addEvents(Collection $events){
		foreach ($events as $event){
			$this->events->add($event);
		}
	}
	
	public function removeEvents(Collection $events){
		foreach ($events as $event){
			$this->events->removeElement($event);
		}
	}
	
	public function getSize(){
		return $this->size;
	}
	
	public function setSize($size){
		$this->size = $size;
	}
	
	public function getPath(){
		return $this->path;
	}
	
	public function setPath($path){
		$this->path = $path;
	}
	
	public function setMimetype($mimetype){
		$this->mimetype = $mimetype;
	}
	
	public function getMimetype(){
		return $this->mimetype;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setFilename($filename){
		$this->filename = $filename;
	}
	
	public function getFilename(){
		return $this->filename;
	}
	
	public function setReference($reference){
		$this->reference = $reference;
	}
	
	public function getReference(){
		return $this->reference;
	}
}