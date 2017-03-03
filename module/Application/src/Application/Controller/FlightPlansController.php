<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Controller;

use Core\Controller\AbstractEntityManagerAwareController;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DateTime;
use DateInterval;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Stdlib\Parameters;

use Application\Services\CustomFieldService;
use Application\Form\CustomFieldset;

use Application\Entity\FlightPlanCategory;
use Application\Entity\Organisation;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
/**
 *
 * @author Loïc Perrin
 */
class FlightPlansController extends AbstractEntityManagerAwareController
{
    protected $em, $cf, $repo, $form;

    public function __construct(EntityManager $em, CustomFieldService $cf)
    {
        parent::__construct($em);
        $this->em = $this->getEntityManager();
        $this->cf = $cf;
    }

    private function getCatId() {
        $cat = $this->getCat();
        return (is_a($cat, FlightPlanCategory::class)) ? $cat->getId() : 0;
    }

    private function getCat() {
        $cat = $this->em->getRepository(FlightPlanCategory::class)->findAll();
        return (is_array($cat)) ? end($cat) : null;
    }

    private function getFp($start, $end, $sar = false)
    {
        $allFpEvents = $this->em->getRepository(Event::class)->getFlightPlanEvents($start, $end);
        $fpEvents = [];

        foreach ($allFpEvents as $fpEvent) 
        {
            $push = false;
            $cat = $fpEvent->getCategory();
            $ev = [
                'id' => $fpEvent->getId(),
                'start_date' => $fpEvent->getStartDate(),
                'end_date' => $fpEvent->getEndDate()
            ];
            foreach ($fpEvent->getCustomFieldsValues() as $value) 
            {
                $namefield = $value->getCustomField()->getName();
                $valuefield = $value->getValue();
                $ev[$namefield] = $valuefield;

                if($namefield == 'Alerte') 
                {
                    if($sar == true && $valuefield > 0) {
                        $push = true;
                        $altEv = $this->em->getRepository(Event::class)->findOneBy(['id' => $valuefield]);
                        foreach ($altEv->getCustomFieldsValues() as $altvalue) {
                            $ev[$altvalue->getCustomField()->getName()] = $altvalue->getValue();
                        } 
                    }
                    if($sar == false && !$valuefield) $push = true;  
                }
            }
            if($push == true) $fpEvents[] = $ev;
        }
        return $fpEvents;
    }

    private function getAlertIdFromFp($fp) 
    {
        if (is_a($fp, Event::class)) 
        {
            // $alertev = $this->em->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\AfisCategory');
            // On va chercher l'id de l'event d'alerte
            foreach ($fp->getCustomFieldsValues() as $customfieldvalue) 
            {
                // echo $this->getCat()->getAlertfield()->getId();
                // echo $customfieldvalue->getCustomField()->getId();
                if ($customfieldvalue->getCustomField()->getId() == $this->getCat()->getAlertfield()->getId())
                {
                    $alertid = $customfieldvalue->getValue();        
                }
            }            
        }
        return $alertid;
    }

    public function indexAction()
    {
        if (!$this->authFlightPlans('read')) return new JsonModel();
        return (new ViewModel())
            ->setVariables([
                'cat' => $this->getCatId(),
            ]);
    }
    
    public function sarAction()
    {
        if (!$this->authFlightPlans('read')) return new JsonModel();

        return (new ViewModel())
            ->setTemplate('application/flight-plans/index')
            ->setVariables([
                'cat' => $this->getCatId(),
            ]);
    }   

    private function getFields() {
        $cf = $this->em->getRepository('Application\Entity\CustomField')->findBy(['category' => $this->getCatId()]);
        $fields = [];
        foreach ($cf as $c) {
           $fields[] = $c->getName();
        }
        return $fields;
    }

