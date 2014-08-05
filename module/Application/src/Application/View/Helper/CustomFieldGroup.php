<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CustomFieldGroup extends AbstractHelper {
	
	public function __invoke($element){
				
		$view = $this->getView();
		
		if($element instanceof \Zend\Form\Element\Select){
			$html =  $view->controlGroup(
						$view->formLabel($element->setLabelAttributes(array('class'=>'control-label'))),
						$view->formSelect($element));
		} elseif ($element instanceof \Zend\Form\Element\Checkbox){
			$element->setUseHiddenElement(true);
			$html =  $view->controlGroup(
						$view->formLabel($element->setLabelAttributes(array('class'=>'control-label'))),
						$view->formCheckbox($element));
		} elseif ($element instanceof \Zend\Form\Element\Text){
			$html =  $view->controlGroup(
						$view->formLabel($element->setLabelAttributes(array('class'=>'control-label'))),
						$view->formText($element));
		} elseif ($element instanceof \Zend\Form\Element\Textarea){
			$html =  $view->controlGroup(
						$view->formLabel($element->setLabelAttributes(array('class'=>'control-label'))),
						$view->formTextarea($element));
		} else {
			$html =  $view->formRow($element);
		}
		
		return $html;
		
	}
	
}