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

    /*
     * Alarmes futures non acquittées.
     * Seules les alarmes de l'organisation de l'utilisateur sont envoyées.
     * Si lastupdate contient une date valide, envoit les alarmes modifiées depuis lastupdate, y compris celles acquittées
     * Dans tous les cas : nécessite d'être identifié.
     */
    public function getalarmsAction(){
	$alarms = array();
	if($this->zfcUserAuthentication()->hasIdentity()) {
		$organisation = $this->zfcUserAuthentication()->getIdentity()->getOrganisation()->getId();
		$lastupdate = $this->params()->fromQuery('lastupdate', null);
		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$eventservice = $this->getServiceLocator()->get('EventService');
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
		$qbEvents = $objectManager->createQueryBuilder();
		$qbEvents->select(array('e', 'cat'))
			->from('Application\Entity\Event', 'e')
			->innerJoin('e.category', 'cat')
			->andWhere($qbEvents->expr()->eq('e.organisation', $organisation))
			->andWhere('cat INSTANCE OF Application\Entity\AlarmCategory')
			->andWhere($qbEvents->expr()->in('e.status', '?2'));		//statut alarme
		if($lastupdate && $lastupdate != 'undefined'){
			$from = new \DateTime($lastupdate);
			$from->setTimezone(new \DateTimeZone("UTC"));
			//uniquement les alarmes créés et modifiées à partir de lastupdate
			$qbEvents->andWhere($qbEvents->expr()->gte('e.last_modified_on', '?1'))
			->setParameters(array(2 => array(1,2,3,4),
					1 => $from->format('Y-m-d H:i:s')));
		} else {
			$qbEvents->andWhere($qbEvents->expr()->gte('e.startdate', '?1')) //date de début dans le future
			->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
					2 => array(1,2)));
		}
		$result = $qbEvents->getQuery()->getResult();
		foreach($result as $alarm){
			$alarmjson = array();
			$alarmjson['id'] = $alarm->getId();
			$alarmjson['datetime'] = $alarm->getStartDate()->format(DATE_RFC2822);
			$alarmjson['status'] = $alarm->getStatus()->getId();
			$parentname = $eventservice->getName($alarm->getParent());
			$alarmname = $eventservice->getName($alarm);
			$alarmcomment = "";
			foreach($alarm->getCustomFieldsValues() as $value){
				if($value->getCustomField()->getId() == $alarm->getCategory()->getTextfield()->getId()){
					$alarmcomment = nl2br($value->getValue());
				}
			}
			$alarmjson['text'] = "<b>Alerte pour ".$parentname."</b><br />"
					. $alarmname.(strlen($alarmcomment) > 0 ? " : <br />".$alarmcomment : "");
			
			$alarms[] = $alarmjson;
		}
	}
	if(empty($alarms)){
		$this->getResponse()->setStatusCode(304);
		return;
	}
	return new JsonModel($alarms);
    }
    
    /**
     * Change le statut d'une alarme à Terminé
     * Nécessite d'être identifié et d'avoir les droits events.status
     */
    public function confirmAction(){
	if($this->zfcUserAuthentication()->hasIdentity()){
		if($this->isGranted('events.status')){
			$id = $this->params()->fromQuery('id', null);
			$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$messages = array();
			if($id){
				$alarm = $objectManager->getRepository('Application\Entity\Event')->find($id);
				if($alarm){
					$status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array('open' => false, 'defaut' => true));
					//ne pas enregistrer si pas de changement
					if($alarm->getStatus()->getId() != $status->getId()){
						$alarm->setStatus($status);
						$objectManager->persist($alarm);
						try {
							$objectManager->flush();
							$messages['success'][] = "Alarme acquittée";
						} catch (\Exception $e) {
							$messages['error'][] = $e->getMessage();
						}
					}
				} else {
					$messages['error'][] = "Aucune alarme trouvée";
				}
			} else {
				$messages['error'][] = "Argument incorrect";
			}
		} else {
			$messages['error'][] = "Droits insuffisants pour acquitter l'alarme";
		}
	} else {
		$messages['error'][] = "Utilisateur non identifié, modification impossible";
	}
	return new JsonModel($messages);
    }
}
