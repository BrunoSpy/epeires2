<?php

/**
* Epeires 2
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/

namespace Application\Controller;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
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
                    $offset = date("Z");
                    $startdate = clone $event->getStartDate();
                    $startdate->setTimezone(new \DateTimeZone("UTC"));
                    $startdate->add(new \DateInterval("PT".$offset."S"));
                    $event->setStartDate($startdate);
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
			'value' => 'Ajouter',
			'class' => 'btn btn-primary',
		),
	));
	
	return array('form' => $form, 'alarm'=>$alarm);
    }
    
    public function deleteAction(){
	    $alarmid = $this->params()->fromQuery('id', null);
	    $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	    $messages = array();
	    
	    if($alarmid){
		    $alarm = $objectManager->getRepository('Application\Entity\Event')->find($alarmid);
		    if($alarm){
			$objectManager->remove($alarm);
			try {
				$objectManager->flush();
				$messages['success'][] = "Alarme supprimée";
			} catch (\Exception $e) {
				$messages['error'][] = $e->getMessage();
			}
		    } else {
			$messages['error'][] = "Aucune alarme correspondante trouvée";    
		    }
	    } else {
		$messages['error'][] = "Aucune alarme à supprimer";    
	    }
	    return new JsonModel($messages);
    }

    public function getalarmsAction(){
	$alarms = array();
	$lastupdate = $this->params()->fromQuery('lastupdate', null);
	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	$eventservice = $this->getServiceLocator()->get('EventService');
	$now = new \DateTime('NOW');
	$now->setTimezone(new \DateTimeZone("UTC"));
	$qbEvents = $objectManager->createQueryBuilder();
	$qbEvents->select(array('e', 'cat'))
		->from('Application\Entity\Event', 'e')
		->innerJoin('e.category', 'cat')
		->andWhere('cat INSTANCE OF Application\Entity\AlarmCategory')
		->andWhere($qbEvents->expr()->gte('e.startdate', '?1')) //date de début dans le future
		->andWhere($qbEvents->expr()->in('e.status', '?2'))		//alarme nouvelle
	->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
				2 => array(1,2)));
	if($lastupdate && $lastupdate != 'undefined'){
		$from = new \DateTime($lastupdate);
		//uniquement les alarmes créés et modifiées à partir de lastupdate
		$qbEvents->andWhere($qbEvents->expr()->gte('e.last_modified_on', '?3'))
		->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
				2 => array(1,2),
				3 => $from->format('Y-m-d H:i:s')));
	}
	$result = $qbEvents->getQuery()->getResult();
	foreach($result as $alarm){
		$alarmjson = array();
		$alarmjson['id'] = $alarm->getId();
		$alarmjson['datetime'] = $alarm->getStartDate()->format(DATE_RFC2822);
		$parentname = $eventservice->getName($alarm->getParent());
		$alarmname = $eventservice->getName($alarm);
		$alarmcomment = "";
		foreach($alarm->getCustomFieldsValues() as $value){
			if($value->getCustomField()->getId() == $alarm->getCategory()->getTextfield()->getId()){
				$alarmcomment = nl2br($value->getValue());
			}
		}
		$alarmjson['text'] = "<b>Alerte pour ".$parentname."</b><br />"
				. $alarmname." : <br />".$alarmcomment;
		
		$alarms[] = $alarmjson;
	}
	return new JsonModel($alarms);
    }
    
    public function confirmAction(){
	$id = $this->params()->fromQuery('id', null);
	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	$messages = array();
	if($id){
		$alarm = $objectManager->getRepository('Application\Entity\Event')->find($id);
		if($alarm){
			$status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array('open' => false, 'defaut' => true));
			$alarm->setStatus($status);
			$objectManager->persist($alarm);
			try {
				$objectManager->flush();
				$messages['success'][] = "Alarme acquittée";
			} catch (\Exception $e) {
				$messages['error'][] = $e->getMessage();
			}
		} else {
			$messages['error'][] = "Aucune alarme trouvée";
		}
	} else {
		$messages['error'][] = "Argument incorrect";
	}
	return new JsonModel($messages);
    }
}
