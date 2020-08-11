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
namespace IPO\Controller;

use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Core\Controller\AbstractEntityManagerAwareController;
use Doctrine\ORM\EntityManager;
use OpentbsBundle\Factory\TBSFactory as TBS;
use IPO\Entity\Report;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Laminas\View\Model\JsonModel;
use IPO\Entity\Element;

/**
 *
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class ReportController extends AbstractEntityManagerAwareController
{

    private $eventService;
    private $customFieldService;

    public function __construct(EntityManager $entityManager, EventService $eventService, CustomFieldService $customFieldService)
    {
        parent::__construct($entityManager);
        $this->eventService = $eventService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Export a report in ODF
     */
    public function exportAction()
    {
        
        $id = $this->params()->fromQuery('id', null);
        
        if ($this->lmcUserAuthentication()->hasIdentity() && $id !== null) {
            $tbs = new TBS();
            
            $tbs->LoadTemplate('data/templates/cr_ipo_model_v1.odt', OPENTBS_ALREADY_UTF8);
            
            $org_id = $this->lmcUserAuthentication()
                ->getIdentity()
                ->getOrganisation()
                ->getId();
            $org = $this->getEntityManager()->getRepository('Application\Entity\Organisation')->find($org_id);
            
            $report = $this->getEntityManager()->getRepository('IPO\Entity\Report')->find($id);
            
            $startdate = $report->getStartDate();
            
            $categories = array();
            
            foreach ($report->getElements() as $element) {
                if ($element->getCategory() !== null) {
                    $categories[$element->getCategory()->getId()][] = $element->getEvent();
                }
            }
            
            $formatter = \IntlDateFormatter::create(
                'fr_FR',
                \IntlDateFormatter::FULL, 
                \IntlDateFormatter::FULL, 
                'UTC', 
                \IntlDateFormatter::GREGORIAN, 
                'EEEE d MMMM'
            );
            
            $formatterYear = \IntlDateFormatter::create(
                'fr_FR',
                \IntlDateFormatter::FULL, 
                \IntlDateFormatter::FULL, 
                'UTC', 
                \IntlDateFormatter::GREGORIAN, 
                'EEEE d MMMM Y'
            );
            
            $formatterDayHour = \IntlDateFormatter::create(
                'fr_FR',
                \IntlDateFormatter::FULL, 
                \IntlDateFormatter::FULL, 
                'UTC', 
                \IntlDateFormatter::GREGORIAN, 
                'dd/MM HH:mm'
            );
            
            // pour chaque catégorie
            foreach ($this->getEntityManager()->getRepository('IPO\Entity\ReportCategory')->findAll() as $cat) {
                $catevents = array();
                // pour chaque jour de la semaine
                for ($i = 0; $i <= 6; $i ++) {
                    $tempdate0 = clone $startdate;
                    $tempdate0->modify('+' . $i . ' days');
                    $tempdate1 = clone $startdate;
                    $tempdate1->modify('+' . ($i + 1) . ' days');
                    $date = $formatter->format($tempdate0);
                    $events = array();
                    // pour chaque évènement de la catégorie
                    if (isset($categories[$cat->getId()])) {
                        foreach ($categories[$cat->getId()] as $event) {
                            // on l'ajoute à la liste du jour si l'évènement intersecte le jour
                            if ($this->intersectDates($event, $tempdate0, $tempdate1)) {
                                $newevent = array();
                                $newevent['name'] = $this->eventService->getName($event);
                                $newevent['start'] = $formatterDayHour->format($event->getStartdate());
                                $newevent['end'] = ($event->getEnddate() !== null ? $formatterDayHour->format($event->getEnddate()) : '');
                                $newevent['author'] = $event->getAuthor()->getDisplayName();
                                $newevent['fields'] = array();
                                foreach ($event->getCustomFieldsValues() as $value) {
                                    if($value->getCustomField()->isHidden())
                                        continue;
                                    if(!$value->getCustomField()->isTraceable()) {
                                        $val = array();
                                        $val['name'] = $value->getCustomfield()->getName();
                                        $val['value'] = $this->customFieldService->getFormattedValue($value->getCustomField(), $value->getValue());
                                        $newevent['fields'][] = $val;
                                    } else {
                                        $repo = $this->getEntityManager()->getRepository('Application\Entity\Log');
                                        $logs = $repo->getLogEntries($value);
                                        foreach(array_reverse($logs) as $log) {
                                            $val = array();
                                            $val['name'] = $formatterDayHour->format($log->getLoggedAt()) . " " . $value->getCustomfield()->getName();
                                            $val['value'] = $this->customFieldService->getFormattedValue($value->getCustomField(), $log->getData()["value"]);
                                            $newevent['fields'][] = $val;
                                        }
                                    }
                                }
                                $newevent['updates'] = array();
                                foreach ($event->getUpdates() as $update) {
                                    $up = array();
                                    $up['hour'] = $formatterDayHour->format($update->getCreatedOn());
                                    $up['author'] = $this->eventService->getUpdateAuthor($update);
                                    $up['note'] = $update->getText();
                                    $newevent['updates'][] = $up;
                                }
                                $newevent['isupdates'] = (count($newevent['updates']) > 0 ? 1 : 0);
                                $events[] = $newevent;
                            }
                        }
                    }
                    $catevents[] = array(
                        'day' => $date,
                        'event' => $events
                    );
                }
                $tbs->MergeBlock($cat->getShortname(), $catevents);
            }
            
            $tbs->MergeField('general', array(
                'week_number' => $report->getWeek() . '/' . $report->getYear(),
                'start_date' => $formatter->format($report->getStartDate()),
                'end_date' => $formatterYear->format($report->getEndDate())
            ));
            
            // fields in Header
            $tbs->PlugIn(OPENTBS_SELECT_FILE, 'styles.xml');
            $tbs->MergeField('general', array(
                'organisation_name' => $org->getLongname(),
                'export_date' => $formatterYear->format(new \DateTime('NOW'))
            ));
            
            // send the file
            $tbs->Show(OPENTBS_DOWNLOAD, 'rapport_IPO_semaine' . $report->getWeek() . '_' . $report->getYear() . '.odt');
        } else {}
    }

    private function intersectDates($event, $start, $end)
    {
        return ($event->isPunctual() && $event->getStartdate() >= $start && $event->getStartdate() <= $end) || 
               (! $event->isPunctual() && $event->getEnddate() === null && $event->getStartdate() <= $end) || 
               (! $event->isPunctual() && $event->getEnddate() !== null && $event->getStartdate() <= $end && $event->getEnddate() >= $start);
    }

    public function newreportAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getFormReport($id);
        $form = $getform['form'];
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary'
            )
        ));
        
        $viewmodel->setVariables(array(
            'form' => $form
        ));
        return $viewmodel;
    }

    public function savereportAction()
    {
        $messages = array();
        $json = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getFormReport($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $report = $datas['report'];
            
            if ($form->isValid()) {
                $this->getEntityManager()->persist($report);
                try {
                    $this->getEntityManager()->flush();
                    $this->flashMessenger()->addSuccessMessage("Rapport enregistré.");
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
                $this->flashMessenger()->addErrorMessage("Impossible d\'enregistrer le rapport.");
            }
        }
        return new JsonModel();
    }

    private function getFormReport($id)
    {
        $report = new Report();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($report);
        $form->setHydrator(new DoctrineObject($this->getEntityManager()))->setObject($report);
        
        if ($id) {
            $report = $this->getEntityManager()->getRepository('IPO\Entity\Report')->find($id);
            if ($report) {
                $form->bind($report);
                $form->setData($report->getArrayCopy());
            }
        }
        return array(
            'form' => $form,
            'report' => $report
        );
    }

    public function showAction()
    {
        if ($this->lmcUserAuthentication()->hasIdentity()) {

            $id = $this->params()->fromQuery('id', null);
            if ($id !== null) {
                $report = $this->getEntityManager()->getRepository('IPO\Entity\Report')->find($id);
                $reportcategories = array();
                foreach ($this->getEntityManager()->getRepository('IPO\Entity\ReportCategory')->findBy(array(), array(
                    'place' => 'ASC'
                )) as $reportcategory) {
                    $reportcategories[$reportcategory->getId()] = array(
                        'category' => $reportcategory,
                        'events' => array()
                    );
                }
                // éléments exclus du rapport
                $reportcategories[- 1] = array(
                    'category' => null,
                    'events' => array()
                );
                if ($report) {
                    $events = $this->getEntityManager()->getRepository('Application\Entity\Event')->getAllEvents(
                        $this->lmcUserAuthentication(),
                        $report->getStartDate(), 
                        $report->getEndDate(),
                        true,
                        array(1,2,3,4)
                    );
                    
                    // ids des évènements inclus au rapport
                    $events_id = array();
                    // on enlève tous les éléments déjà inclus au rapport
                    foreach ($report->getElements() as $element) {
                        if ($element->getCategory() === null) {
                            $reportcategories[- 1]['events'][] = $element->getEvent();
                        } else {
                            $reportcategories[$element->getCategory()->getId()]['events'][] = $element->getEvent();
                        }
                        $events_id[] = $element->getEvent()->getId();
                    }
                    
                    $unclassified = array();
                    
                    foreach ($events as $event) {
                        if (! in_array($event->getId(), $events_id, true)) {
                            $unclassified[] = $event;
                        }
                    }
                    
                    return array(
                        'report' => $report,
                        'reportcategories' => $reportcategories,
                        'unclassified' => $unclassified
                    );
                } else {
                    $this->flashMessenger()->addErrorMessage("Aucun rapport trouvé.");
                }
            } else {
                $this->flashMessenger()->addErrorMessage("Aucun rapport trouvé.");
            }
        } else {
            $this->flashMessenger()->addErrorMessage("Utilisateur non identifié : action impossible.");
        }
        
        return array();
    }

    public function affectcategoryAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $catid = $this->params()->fromQuery('catid', null);
        $reportid = $this->params()->fromQuery('reportid', null);
        $json = array();
        $messages = array();
        if ($id !== null && $reportid !== null) {
            $event = $this->getEntityManager()->getRepository('Application\Entity\Event')->find($id);
            // search if report already owns this event
            $report = $this->getEntityManager()->getRepository('IPO\Entity\Report')->find($reportid);
            $element = null;
            foreach ($report->getElements() as $elmt) {
                if ($elmt->getEvent()->getId() === $event->getId()) {
                    $element = $elmt;
                    break;
                }
            }
            if ($element === null) {
                $element = new Element();
                $element->setEvent($event);
                $report->addElement($element);
            }
            if ($catid !== null) {
                $cat = $this->getEntityManager()->getRepository('IPO\Entity\ReportCategory')->find($catid);
                $element->setCategory($cat);
            } else {
                $element->setCategory(null);
            }
            $this->getEntityManager()->persist($element);
            $this->getEntityManager()->persist($report);
            try {
                $this->getEntityManager()->flush();
                $json['id'] = $event->getId();
                $json['catid'] = $catid;
                $messages['success'][] = "Evènement correctement associé.";
            } catch (\Exception $e) {
                $messages['error'][] = $e->getMessage();
            }
        } else {
            $messages['error'][] = "Données manquantes.";
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $json = array();
        $messages = array();
        if ($id !== null) {
            $report = $this->getEntityManager()->getRepository('IPO\Entity\Report')->find($id);
            if ($report) {
                $this->getEntityManager()->remove($report);
                try {
                    $this->getEntityManager()->flush();
                    $messages['success'][] = "Rapport supprimé";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver le rapport à supprimer";
            }
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }
}