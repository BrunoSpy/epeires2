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

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Core\Controller\AbstractEntityManagerAwareController;

use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Stdlib\Parameters;

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
class AfisController extends TabController
{
    private $em, $cf, $repo, $form, $notamweb;

    public function __construct(EntityManager $em, CustomFieldService $cf, $config, $mattermost, $notamweb)
    {
        parent::__construct($config, $mattermost);
        $this->em = $em;
        $this->cf = $cf;
        $this->repo = $this->em->getRepository(Afis::class);

        $this->form = (new AnnotationBuilder())->createForm(Afis::class);
        $this->form
            ->get('organisation')
            ->setValueOptions(
                $this->em
                    ->getRepository(Organisation::class)
                    ->getAllAsArray()
                );
        $this->notamweb = $notamweb;
    }


    public function indexAction()
    {
        parent::indexAction();
        $cats = [];
        foreach ($this->em->getRepository(AfisCategory::class)->findAll() as $cat) {
            $cats[] = $cat->getId();
        }

        return (new ViewModel())
            ->setVariables([
                'cats' => $cats,
                'afis' => $this->getAfis()
            ]);
    }

    public function testNotamAccessAction()
    {
        return new JsonModel([
            'notamAccess' => $this->notamweb->testNOTAMWeb(),
            'notamUrl' => $this->notamweb->getNotamWebUrl(),
            'notamProxy' => $this->notamweb->getNotamWebProxy(),
        ]);
    }

    public function getAllNotamFromCodeaction()
    {
        $code = strtoupper($this->params()->fromQuery('code'));
        $content = $this->notamweb->getFromCode($code);
        return new JsonModel([
            'notams'   => $content
        ]);
    }

    public function getAction()
    {
        if (!$this->authAfis('read')) {
            echo self::ACCES_REQUIRED;
            return new JsonModel();
        }

        $post = $this->getRequest()->getPost();
        $decom = (boolean) intval($post['decomissionned']);
        $admin = (boolean) intval($post['admin']);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'admin'    => $admin,
                'afises'   => $this->getAfis($decom)
            ]);
    }

    public function formAction()
    {
        $id = intval($this->getRequest()->getPost()['id']);
        $afis = ($id) ? $this->repo->find($id) : new Afis();
        $this->form->bind($afis);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->form
            ]);
    }

    public function switchafisAction()
    {
        if (!$this->authAfis('read')) return new JsonModel();
        $msgType = "error";

        $post = $this->getRequest()->getPost();
        $state = (boolean) $post['state'];
        $id = intval($post['id']);

        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));

        if ($id)
        {
            $events = $this->em
                ->getRepository('Application\Entity\Event')
                ->getCurrentEvents('Application\Entity\AfisCategory');

            $afisevents = array();
            foreach ($events as $event)
            {
                $afisfield = $event->getCategory()->getAfisfield();
                foreach ($event->getCustomFieldsValues() as $value)
                {
                    if ($value->getCustomField()->getId() == $afisfield->getId())
                    {
                        if ($value->getValue() == $id) {
                            $afisevents[] = $event;
                        }
                    }
                }
            }

            if ($state == 0)
            {
                if (count($afisevents) == 1) {
                    $event = $afisevents[0];
                    $endstatus = $this->em->getRepository('Application\Entity\Status')->find('3');
                    $event->setStatus($endstatus);
                    $event->setEnddate($now);
                    $this->em->persist($event);
                    try {
                        $this->em->flush();
                        $msgType = 'success';
                        $msg = "Fermeture d'AFIS.";
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                    }
                } else {
                    $msg = "Impossible de déterminer l'évènement à terminer.";
                }
            }
            else
            {
                if (count($afisevents) > 0) {
                    $msg = "Un évènement est déjà en cours pour cette AFIS, impossible d'en créer un nouveau.";
                } else {
                    $event = new Event();
                    $status = $this->em->getRepository('Application\Entity\Status')->find('2');
                    $impact = $this->em->getRepository('Application\Entity\Impact')->find('3');
                    $event->setStatus($status);
                    $event->setStartdate($now);
                    $event->setImpact($impact);
                    $event->setPunctual(false);
                    $afis = $this->em->getRepository('Application\Entity\Afis')->find($id);
                    $event->setOrganisation($afis->getOrganisation());
                    $event->setAuthor($this->zfcUserAuthentication()
                        ->getIdentity());

                    $categories = $this->em->getRepository('Application\Entity\AfisCategory')->findBy(array(
                        'defaultafiscategory' => true
                    ));

                    if ($categories) {
                        $cat = $categories[0];
                        $afisfieldvalue = new CustomFieldValue();
                        $afisfieldvalue->setCustomField($cat->getAfisfield());
                        $afisfieldvalue->setValue($id);
                        $afisfieldvalue->setEvent($event);
                        $event->addCustomFieldValue($afisfieldvalue);
                        $statusvalue = new CustomFieldValue();
                        $statusvalue->setCustomField($cat->getStatefield());
                        $statusvalue->setValue(true);
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
                        try {
                            $this->em->flush();
                            $msgType = 'success';
                            $msg = "Ouverture d'AFIS.";
                        } catch (\Exception $e) {
                            $msg = $e->getMessage();
                        }
                    } else {
                        $msg = "Impossible de créer un évènement associé à cette action : aucune catégorie AFIS n'a été créée.";
                    }
                }
            }
        } else {
            $msg = "Requête incorrecte, impossible de trouver l'AFIS correspondant.";
        }
        return new JsonModel([
            'type' => $msgType,
            'msg' => $msg
        ]);
    }

    private function getAfis($decom = 0)
    {
        $allAfis = [];
        foreach ($this->repo->findBy(['decommissionned' => $decom]) as $afis)
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

    private function authAfis($action)
    {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.'.$action)) ? false : true;
    }
}
