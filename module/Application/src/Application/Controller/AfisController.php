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
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;

use Doctrine\ORM\EntityManager;

use Core\Controller\AbstractEntityManagerAwareController;

use Application\Services\CustomFieldService;
use Application\Form\CustomFieldset;

use Application\Entity\Event;
use Application\Entity\CustomFieldValue;

use Application\Entity\Organisation;
use Application\Entity\AfisCategory;
use Application\Entity\Afis;
/**
 *
 * @author Loïc Perrin
 */
class AfisController extends AbstractEntityManagerAwareController
{
    private $customfieldservice, $em, $repo, $form;

    public function __construct(EntityManager $em, CustomFieldService $customfieldService)
    {
        parent::__construct($em);
        $this->customfieldservice = $customfieldService;
        $this->em = $this->getEntityManager();
        $this->repo = $this->em->getRepository(Afis::class);

        $this->form = (new AnnotationBuilder())->createForm(Afis::class);    
        $this->form
            ->get('organisation')
            ->setValueOptions(
                $this->em
                    ->getRepository(Organisation::class)
                    ->getAllAsArray()
                );

        $categories = $this->em->getRepository(AfisCategory::class)->findBy([
            'defaultafiscategory' => true
        ]);

        if ($categories) {
            $cat = $categories[0];
            $this->form->add(new CustomFieldset($this->em, $this->customfieldservice, $cat->getId()));
            $this->form->get('custom_fields')->remove($cat->getAfisfield()->getId());
            $this->form->get('custom_fields')->remove($cat->getStatefield()->getId());
        }


    }

    public function indexAction()
    {
        if (!$this->authAfis('read')) return new JsonModel();
    }

    private function getAfis()
    {
        $allAfis = [];
        foreach ($this->repo->findBy(['decommissionned' => 0]) as $afis) 
        {
            $allAfis[$afis->getId()]['self'] = $afis;
        }
        
        $results = $this->em->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\AfisCategory');
        foreach ($results as $result) 
        {
            $statefield = $result->getCategory()
                ->getStatefield()
                ->getId();
            $afisfield = $result->getCategory()
                ->getAfisfield()
                ->getId();
            // $afisid = 0;
            // $available = true;
            foreach ($result->getCustomFieldsValues() as $customvalue) 
            {
                if ($customvalue->getCustomField()->getId() == $statefield) 
                {
                    $available = $customvalue->getValue();
                } 
                else if ($customvalue->getCustomField()->getId() == $afisfield) $afisid = $customvalue->getValue();
            }
            if (array_key_exists($afisid, $allAfis)) $allAfis[$afisid]['state'] = $available;
        }
        return $allAfis;
    }

