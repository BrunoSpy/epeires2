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
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Doctrine\ORM\EntityManager;

use Application\Entity\Category;
use Application\Services\CustomFieldService;
use Application\Services\EventService;

use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Application\Entity\FlightPlanCategory;
use Application\Entity\AlertCategory;

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

    protected $em, $cf, $repo, $form, $fp_cats, $alt_cats;

    public function __construct(
        EntityManager $em,
        EventService $eventService,
        CustomFieldService $cf,
        $zfrcbacOptions,
        Array $config,
        $mattermost,
        $translator)
    {
        parent::__construct(
            $em,
            $eventService,
            $cf,
            $zfrcbacOptions,
            $config,
            $mattermost,
            $translator
        );

        $this->em = $em;
        $this->cf = $cf;
        $this->fp_cats = $this->getEventCategories(FlightPlanCategory::class);
        $this->alt_cats = $this->getEventCategories(AlertCategory::class);
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
            echo ACCES_REQUIRED;
            return false;
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
                'flightplansWAlt'   => $flightplans[1]
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

        $note = $this->params()->fromPost('note', '');
        $eventupdate = new \Application\Entity\EventUpdate();
        $eventupdate->setText($note);
        $eventupdate->setEvent($event);
        $event->setLastModifiedOn();
        $this->em->persist($eventupdate);
        $this->em->persist($event);
        try {
            $this->em->flush();
        } catch (\Exception $ex) {
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
        {
            return new JsonModel();
        }

        $note = $this->params()->fromPost('note', '');
        $eventupdate = new \Application\Entity\EventUpdate();
        $eventupdate->setText($note);
        $eventupdate->setEvent($event);
        $openStatus = $this->em->getRepository('Application\Entity\Status')->find('1');

        $event->setLastModifiedOn();
        $event->setStatus($openStatus);
        $event->setEnddate(null);
        $this->em->persist($eventupdate);
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
        // return new JsonModel();
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

        $this->endEvent($eventId, $endDate);
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
        $id = (int) $post['id'];

        // vérification identifiant de l'événement
        if ($id == 0)
        {
            $this->flashMessenger()->addErrorMessage(self::NO_ID_EVENT);
            return new JsonModel;
        }

        // vérification existence de l'événement
        $fp = $this->em->getRepository(Event::class)->find($id);
        if (!is_a($fp, Event::class))
        {
            $this->flashMessenger()->addErrorMessage(self::NO_EVENT);
            return new JsonModel;
        }

        $alertid = $this->getAlertIdFromFp($fp);
        $alertev = ($alertid) ? $this->em->getRepository(Event::class)->find($alertid) : null;

        if ($alertev)
        {
            $this->editAlert($alertev, $post);
        }
        else
        {
            $idAlert = $this->createAlert($post);
            if ($idAlert != 0)
            {
                $alertvalue = new CustomFieldValue();
                $alertvalue->setEvent($fp);
                $alertvalue->setCustomField($fp->getCategory()->getAlertfield());
                $alertvalue->setValue($idAlert);
                $fp->addCustomFieldValue($alertvalue);
                $this->em->persist($alertvalue);
                $this->em->persist($fp);
                try
                {
                    $this->em->flush();
                    $this->flashMessenger()->addSuccessMessage(self::ALERT_CREATED);
                }
                catch(\Exception $e)
                {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            }
        }
        // return new JsonModel();
    }

    // gestion des PLN
    private function verifEvent($eventId)
    {
        // vérification présence d'un ID
        if($eventId == 0)
        {
            $this->flashMessenger()->addErrorMessage(self::NO_ID_EVENT);
            return false;
        }

        // vérification existence de l'événement
        $event = $this->em->getRepository(Event::class)->find($eventId);
        if (!is_a($event, Event::class))
        {
            $this->flashMessenger()->addErrorMessage(self::NO_EVENT);
            return false;
        }
        return $event;
    }

    private function endEvent($eventId, $endDate)
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
                return false;
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
            return false;
        }
    }

    // retourne [ [0], [1] ] 0: PLN sans alerte / 1: PLN avec alerte
    private function getFlightPlansFromTimeInterval($start, $end)
    {
        $flightPlans = [];
        $flightPlansAlert = [];
        $evRepo = $this->em->getRepository(Event::class);

        foreach ($evRepo->getFlightPlanEvents($start, $end) as $fpEvent)
        {
            $arrayFlightPlan = $this->getFlightPlanDataFromEvent($fpEvent);
            $arrayFlightPlan['notes'] = $fpEvent->getUpdates();
            $arrayAlert = $this->getAlertDataFromEvent($fpEvent);
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

    private function getFlightPlanDataFromEvent($event)
    {
        $flightPlanArray = [
            'id' => $event->getId(),
            'start_date' => $event->getStartDate(),
            'end_date' => $event->getEndDate(),
            'status' => $event->getStatus()
        ];
        foreach ($event->getCustomFieldsValues() as $customFieldValue)
        {
            $flightPlanArray[$customFieldValue->getCustomField()->getName()] =
                $this->cf->getFormattedValue(
                    $customFieldValue->getCustomField(),
                    $customFieldValue->getValue());
        }
        return $flightPlanArray;
    }

    private function processCustomFieldValues($customFieldArray, $event)
    {
        foreach ($alertData['custom_fields'] as $key => $value)
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

    private function createCFValue($event, $customField, $value)
    {
        $cfValue = new CustomFieldValue();
        $cfValue->setCustomField($customField);
        $cfValue->setValue($value);
        $cfValue->setEvent($event);
        $event->addCustomFieldValue($cfValue);
        return $cfValue;
    }

    private function createAlert($alertData)
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));

        // création de l'evenement d'alerte
        $event = new Event();
        $event->setStatus($this->em->getRepository('Application\Entity\Status')->find('2'));
        $event->setStartdate($now);
        $event->setImpact($this->em->getRepository('Application\Entity\Impact')->find('3'));
        $event->setPunctual(false);
        $event->setOrganisation($this->zfcUserAuthentication()
            ->getIdentity()
            ->getOrganisation());
        $event->setAuthor($this->zfcUserAuthentication()->getIdentity());

        // affectation de la catégorie
        // TODO si plusieurs catégories?
        $categories = $this->em->getRepository('Application\Entity\AlertCategory')->findAll();
        if (count($categories) == 0)
        {
            $this->flashMessenger()->addErrorMessage(self::NO_ALERT_CAT);
            return 0;
        }

        $cat = $categories[0];
        $event->setCategory($cat);

        $typefieldvalue = $this->createCFValue($event, $cat->getTypeField(), $alertData['type']);
        $this->em->persist($typefieldvalue);
        $causefieldvalue = $this->createCFValue($event, $cat->getCauseField(), $alertData['cause']);
        $this->em->persist($causefieldvalue);

        // ajout des valeurs des champs persos
        if (isset($alertData['custom_fields']))
            $this->processCustomFieldValues($alertData['customfields'], $event);

        $this->em->persist($event);
        try
        {
            $this->em->flush();
            $id = $event->getId();
        }
        catch (\Exception $e)
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            $id = 0;
        }
        return $id;
    }

    private function editAlert($event, $alertData)
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
            $typefield->setValue($alertData['type']);
            $causefield->setValue($alertData['cause']);
            $this->em->persist($typefield);
            $this->em->persist($causefield);
            try
            {
                $this->em->flush();
                $this->flashMessenger()->addSuccessMessage(self::ALERT_EDITED);
            }
            catch (\Exception $e)
            {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
    }

    // gestion des alertes
    private function getAlertDataFromEvent($event)
    {
        $alertArray = [];
        foreach ($event->getCustomFieldsValues() as $value)
        {
            $customfield = $value->getCustomField();
            // pas un champ contenant une alerte
            if ($customfield->getId() != $event->getCategory()->getAlertfield()->getId())
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
                'end_date' => $altEvent->getEndDate()
            ];

            foreach ($this->getCustomFieldData($altEvent) as $key => $value)
            {
                $alertArray[$key] = $value;
            };
        }
        return $alertArray;
    }

    private function getCustomFieldData($event)
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

    private function getAlertIdFromFp($fp)
    {
        $alertid = null;
        if (is_a($fp, Event::class))
        {
            foreach ($fp->getCustomFieldsValues() as $customfieldvalue)
            {
                if ($customfieldvalue->getCustomField()->getId() == $fp->getCategory()->getAlertfield()->getId())
                {
                    $alertid = $customfieldvalue->getValue();
                }
            }
        }
        return $alertid;
    }

    private function getFields()
    {
        $cf = $this->em->getRepository('Application\Entity\CustomField')->findBy(
            ['category' => $this->fp_cats[0]],
            ['place' => 'ASC'] );

        $fields = [];
        foreach ($cf as $c) {
           $fields[] = $c->getName();
        }
        return $fields;
    }

    private function authFlightPlans($action)
    {
        return ($this->zfcUserAuthentication()->hasIdentity() && $this->isGranted('flightplans.'.$action)) ? true : false;
    }

    private function getEventCategories($classCategory)
    {
        $cats = [];
        foreach ($this->em->getRepository($classCategory)->findAll() as $cat) {
            $cats[] = $cat->getId();
        }
        return $cats;
    }

    private function canCurrentUserAccessOneCategoryOf($cats)
    {
        $readablecat = [];
        foreach ($cats as $cat)
        {
            $category = $this->em->getRepository(Category::class)->find($cat);
            if ($this->zfcUserAuthentication()->hasIdentity())
            {
                $roles = $this->zfcUserAuthentication()
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
                $role = $this->zfcRbacOptions->getGuestRole();
                $roleentity = $this->em->getRepository('Core\Entity\Role')->findOneBy(array(
                    'name' => $role
                ));
                if ($roleentity)
                {
                    if ($category->getReadroles(true)->contains($roleentity)) {
                        $readablecat[] = $category;
                    }
                }
            }
        }
        return (count($readablecat) > 0);
    }

    private function getStartAndEndDateTime($date)
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

    private function getPendingMessages()
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
}
