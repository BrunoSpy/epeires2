<?php

/**
* Epeires 2
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/

namespace Application\Controller;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

use Application\Form\CustomFieldset;
use Application\Entity\Event;
/**
*
* @author Bruno Spyckerelle
*/
class AlarmController extends FormController {


  public function formAction(){
	$request = $this->getRequest();
	$viewmodel = new ViewModel();
	//disable layout if request by Ajax
	$viewmodel->setTerminal($request->isXmlHttpRequest());

	$alarmid = $this->params()->fromQuery('id', null);
	
	$getform = $this->getForm($alarmid);

	$viewmodel->setVariables(array('form' => $getform['form'],'alarmid'=>$alarmid));
	return $viewmodel;
  }
  
  private function getForm($alarmid = null){
	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	$alarm = new Event();
	
	$builder = new AnnotationBuilder();
	$form = $builder->createForm($alarm);
	$form->setHydrator(new DoctrineObject($objectManager))
	     ->setObject($alarm);
	
	$alarmcat = $objectManager->getRepository('Application\Entity\AlarmCategory')->findAll()[0]; //TODO
		
	$form->add(new CustomFieldset($this->getServiceLocator(), $alarmcat->getId()));
	
	if($alarmid){
		$alarm = $objectManager->getRepository('Application\Entity\Event')->find($alarmid);
		if($alarm) {
			$form->bind($alarm);
			$form->setData($alarm->getArrayCopy());
		}
	}
	
	$form->add(array(
		'name' => 'submit',
		'attributes' => array(
			'type' => 'submit',
			'value' => 'Enregistrer',
			'class' => 'btn btn-primary',
		),
	));
	
	return array('form' => $form, 'alarm'=>$alarm);
    }

}
