<?php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\Form\Element\File;
use Zend\Form\Element\Text;

class FileFieldset extends Fieldset {
	
	
	public function addFile($count = 1){
		
		$fieldset = new Fieldset('fichier'.$count);
		
		$file = new File('file');
		$file->setLabel(' ');
		 
		$name = new Text('name');
		$name->setLabel('Fichier '.$count.' :');
		$name->setAttribute('placeholder', 'Titre (facultatif)');
		$fieldset->add($name);
		$fieldset->add($file);
		
		$this->add($fieldset);
	}
	
}