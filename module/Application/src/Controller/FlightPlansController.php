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

use DateTime;
use DateInterval;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Session\Container;
use Doctrine\ORM\EntityManager;

use Application\Entity\Category;
use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Application\Entity\Event;
use Application\Entity\EventUpdate;
use Application\Entity\CustomFieldValue;

use Application\Entity\FlightPlanCategory;
use Application\Entity\AlertCategory;
use Application\Entity\CustomField;
use Application\Form\AlertForm;

/**
 *
 * @author Loïc Perrin
 */

class FlightPlansController extends EventsController
{
    const ACCES_REQUIRED = "Droits d'accès insuffisants.";
    const NO_ID_EVENT = "Aucun événement ne correspond à l'identifiant.";
    const NO_EVENT = "L'identifiant donné ne correspond à aucun événement.";
    const INVALID_TIME_INTERVAL = "Heure de clôture < Heure de début.";
    const END_OK = "Clôture de l'événement.";
    const REOPEN_OK = "Annulation de la clôture de l'événement.";
    const NO_ALERT_CAT = "Impossible de créer l'alerte, pas de catégorie alerte créée.";
    const ALERT_CREATED = "Nouvelle alerte confirmée.";
    const ALERT_EDITED = "Alerte modifiée.";
    const DATE_FORMAT_INVALID = "Format de date non valide.";
    const CANT_CLOSE = "Impossible de clôre l'événement.";

    const ALERT_TYPES = [
        'INCERFA' => 'info',
        'ALERFA' => 'warning',
        'DETRESFA' => 'danger'
    ];

    protected $em, $cfService, $evService,
        $repo, $form,
        $fp_cats, $alt_cats,
        $zfrcbacOptions;

    private $sessionManager;

    public function __construct(
        EntityManager $em,
        EventService $evService,
        CustomFieldService $cfService,
        $zfrcbacOptions,
        Array $config,
        $mattermost,
        $translator,
        $sessionManager,
        $sessioncontainer)
    {
        parent::__construct(
            $em,
            $evService,
            $cfService,
            $zfrcbacOptions,
            $config,
            $mattermost,
            $translator,
            $sessioncontainer
        );

        $this->em = $em;
        $this->cfService = $cfService;
        $this->evService = $evService;
        $this->zfrcbacOptions = $zfrcbacOptions;
        $this->fp_cats = $this->getEventCategories(FlightPlanCategory::class);
        $this->alt_cats = $this->getEventCategories(AlertCategory::class);
        $this->sessionManager = $sessionManager;
    }

    // vues
    public function indexAction()
    {
        parent::indexAction();

        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
            && $this->authFlightPlans('read'));
        if (!$hasAccess)
        {
            echo $this->showErrorMsg(self::ACCES_REQUIRED);
            return (new ViewModel())
                ->setVariables([
                    'cats' => $this->fp_cats,
                    'messages' => $this->getPendingMessages(),
                    'alertcats' => $this->alt_cats,
                    'hasAccess' => $hasAccess,
                ]);
        }

        // récupération des données de la date depuis GET ou POST
        $post_date = $this->params()->fromPost('date', '');
        $query_date = $this->params()->fromQuery('d', '');
        $date = ($query_date) ? $query_date : $post_date;

        // création d'un intervalle de temps pour la récupération des PLN
        $date_interval = $this->getStartAndEndDateTime($date);
        $flightplans = $this->getFlightPlansFromTimeInterval(
        $date_interval['start'], $date_interval['end']);


