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

  public function saveAction() {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $json = array();
        $messages = array();
        if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
                
                $datas = $this->getForm();
                $form = $datas['form'];
                $form->setData($post);
                $form->setPreferFormInputFilter(true);
                if($form->isValid()){
                    $event = $form->getData();
                    $alarm = array();
                    $alarm['datetime'] = $event->getStartDate()->format(DATE_RFC2822);
                    $alarm['name'] = $post['custom_fields'][$event->getCategory()->getFieldname()->getId()];
                    $alarm['comment'] = $post['custom_fields'][$event->getCategory()->getTextfield()->getId()];
                    $json['alarm'] = $alarm;
                } else {
                    $this->processFormMessages($form->getMessages(), $messages);
                }
                
        }
        $json['messages'] = $messages;
        return new \Zend\View\Model\JsonModel($json);
    }

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
	} else {
            //alarm : punctual, impact : info, organisation, category : alarm, status : open (closed when aknowledged)
            //all these information are just here to validate form
            $form->get('impact')->setValue(5);
            $form->get('punctual')->setValue(true);
            $form->get('category')->setValue($alarmcat->getId());
            $form->get('status')->setValue($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('open'=>true, 'defaut'=>true))->getId());
            if($this->zfcUserAuthentication()->hasIdentity()){
                $form->get('organisation')->setValue($this->zfcUserAuthentication()->getIdentity()->getOrganisation()->getId());
            } else {
                throw new \ZfcRbac\Exception\UnauthorizedException();
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