    public function getAction() 
    {
        if (!$this->authFlightPlans('read')) return new JsonModel();
        $post = $this->getRequest()->getPost();
        //TODO : tester validité date
        if (isset($post['date']) && $post['date'] != '') {
            $start = new DateTime($post['date']); 
            $end = (new DateTime($post['date']))->add(new DateInterval('P1D'));
        } else {
            $start = (new DateTime())->setTime(0,0,0);
            $end = (new DateTime())->setTime(0,0,0)->add(new DateInterval('P1D'));
        }

        $flightplans = (isset($post['sar']) && $post['sar'] == '1') ? $this->getFp($start, $end, true) : $this->getFp($start, $end);

        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setVariables([
                    'fields' => $this->getFields(),
                    'flightplans' => $flightplans
                ]);
    }   

    public function endAction() {
        if (!$this->authFlightPlans('read')) return new JsonModel();
        $post = $this->getRequest()->getPost();
        $msgType = 'error';
        $id = (int) $post['id'];
        if($id > 0) 
        {
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));

            $event = $this->em->getRepository(Event::class)->find($id);
            $endstatus = $this->em->getRepository('Application\Entity\Status')->find('3');
            $event->setStatus($endstatus);
            $event->setEnddate($now);
            $this->em->persist($event);

            // cloture de l'alerte si elle existe
            $alertev = $this->em->getRepository(Event::class)->find($this->getAlertIdFromFp($event));
            if ($alertev) 
            {
                $alertev->setStatus($endstatus);
                $alertev->setEnddate($now);
                $this->em->persist($event);
            }
            try 
            {
                $this->em->flush();
                $msgType = 'success';
                $msg = "Clôture du plan de vol.";
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        } else $msg = "Impossible de trouver le vol.";

        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg
        ]);
    }

    private function authFlightPlans($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('flightplans.'.$action)) ? false : true;
    }

    public function triggerAlertAction() 
    {
        if (!$this->authFlightPlans('read')) return new JsonModel();

        $msgType = 'error';

        $req = $this->getRequest()->getPost();
        $id = (int) $req['id'];
        $type = $req['type'];
        $cause = $req['cause'];
        if ($id > 0) 
        {
            $fp = $this->em->getRepository(Event::class)->find($id);
            if($fp) 
            {
                // $alertev = $this->em->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\AfisCategory');
                // On va chercher l'id de l'event d'alerte
                foreach ($fp->getCustomFieldsValues() as $customfieldvalue) 
                {
                    // echo $this->getCat()->getAlertfield()->getId();
                    // echo $customfieldvalue->getCustomField()->getId();
                    if ($customfieldvalue->getCustomField()->getId() == $this->getCat()->getAlertfield()->getId())
                    {
                        $alertid = $customfieldvalue->getValue();        
                    }
                }            

                $alertev = $this->em->getRepository(Event::class)->find($alertid);
                //modification
                if ($alertev) 
                {
                    //TODO attention si plusieurs cat
                    $alertcat = $this->em->getRepository('Application\Entity\AlertCategory')->findAll();
                    $typefieldid = $alertcat[0]->getTypeField()->getId();
                    $causefieldid = $alertcat[0]->getCauseField()->getId();
                    foreach ($alertev->getCustomFieldsValues() as $customfieldvalue) 
                    {
                        if ($customfieldvalue->getCustomField()->getId() == $typefieldid)
                        {
                            $typefield = $customfieldvalue;
                        }
                        if ($customfieldvalue->getCustomField()->getId() == $causefieldid)
                        {
                            $causefield = $customfieldvalue;
                        }
                    }
                    if (isset($typefield) && isset($causefield)) 
                    {
                        $typefield->setValue($type);
                        $causefield->setValue($cause);  
                        $this->em->persist($typefield);
                        $this->em->persist($causefield);
                        try {
                            $this->em->flush();
                            $msgType = 'success';
                            $msg = "Alerte modifiée.";
                        } catch (\Exception $e) {
                            $msg = $e->getMessage();
                        }                
                    } else {
                        $msg = "Impossible de trouver le champ correspondant au type d'alerte.";
                    } 

                } 
                // création
                else 
                {
                    $now = new \DateTime('NOW');
                    $now->setTimezone(new \DateTimeZone("UTC"));

                    $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['id' => 1]);
                    // crétation de l'evenement d'alerte
                    $event = new Event();
                    $event->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
                    $event->setStartdate($now);
                    $event->setImpact($this->em->getRepository('Application\Entity\Impact')->find('3'));
                    $event->setPunctual(false);
                    $event->setOrganisation($organisation);
                    $event->setAuthor($this->zfcUserAuthentication()->getIdentity());
                    
                    $categories = $this->em->getRepository('Application\Entity\AlertCategory')->findAll();

                    if ($categories) 
                    {
                        $event->setCategory($categories[0]);

                        $catalert = $categories[0];
                        $typefieldvalue = new CustomFieldValue();
                        $typefieldvalue->setCustomField($catalert->getTypeField());
                        $typefieldvalue->setValue($type);
                        $typefieldvalue->setEvent($event);
                        $event->addCustomFieldValue($typefieldvalue);
                        $this->em->persist($typefieldvalue);

                        $causefieldvalue = new CustomFieldValue();
                        $causefieldvalue->setCustomField($catalert->getCauseField());
                        $causefieldvalue->setValue($cause);
                        $causefieldvalue->setEvent($event);
                        $event->addCustomFieldValue($causefieldvalue);
                        $this->em->persist($causefieldvalue);
                        //on ajoute les valeurs des champs persos
                        if (isset($post['custom_fields'])) {
                            foreach ($post['custom_fields'] as $key => $value) {
                                // génération des customvalues si un customfield dont le nom est $key est trouvé
                                $customfield = $this->em->getRepository('Application\Entity\CustomField')->findOneBy(array(
                                    'id' => $key
                                ));
                                if ($customfield) {
                                    if (is_array($value)) {
                                        $temp = "";
                                        foreach ($value as $v) {
                                            $temp .= (string) $v . "\r";
                                        }
                                        $value = trim($temp);
                                    }
                                    $customvalue = new CustomFieldValue();
                                    $customvalue->setEvent($event);
                                    $customvalue->setCustomField($customfield);
                                    $event->addCustomFieldValue($customvalue);
                                    
                                    $customvalue->setValue($value);
                                    $this->em->persist($customvalue);
                                }
                            }
                        }
                        //et on sauve le tout
                        $this->em->persist($event);
                        try {
                            $this->em->flush();
                            foreach ($fp->getCustomFieldsValues() as $customfieldvalue) 
                            {
                                if ($customfieldvalue->getCustomField()->getId() == $this->getCat()->getAlertfield()->getId())
                                {
                                    $customfieldvalue->setValue($event->getId());
                                    $this->em->persist($customfieldvalue);
                                }
                            }
                            $this->em->flush();
                            $msgType = 'success';
                            $msg = "Alerte créée.";
                        } catch (\Exception $e) {
                            $msg = $e->getMessage();
                        }
                    } else {
                        $msg = "Impossible de créer l'alerte, pas de catégorie alerte créée.";
                    }
                }
            } else {
                $msg = "Requête incorrecte, impossible de trouver le plan de vol correspondant.";
            }
        } else {
            $msg = "Requête incorrecte, pas d'identifiant de plan de vol valide.";
        }
        return new JsonModel([
            'type' => $msgType, 
            'msg' => $msg
        ]);
    }

    // private function validateFlightPlan($params) 
    // {
    //     if (!is_a($params, Parameters::class) && !is_array($params)) return false;

    //     $id = intval($params['id']);
    //     $fp = ($id) ? $this->repo->find($id) : new FlightPlan();
    //     $this->form->setData($params);

    //     if (!$this->form->isValid()) $ret = false;
    //     else 
    //     { 
    //         $ret = $this->repo->hydrate($this->form->getData(), $fp);
    //     }
    //     return $ret;
    // }
}