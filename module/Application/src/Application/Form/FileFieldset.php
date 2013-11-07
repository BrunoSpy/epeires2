<?php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\Form\Element\File;
use Zend\Form\Element\Text;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputFilterInterface;

class FileFieldset extends Fieldset {
	
	
	public function addFile($count = 1){
		
		$fieldset = new Fieldset('fichier'.$count);
		
		$file = new File('file'.$count);
		$file->setLabel(' ');
		 
		$name = new Text('name'.$count);
		$name->setLabel('Fichier '.$count.' :');
		$name->setAttribute('placeholder', 'Titre');
		$name->setAttribute('class', 'input-medium');
		
		$ref = new Text('reference'.$count);
		$ref->setAttribute('placeholder', 'Ref.');
		$ref->setAttribute('class', 'input-mini');
		
		$fieldset->add($ref);
		$fieldset->add($name);
		$fieldset->add($file);
		
		$this->add($fieldset);
		
		
	}
	
}