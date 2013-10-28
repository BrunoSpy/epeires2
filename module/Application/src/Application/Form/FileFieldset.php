<?php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\Form\Element\File;
use Zend\Form\Element\Text;

class FileFieldset extends Fieldset {
	
	
	public function addFile($count = 1){
		$file = new File('file'.$count);
		$file->setLabel(' ')
		->setAttribute('id', 'file'.$count);
		 
		$name = new Text('name'.$count);
		$name->setLabel('Fichier '.$count.' :')->setAttribute('id', 'name'.$count);
		$name->setAttribute('placeholder', 'Titre (facultatif)');
		$this->add($name);
		$this->add($file);
	}
	
}