        return (new ViewModel())
            ->setVariables([
                'cats' => $this->fp_cats,
                // affichage des flashMessages
                'messages' => $this->getPendingMessages(),
                'alertcats' => $this->alt_cats,
                // accès à une catégorie d'événement PLN
                'hasAccess' => $hasAccess,
                // envoi de la date courante ou choisi au bootstrap calendar : format américain
                'current_date' => $date_interval['start']->format('m/d/Y'),
                // champs de la catégorie ordonnés par le champ "place"
                'fields'            => $this->getFields(),
                // vols sans alerte
                'flightplans'       => $flightplans[0],
                // vols avec alerte
                'flightplansWAlt'   => $flightplans[1],
                'filters' => $this->getFpFilters()
            ]);
    }

    public function endAction()
    {
        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
        && $this->authFlightPlans('read') && $this->authFlightPlans('write'));
        if (!$hasAccess)
        {
            $this->flashMessenger()->addErrorMessage(ACCES_REQUIRED);
            return new JsonModel();
        }
        
        $eventId = $this->params()->fromPost('id', 0);
        $endDate = $this->params()->fromPost('endDate', '');
        $event = $this->endEvent($eventId, $endDate);
        
        if (!$event)
        {
            $this->flashMessenger()->addErrorMessage(self::CANT_CLOSE);
            return;
        }
        
        $this->addEventNote($event, $this->params()->fromPost('fpNote', ''));
        $event->setLastModifiedOn();
        
        $this->em->persist($event);
        try
        {
            $this->em->flush();
        }
        catch (\Exception $ex)
        {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
        }
        return new JsonModel();
    }

    public function reopenAction()
    {
        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
        && $this->authFlightPlans('read') && $this->authFlightPlans('write'));
        if (!$hasAccess)
        {
            $this->flashMessenger()->addErrorMessage(ACCES_REQUIRED);
            return new JsonModel();
        }

        $eventId = $this->params()->fromPost('id', 0);
        $event = $this->verifEvent($eventId);
        if (!$event)
            return new JsonModel();

        $this->addEventNote($event, $this->params()->fromPost('fpNote', ''));

        $openStatus = $this->em->getRepository('Application\Entity\Status')->find('1');
        $event->setLastModifiedOn();
        $event->setStatus($openStatus);
        $event->setEnddate(null);
        $this->em->persist($event);
        try
        {
            $this->em->flush();
            $this->flashMessenger()->addSuccessMessage(self::REOPEN_OK);
        }
        catch (\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }
        return new JsonModel();
    }

    public function histAction()
    {
        $fpEvent = $this->verifEvent($this->params()->fromPost('id', 0));
        if (!$fpEvent) return new JsonModel();

        $alertId = $this->getAlertIdFromFp($fpEvent);
        $altEvent = ($alertId) ? $this->em->getRepository(Event::class)->find($alertId) : null;

        $fpHistory = $this->evService->getHistory($fpEvent);
        $altHistory = ($altEvent) ? $this->evService->getHistory($altEvent) : null;

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(
        [
            'fpHistory'=> $fpHistory,
            'altHistory'=> $altHistory
        ]);
        return $view;
    }

    public function formAltAction()
    {
        $fpEvent = $this->verifEvent($this->params()->fromPost('id', 0));
        if (!$fpEvent) return new JsonModel();

        $form = new AlertForm('alert-form', ['alt-type' => self::ALERT_TYPES]);

        $alertId = $this->getAlertIdFromFp($fpEvent);
        // edit
        if ($alertId)
        {
            $alertData = $this->getAlertDataFromFpEvent($fpEvent);
            $form->get('alt-type')->setValue($alertData['Type']);
            $form->get('alt-cause')->setValue($alertData['Cause']);
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(
        [
            'form' => $form
        ]);
        return $view;;
    }

    public function endAlertAction()
    {
        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
        && $this->canCurrentUserAccessOneCategoryOf($this->alt_cats)
        && $this->authFlightPlans('read') && $this->authFlightPlans('write'));
        if (!$hasAccess)
        {
            $this->flashMessenger()->addErrorMessage(self::ACCES_REQUIRED);
            return new JsonModel();
        }

        $eventId = $this->params()->fromPost('id', 0);
        $endDate = $this->params()->fromPost('endAltDate', '');
        $altEvent = $this->endEvent($eventId, $endDate);

        $this->addEventNote($altEvent, $this->params()->fromPost('altNote', ''));

        return new JsonModel();
    }

    public function triggerAlertAction()
    {
        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
        && $this->canCurrentUserAccessOneCategoryOf($this->alt_cats)
        && $this->authFlightPlans('read') && $this->authFlightPlans('write'));
        if (!$hasAccess)
        {
            $this->flashMessenger()->addErrorMessage(self::ACCES_REQUIRED);
            return new JsonModel();
        }

        // récupération données POST
        $post = $this->getRequest()->getPost();
        $alertNote = $post['altNote'];
        $alertData = [
            'altType' => $post['altType'],
            'altCause' => $post['altCause'],
        ];

        $fpEvent = $this->verifEvent((int) $post['id']);
        $alertid = $this->getAlertIdFromFp($fpEvent);
        $alertev = ($alertid) ? $this->em->getRepository(Event::class)->find($alertid) : null;

        if ($alertev)
        {
            $this->editAlert($alertev, $alertData);
            $this->addEventNote($alertev, $alertNote);
        }
        else
        {
            $newAlertEvent = $this->createAlert($alertData);
            if (!$newAlertEvent) return new JsonModel();

            if (!$this->setAlertOnFlightPlan($fpEvent, $newAlertEvent)) return new JsonModel;
            $this->addEventNote($newAlertEvent, $alertNote);
        }
        return new JsonModel();
    }

    public function reopenaltAction()
    {
        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
        && $this->canCurrentUserAccessOneCategoryOf($this->alt_cats)
        && $this->authFlightPlans('read') && $this->authFlightPlans('write'));
        if (!$hasAccess)
        {
            $this->flashMessenger()->addErrorMessage(self::ACCES_REQUIRED);
            return new JsonModel();
        }

        $altId = $this->params()->fromPost('id', 0);
        $altEvent = $this->verifEvent($altId);
        if (!$altEvent)
            return new JsonModel();

        $this->addEventNote($altEvent, $this->params()->fromPost('altNote', ''));

        $openStatus = $this->em->getRepository('Application\Entity\Status')->find('1');
        $altEvent->setLastModifiedOn();
        $altEvent->setStatus($openStatus);
        $altEvent->setEnddate(null);
        $this->em->persist($altEvent);
        try
        {
            $this->em->flush();
            $this->flashMessenger()->addSuccessMessage(self::REOPEN_OK);
        }
        catch (\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }
        return new JsonModel();
    }
    
    public function toggleFilterAction()
    {
        // vérification d'accès à l'onglet et d'accès à la catégorie
        $hasAccess = ($this->canCurrentUserAccessOneCategoryOf($this->fp_cats)
            && $this->authFlightPlans('read'));
        if (!$hasAccess)
        {
            $this->flashMessenger()->addErrorMessage(ACCES_REQUIRED);
            return new JsonModel();
        }

        $filter = $this->params()->fromPost('filter', '');
        if ($filter == '') return new JsonModel();

        $checkedValue = $this->params()->fromPost('value', false);

        $filterSession = new Container($filter, $this->sessionManager);
        $filterSession->checked = $checkedValue;
        return new JsonModel();
    }

    // gestion des PLN
    private function verifEvent(int $eventId) : Event
    {
        // vérification présence d'un ID
        if($eventId == 0)
        {
            $this->flashMessenger()->addErrorMessage(self::NO_ID_EVENT);
            return null;
        }

        // vérification existence de l'événement
        $event = $this->em->getRepository(Event::class)->find($eventId);
        if (!is_a($event, Event::class))
        {
            $this->flashMessenger()->addErrorMessage(self::NO_EVENT);
            return null;
        }
        return $event;
    }

    private function endEvent(int $eventId, string $endDate) : Event
    {
        $event = $this->verifEvent($eventId);

        // paramétrage de la date de cloture du plan de vol
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        if ($endDate)
        {
            try
            {
                // TODO verif format $endDate
                $endDate = new \DateTime($endDate);
                $endDate->setTimezone(new \DateTimeZone("UTC"));
            }
            catch( \Exception $e)
            {
                $this->flashMessenger()->addErrorMessage(self::DATE_FORMAT_INVALID);
                $this->flashMessenger()->addErrorMessage($e->getMessage());
                return null;
            }
        }
        else
        {
            $endDate = $now;
        }

        // statut fin confirmé
        $endstatus = $this->em->getRepository('Application\Entity\Status')->find('3');

        $event->close($endstatus, $endDate);
        $this->em->persist($event);
        try
        {
            $this->em->flush();
            $this->flashMessenger()->addSuccessMessage(self::END_OK);
            return $event;
        }
        catch (\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return null;
        }
    }

    // retourne [ [0], [1] ] 0: PLN sans alerte / 1: PLN avec alerte
    private function getFlightPlansFromTimeInterval(DateTime $start, DateTime $end) : array
    {
        $flightPlans = [];
        $flightPlansAlert = [];
        $evRepo = $this->em->getRepository(Event::class);

        foreach ($evRepo->getFlightPlanEvents($start, $end) as $fpEvent)
        {
            $arrayFlightPlan = $this->getFlightPlanDataFromEvent($fpEvent);
            $arrayAlert = $this->getAlertDataFromFpEvent($fpEvent);
            if ($arrayAlert['id'])
            {
                $arrayFlightPlan['ev-alert'] = $arrayAlert;
                $flightPlansAlert[] = $arrayFlightPlan;
            }
            else
                $flightPlans[] = $arrayFlightPlan;
        }
        return [$flightPlans, $flightPlansAlert];
    }

    private function getFlightPlanDataFromEvent(Event $event) : array
    {
        $flightPlanArray = [
            'id' => $event->getId(),
            'start_date' => $event->getStartDate(),
            'end_date' => $event->getEndDate(),
            'status' => $event->getStatus(),
            'notes' => $event->getUpdates()
        ];
        foreach ($event->getCustomFieldsValues() as $customFieldValue)
        {
            $flightPlanArray[$customFieldValue->getCustomField()->getName()] =
                $this->cfService->getFormattedValue(
                    $customFieldValue->getCustomField(),
                    $customFieldValue->getValue());
        }
        return $flightPlanArray;
    }

    private function processCustomFieldValues(array $customFieldArray, Event $event)
    {
        foreach ($customFieldArray as $key => $value)
        {
            // génération des customvalues si un customfield dont le nom est $key est trouvé
            $customfield = $this->em->getRepository('Application\Entity\CustomField')->findOneBy(
                ['id' => $key]);
            if ($customfield)
            {
                if (is_array($value))
                {
                    $temp = "";
                    foreach ($value as $v)
                    {
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

    private function createCFServiceValue(Event $event, CustomField $customField, $value) : CustomFieldValue
    {
        $cfServiceValue = new CustomFieldValue();
        $cfServiceValue->setCustomField($customField);
        $cfServiceValue->setValue($value);
        $cfServiceValue->setEvent($event);
        $event->addCustomFieldValue($cfServiceValue);
        return $cfServiceValue;
    }

    private function addEventNote(Event $event, string $note) : bool
    {
        if (!$note) return false;
        // suppression des separateurs utilisés par le script js
        $note = str_replace('|', null, $note);
        $note = str_replace('$', null, $note);
        $eventupdate = new EventUpdate();
        $eventupdate->setText($note);
        $eventupdate->setEvent($event);
        $this->em->persist($eventupdate);
        try
        {
            $this->em->flush();
            return true;
        }
        catch (\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return false;
        }
    }

    private function createAlert(array $alertData) : Event
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));

        // création de l'evenement d'alerte
        $event = new Event();
        $event->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
        $event->setStartdate($now);
        $event->setImpact($this->em->getRepository('Application\Entity\Impact')->find('3'));
        $event->setPunctual(false);
        $event->setOrganisation($this->lmcUserAuthentication()
            ->getIdentity()
            ->getOrganisation());
        $event->setAuthor($this->lmcUserAuthentication()->getIdentity());

        // affectation de la catégorie
        // TODO si plusieurs catégories?
        $categories = $this->em->getRepository('Application\Entity\AlertCategory')->findAll();
        if (count($categories) == 0)
        {
            $this->flashMessenger()->addErrorMessage(self::NO_ALERT_CAT);
            return null;
        }

        $cat = $categories[0];
        $event->setCategory($cat);

        $typefieldvalue = $this->createCFServiceValue($event, $cat->getTypeField(), $alertData['altType']);
        $this->em->persist($typefieldvalue);
        $causefieldvalue = $this->createCFServiceValue($event, $cat->getCauseField(), $alertData['altCause']);
        $this->em->persist($causefieldvalue);

        // ajout des valeurs des champs persos
        if (isset($alertData['custom_fields']))
            $this->processCustomFieldValues($alertData['customfields'], $event);

        $this->em->persist($event);
        try
        {
            $this->em->flush();
            return $event;
        }
        catch (\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return null;
        }
    }

    private function editAlert(Event $event, array $alertData) : bool
    {
        $cat = $event->getCategory();
        $typefieldid = $cat->getTypeField()->getId();
        $causefieldid = $cat->getCauseField()->getId();
        foreach ($event->getCustomFieldsValues() as $customfieldvalue)
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
            $typefield->setValue($alertData['altType']);
            $causefield->setValue($alertData['altCause']);
            $this->em->persist($typefield);
            $this->em->persist($causefield);
            try
            {
                $this->em->flush();
                $this->flashMessenger()->addSuccessMessage(self::ALERT_EDITED);
                return true;
            }
            catch (\Exception $e)
            {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
                return false;
            }
        }
    }

    // gestion des alertes
    private function getAlertDataFromFpEvent(Event $fpEvent) : array
    {
        $alertArray = [];
        foreach ($fpEvent->getCustomFieldsValues() as $value)
        {
            $customfield = $value->getCustomField();
            // pas un champ contenant une alerte
            if ($customfield->getId() != $fpEvent->getCategory()->getAlertfield()->getId())
                continue;

            $valuefield = $value->getValue();
            // champ d'alerte mais pas d'alerte associée
            if (!$valuefield)
                continue;

            $altEvent = $this->em->getRepository(Event::class)->findOneBy(['id' => $valuefield]);
            if (!$altEvent instanceof Event)
                continue;

            $alertArray = [
                'id' => $altEvent->getId(),
                'start_date' => $altEvent->getStartDate(),
                'end_date' => $altEvent->getEndDate(),
                'notes' => $altEvent->getUpdates()
            ];

            foreach ($this->getCustomFieldData($altEvent) as $key => $value)
            {
                $alertArray[$key] = $value;
            };
        }
        return $alertArray;
    }

    private function getCustomFieldData(Event $event) : array
    {
        $dataArray = [];
        foreach ($event->getCustomFieldsValues() as $value)
        {
            $customfield = $value->getCustomField();
            $namefield = (isset($customfield)) ? $customfield->getName() : null;
            (isset($namefield)) ? $dataArray[$namefield] = $value->getValue() : null;
        }
        return $dataArray;
    }

    private function getAlertIdFromFp(Event $fpEvent) : int
    {
        if (is_a($fpEvent, Event::class))
        {
            foreach ($fpEvent->getCustomFieldsValues() as $customfieldvalue)
            {
                if ($customfieldvalue->getCustomField()->getId() == $fpEvent->getCategory()->getAlertfield()->getId())
                {
                    return $customfieldvalue->getValue();
                }
            }
        }
        return 0;
    }

    private function setAlertOnFlightPlan(Event $fpEvent, Event $newAlertEvent): bool
    {
        $alertvalue = new CustomFieldValue();
        $alertvalue->setEvent($fpEvent);
        $alertvalue->setCustomField($fpEvent->getCategory()->getAlertfield());
        $alertvalue->setValue($newAlertEvent->getId());
        $fpEvent->addCustomFieldValue($alertvalue);
        $this->em->persist($alertvalue);
        $this->em->persist($fpEvent);
        try
        {
            $this->em->flush();
            $this->flashMessenger()->addSuccessMessage(self::ALERT_CREATED);
            return true;
        }
        catch(\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return false;
        }
    }

    // on exclut le champ alerte
    private function getFields() : array
    {
        $cfService = $this->em->getRepository('Application\Entity\CustomField')->findBy(
            ['category' => $this->fp_cats[0]],
            ['place' => 'ASC'] );

        $fields = [];
        foreach ($cfService as $c) {
            $name = $c->getName();
            if ($name == "Alerte") continue;
            $fields[] = $name;
        }
        return $fields;
    }

    private function authFlightPlans(string $action) : bool
    {
        return ($this->lmcUserAuthentication()->hasIdentity() && $this->isGranted('flightplans.'.$action)) ? true : false;
    }

    private function getEventCategories(string $classCategory) : array
    {
        $cats = [];
        foreach ($this->em->getRepository($classCategory)->findAll() as $cat) {
            $cats[] = $cat->getId();
        }
        return $cats;
    }

    private function canCurrentUserAccessOneCategoryOf(array $cats) : bool
    {
        $readablecat = [];
        foreach ($cats as $cat)
        {
            $category = $this->em->getRepository(Category::class)->find($cat);
            if ($this->lmcUserAuthentication()->hasIdentity())
            {
                $roles = $this->lmcUserAuthentication()
                    ->getIdentity()
                    ->getRoles();
                foreach ($roles as $role)
                {
                    if ($category->getReadroles(true)->contains($role))
                    {
                        $readablecat[] = $category;
                        break;
                    }
                }
            }
            else
            {
                //$role = $this->zfcRbacOptions->getGuestRole();
                //$roleentity = $this->em->getRepository('Core\Entity\Role')->findOneBy(array(
                //    'name' => $role
                //));
                //if ($roleentity)
                //{
                //    if ($category->getReadroles(true)->contains($roleentity)) {
                //        $readablecat[] = $category;
                //    }
                //}
            }
        }
        return (count($readablecat) > 0);
    }

    private function getStartAndEndDateTime(string $date) : array
    {
        $dates = [];
        if (isset($date) && $date != '') {
            $dates['start'] = new DateTime($date);
            $dates['end'] = (new DateTime($date))->add(new DateInterval('P1D'));
        } else {
            $dates['start'] = (new DateTime())->setTime(0,0,0);
            $dates['end'] = (new DateTime())->setTime(0,0,0)->add(new DateInterval('P1D'));
        }
        return $dates;
    }

    private function getPendingMessages() : array
    {
        $messages = [];
        if ($this->flashMessenger()->hasErrorMessages()) {
            $messages['error'] = $this->flashMessenger()->getErrorMessages();
        }

        if ($this->flashMessenger()->hasSuccessMessages()) {
            $messages['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        $this->flashMessenger()->clearMessages();
        return $messages;
    }

    private function showErrorMsg(string $msg) : string
    {
        return "<div class='alert alert-danger'>".$msg."</div>";
    }

    private function getFpFilters() : array 
    {
        $fpFilter = new Container('fp', $this->sessionManager);
        $altFilter = new Container('alt', $this->sessionManager);
        return [
            'fp' => (isset($fpFilter->checked)) ? $fpFilter->checked : false,
            'alt' => (isset($altFilter->checked)) ? $altFilter->checked : false,
        ];
    }
}