    public function getnotamsAction() 
    {
        $code = $this->params()->fromQuery('code');
        // $code = "LFOP";
        $fields = [
            'AERO_CM_GPS' => '2',
            'AERO_CM_INFO_COMP' => '1',
            'AERO_CM_REGLE' => '1',
            'AERO_Date_DATE' => urlencode((new \DateTime())->format('Y/m/d')),
            'AERO_Date_HEURE' => urlencode((new \DateTime())->format('H:i')),
            'AERO_Duree' => '24',
            'AERO_Langue' => 'FR',
            'AERO_Tab_Aero[0]' => $code,
            'AERO_Tab_Aero[1]' => '',
            'AERO_Tab_Aero[2]' => '',
            'AERO_Tab_Aero[3]' => '',
            'AERO_Tab_Aero[4]' => '',
            'AERO_Tab_Aero[5]' => '',
            'AERO_Tab_Aero[6]' => '',
            'AERO_Tab_Aero[7]' => '',
            'AERO_Tab_Aero[8]' => '',
            'AERO_Tab_Aero[9]' => '',
            'ModeAffichage' => 'COMPLET',
            'bImpression' => '',
            'bResultat' => 'true'
        ];

        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://notamweb.aviation-civile.gouv.fr/Script/IHM/Bul_Aerodrome.php?AERO_Langue=FR',
            CURLOPT_POST => $fields,
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ]);

        $output = curl_exec($curl);

        curl_close($curl);

        $content = preg_replace('/.*<body[^>]*>/msi','',$output);
        $content = preg_replace('/<\/body>.*/msi','',$content);
        $content = preg_replace('/<?\/body[^>]*>/msi','',$content);
        $content = preg_replace('/<img[^>]+\>/i', '', $content);
        // $content = preg_replace('/[\r|\n]+/msi','',$content);
        $content = preg_replace('/<--[\S\s]*?-->/msi','',$content);
        $content = preg_replace('/<noscript[^>]*>[\S\s]*?'.
                              '<\/noscript>/msi',
                              '',$content);
        $content = preg_replace('/<script[^>]*>[\S\s]*?<\/script>/msi',
                              '',$content);
        $content = preg_replace('/<script.*\/>/msi','',$content);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'notams'   => $content 
            ]);
    }

    public function getAction() 
    {
        if (!$this->authAfis('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $decom = (boolean) intval($post['decomissionned']);
        $admin = (boolean) intval($post['admin']);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'admin'    => $admin,
                // 'notams'   => $this->getNOTAMs(), 
                'afises'   => $this->getAfis()
            ]);     
    }

    public function formAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $afis = ($id) ? $this->repo->find($id) : new Afis();
        $this->form->bind($afis);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->form
            ]);
    }

    // public function switchafisAction()
    // {
    //     if (!$this->authAfis('write')) return new JsonModel();

    //     $post = $this->getRequest()->getPost();
    //     $id = intval($post['id']);

    //     $afis = $this->repo->find($id);

    //     if(is_a($afis, Afis::class)) {
    //         $afis->setState((boolean) $post['state']);
    //         return new JsonModel($this->repo->save($afis));
    //     } else {
    //         return new JsonModel([
    //             'type' => 'error', 
    //             'msg' => 'Afis non existant'
    //         ]);
    //     }
    // }

    public function saveAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $afis = $this->validateAfis($post);

        if(is_a($afis, Afis::class)) {
            return new JsonModel($this->repo->save($afis));
        } else {
            return new JsonModel([
                'type' => 'error', 
                'msg' => $this->showErrors()
            ]);
        }
    }

    public function deleteAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);

        $afis = $this->repo->find($id);

        if(is_a($afis, Afis::class)) {
            return new JsonModel($this->repo->del($afis));
        } else {
            return new JsonModel([
                'type' => 'error', 
                'msg' => 'Afis non existant'
            ]);
        }
    }

    private function authAfis($action) 
    {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.'.$action)) ? false : true;
    }

    private function validateAfis($params) 
    {
        if (!is_a($params, Parameters::class) && !is_array($params)) return false;

        $id = intval($params['id']);
        $afis = ($id) ? $this->repo->find($id) : new Afis();
        $this->form->setData($params);

        if (!$this->form->isValid()) $ret = false;
        else 
        { 
            $ret = $this->repo->hydrate($this->form->getData(), $afis);
        }
        return $ret;
    }

    private function showErrors() {
        $str = '';
        foreach ($this->form->getMessages() as $field => $messages)
        foreach ($messages as $typeErr => $message)
        $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }

    public function switchafisAction()
    {
        // $messages = array();
        // if ($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {

        $post = $this->getRequest()->getPost();
        $state = (boolean) $post['state'];
        $id = intval($post['id']);
        
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        // $id=17;
        // $state=true;
        if ($id == 0) {
            return new JsonModel([
                'type' => 'error', 
                'msg' => 'Afis non existant.'
            ]);
        }
        /*
        Récupération des évènements de catégorie Afis et d'id d'Afis : $id
        Stockage dans tableau $afisEvents
         */
        $allAfisEvents = $this->em
            ->getRepository('Application\Entity\Event')
            ->getCurrentEvents('Application\Entity\AfisCategory');
        
        $afisEvents = [];
        foreach ($allAfisEvents as $afisEvent) 
        {
            $afisField = $afisEvent->getCategory()->getAfisfield();
            foreach ($afisEvent->getCustomFieldsValues() as $value) 
            {
                // print_r($value->getValue());
                if ($value->getCustomField()->getId() == $afisField->getId()) 
                {
                    if ($value->getValue() == $id) $afisEvents[] = $afisEvent;
                }
            }
        }
        
        if ($state == false) 
        {
            // passage d'un afis à l'état fermé : évènement à cloturer
            if (count($afisEvents) == 1) {
                $event = $afisEvents[0];
                $endstatus = $this->em->getRepository('Application\Entity\Status')->find('3');
                $event->setStatus($endstatus);
                $event->setEnddate($now);
                $this->em->persist($event);
                try 
                {
                    $this->em->flush();
                    $messages = [
                        'type' => 'success', 
                        'msg' => "Evènement de fermeture d'Afis correctement effectué."
                    ];
                } 
                catch (\Exception $e) 
                {
                    $messages = [
                        'type' => 'error', 
                        'msg' => $e->getMessages()
                    ];
                }
            } else {
                $messages = [
                    'type' => 'error', 
                    'msg' => "Impossible de déterminer l'évènement à terminer."
                ];
            }
        } 
        else 
        {
            // passage d'un afis à l'état ouvert -> on vérifie qu'il n'y a pas d'evt en cours
            if (count($afisEvents) > 0) 
            {
                $messages = [
                    'type' => 'error', 
                    'msg' => "Un évènement est déjà en cours pour ce radar, impossible d'en créer un nouveau"
                ];
            } 
            else 
            {
                $status = $this->em->getRepository('Application\Entity\Status')->find('2');
                $impact = $this->em->getRepository('Application\Entity\Impact')->find('3');
                $event = new Event();
                $event->setStatus($status);
                $event->setStartdate($now);
                $event->setImpact($impact);
                $event->setPunctual(false);

                $afis = $this->em->getRepository('Application\Entity\Afis')->find($id);
                $event->setOrganisation($afis->getOrganisation());
                $event->setAuthor($this->zfcUserAuthentication()->getIdentity());
                
                $categories = $this->em->getRepository('Application\Entity\AfisCategory')->findBy([
                    'defaultafiscategory' => true
                ]);

                if ($categories) 
                {
                    $cat = $categories[0];
                    //sauvegarde du champ liant l'évènement à l'Afis
                    $afisfieldvalue = new CustomFieldValue();
                    $afisfieldvalue->setCustomField($cat->getAfisfield());
                    $afisfieldvalue->setValue($id);
                    $afisfieldvalue->setEvent($event);
                    $event->addCustomFieldValue($afisfieldvalue);
                    // sauvegarde du champ d'état
                    $statusvalue = new CustomFieldValue();
                    $statusvalue->setCustomField($cat->getStatefield());
                    $statusvalue->setValue(1);
                    $statusvalue->setEvent($event);
                    $event->addCustomFieldValue($statusvalue);
                    $event->setCategory($categories[0]);
                    $this->em->persist($afisfieldvalue);
                    $this->em->persist($statusvalue);
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
                    try 
                    {
                        $this->em->flush();
                        $messages = [
                            'type' => 'success', 
                            'msg' => "Evènement d'ouverture d'Afis correctement crée."
                        ];
                    } 
                    catch (\Exception $e) 
                    {
                        $messages = [
                            'type' => 'error', 
                            'msg' => $e->getMessages()
                        ];
                    }
                } 
                else 
                {
                    $messages = [
                        'type' => 'error', 
                        'msg' => "Impossible de créer l'évènement."
                    ];
                }
            }
        }
        // else 
        // {
        //     $messages['error'][] = "Requête incorrecte, impossible de trouver le radar correspondant.";
        // }
        // } else {
        //     $messages['error'][] = "Droits insuffisants pour modifier l'état du radar";
        // }
        return new JsonModel($messages);
    }
}