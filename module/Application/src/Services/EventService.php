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
namespace Application\Services;

use Application\Entity\EventUpdate;
use Application\Entity\FrequencyCategory;
use Application\Entity\MilCategory;
use Application\Entity\PredefinedEvent;
use Doctrine\ORM\EntityManager;
use Application\Entity\Event;
use Application\Entity\AbstractEvent;

/**
 *
 * @author Bruno Spyckerelle
 */
class EventService
{

    /**
     * Entity Manager
     */
    private $em;

    private $rbac;

    private $auth;

    private $customfieldService;

    public function __construct(EntityManager $entityManager, $authService, $rbac, $customfieldservice)
    {
        $this->em = $entityManager;
        $this->rbac = $rbac;
        $this->auth = $authService;
        $this->customfieldService = $customfieldservice;
    }

    public function getRbac()
    {
        return $this->rbac;
    }

    /**
     * An event is modifiable if the current user is the author of the event or if he has the 'events.write' permission
     * @param Event $event
     * @return boolean
     */
    public function isModifiable(Event $event)
    {
        if ($this->auth->hasIdentity()) {
            if ($this->getRbac()->isGranted('events.write') || ($event->getAuthor() && $event->getAuthor()->getId() === $this->auth->getIdentity()->getId())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the name of an event depending on the title field of the category.
     * If no title field is set, returns the event's id
     *
     * @param $event
     * @return string
     */
    public function getName(AbstractEvent $event)
    {
        if ($event instanceof PredefinedEvent) {
            if ($event->getParent() == null && $event->getName()) {
                return $event->getName();
            }
        }

        $name = $event->getId();

        $category = $event->getCategory();

        if ($category instanceof FrequencyCategory) {
            $freqid = 0;
            $otherfreqid = 0;
            foreach ($event->getCustomFieldsValues() as $value) {
                if ($value->getCustomField()->getId() == $category->getFrequencyField()->getId()) {
                    $freqid = $value->getValue();
                }
                if ($value->getCustomField()->getId() == $category->getOtherFrequencyField()->getId()) {
                    $otherfreqid = intval($value->getValue());
                }
            }
            if ($freqid != 0) {
                $freq = $this->em->getRepository('Application\Entity\Frequency')->find($freqid);
                if ($freq) {
                    if ($otherfreqid != 0) {
                        $otherfreq = $this->em->getRepository('Application\Entity\Frequency')->find($otherfreqid);
                        if ($otherfreq) {
                            $name = $freq->getName() . ' → ' . $otherfreq->getName() . ' ' . $otherfreq->getValue();
                        }
                    } else {
                        $name = $freq->getName() . ' ' . $freq->getValue();
                    }
                }
            }
        } else if ($category instanceof MilCategory) {
            $namefield = $event->getCustomFieldValue($category->getFieldname());
            $name = "???"; // TODO $namefield ne peut jamais être vide !!
            if ($namefield) {
                $name = $this->customfieldService->getFormattedValue($namefield->getCustomField(), $namefield->getValue());
            }
            $plancherfield = $event->getCustomFieldValue($category->getLowerLevelField());
            $plafondfield = $event->getCustomFieldValue($category->getUpperLevelField());
            $name .= ' (' . ($plancherfield !== null ? str_pad($plancherfield->getValue(), 3, '0', STR_PAD_LEFT) : '--') . '/' . ($plafondfield !== null ? str_pad($plafondfield->getValue(), 3, '0', STR_PAD_LEFT) : '--') . ')';
        } else {
            $titlefield = $category->getFieldname();
            $titlefield2 = $category->getFieldname2();

            if ($titlefield) {
                foreach ($event->getCustomFieldsValues() as $fieldvalue) {
                    if ($fieldvalue->getCustomField()->getId() == $titlefield->getId()) {
                        $tempname = $this->customfieldService->getFormattedValue($fieldvalue->getCustomField(), $fieldvalue->getValue());
                        
                        if ($tempname) {
                            $name = ($category->getParent() != null ? $category->getShortName() : '') . ' ' . $tempname;
                        }
                    }
                }
            }
            if ($titlefield2 != null) {
                foreach ($event->getCustomFieldsValues() as $fieldvalue) {
                    if ($fieldvalue->getCustomField()->getId() == $titlefield2->getId()) {
                        $tempname2 = $this->customfieldService->getFormattedValue($fieldvalue->getCustomField(), $fieldvalue->getValue());
                        
                        if ($tempname2) {
                            $name = $name." / ".$tempname2;
                        }
                    }
                }
            }
        }
        return $name;
    }

    protected function sortbydate($a, $b)
    {
        return \DateTime::createFromFormat(DATE_RFC2822, $a) > \DateTime::createFromFormat(DATE_RFC2822, $b);
    }

    public function getUpdateAuthor(EventUpdate $eventupdate)
    {
        $repo = $this->em->getRepository('Application\Entity\Log');

        $logentries = $repo->getLogEntries($eventupdate);
        if (count($logentries) >= 1 && $logentries[count($logentries) - 1]->getAction() == "create") {
            return $logentries[count($logentries) - 1]->getUsername();
        } else {
            return "Unknown";
        }
    }

    public function getLastUpdateAuthorName(Event $action)
    {
        $repo = $this->em->getRepository('Application\Entity\Log');
        $logentries = $repo->getLogEntries($action);
        if (count($logentries) >= 1) {
            return $logentries[0]->getUsername();
        } else {
            return $action->getAuthor()->getUserName();
        }
    }

    /**
     * Returns an array :
     * datetime => array('date' => datetime object,
     * 'user' => user name,
     * 'changes' => array(array ('fieldname', 'oldvalue', 'newvalue'))
     * )
     *
     * @param Event $event
     * @return array
     */
    public function getHistory(Event $event)
    {
        $history = array();

        $repo = $this->em->getRepository('Application\Entity\Log');
        $customfieldslogs = $this->em->getRepository('Application\Entity\CustomFieldLog');

        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd LLL, HH:mm');

        // history of event
        $logentries = $repo->getLogEntries($event);
        if (count($logentries) >= 1 && $logentries[count($logentries) - 1]->getAction() == "create") {
            $ref = null;
            foreach (array_reverse($logentries) as $logentry) {
                if (! $ref) { // set up reference == "create" entry
                    if (! array_key_exists($logentry->getLoggedAt()->format(DATE_RFC2822), $history)) {
                        $ref = $logentry->getData();
                        $entry = array();
                        $entry['date'] = $logentry->getLoggedAt();
                        $entry['user'] = $logentry->getUsername();
                        $history[$logentry->getLoggedAt()->format(DATE_RFC2822)] = $entry;
                    }
                    $historyentry = array();
                    $historyentry['fieldname'] = 'create';
                    $history[$logentry->getLoggedAt()->format(DATE_RFC2822)]['changes'][] = $historyentry;
                } else {
                    foreach ($logentry->getData() as $key => $value) {
                        // sometimes log stores values that didn't changed
                        if (array_key_exists($key, $ref) && $ref[$key] != $value) {
                            if (!array_key_exists($logentry->getLoggedAt()->format(DATE_RFC2822), $history)) {
                                $entry = array();
                                $entry['date'] = $logentry->getLoggedAt();
                                $entry['changes'] = array();
                                $entry['user'] = $logentry->getUsername();
                                $history[$logentry->getLoggedAt()->format(DATE_RFC2822)] = $entry;
                            }
                            $historyentry = array();
                            $historyentry['fieldname'] = $key;
                            if ($key == 'enddate' || $key == 'startdate') {
                                if ($key == 'enddate') {
                                    $historyentry['fieldname'] = "Fin";
                                } elseif ($key == 'startdate') {
                                    $historyentry['fieldname'] = "Début";
                                }
                                $historyentry['oldvalue'] = ($ref[$key] ? $formatter->format($ref[$key]) : '');
                                $historyentry['newvalue'] = ($value ? $formatter->format($value) : null);
                            } else
                                if ($key == 'punctual') {
                                    $historyentry['oldvalue'] = ($ref[$key] ? "Oui" : "Non");
                                    $historyentry['newvalue'] = ($value ? "Oui" : "Non");
                                } elseif ($key == 'status') {
                                    $old = $this->em->getRepository('Application\Entity\Status')->find($ref[$key]['id']);
                                    $new = $this->em->getRepository('Application\Entity\Status')->find($value['id']);
                                    $historyentry['oldvalue'] = $old->getName();
                                    $historyentry['newvalue'] = $new->getName();
                                } elseif ($key == 'impact') {
                                    $old = $this->em->getRepository('Application\Entity\Impact')->find($ref[$key]['id']);
                                    $new = $this->em->getRepository('Application\Entity\Impact')->find($value['id']);
                                    $historyentry['oldvalue'] = $old->getName();
                                    $historyentry['newvalue'] = $new->getName();
                                } elseif ($key == 'mattermostPostId') {
                                    $historyentry['oldvalue'] = '';
                                    $historyentry['newvalue'] = '';
                                } else {
                                    $historyentry['oldvalue'] = $ref[$key];
                                    $historyentry['newvalue'] = $value;
                                }

                            $history[$logentry->getLoggedAt()->format(DATE_RFC2822)]['changes'][] = $historyentry;
                            // update ref
                            $ref[$key] = $value;
                        }
                    }
                }
            }
        }

        // history of customfields
        foreach ($this->em->getRepository('Application\Entity\CustomFieldValue')->findBy(array(
            'event' => $event->getId()
        )) as $customfieldvalue) {
            $fieldlogentries = $customfieldslogs->getLogEntries($customfieldvalue);
            if (count($fieldlogentries) > 1 && $fieldlogentries[count($fieldlogentries) - 1]->getAction() == "create") {
                $ref = null;
                foreach (array_reverse($fieldlogentries) as $fieldlogentry) {
                    if (! $ref) {
                        $ref = $fieldlogentry->getData();
                    } else {
                        foreach ($fieldlogentry->getData() as $key => $value) {
                            if ($ref[$key] != $value) {
                                if (! array_key_exists($fieldlogentry->getLoggedAt()->format(DATE_RFC2822), $history)) {
                                    $entry = array();
                                    $entry['date'] = $fieldlogentry->getLoggedAt();
                                    $entry['changes'] = array();
                                    $entry['user'] = $fieldlogentry->getUsername();
                                    $history[$fieldlogentry->getLoggedAt()->format(DATE_RFC2822)] = $entry;
                                }
                                $historyentry = array();
                                $historyentry['fieldname'] = $customfieldvalue->getCustomField()->getName();
                                $historyentry['oldvalue'] = $this->customfieldService->getFormattedValue($customfieldvalue->getCustomField(), $ref[$key]);
                                $historyentry['newvalue'] = $this->customfieldService->getFormattedValue($customfieldvalue->getCustomField(), $value);
                                $history[$fieldlogentry->getLoggedAt()->format(DATE_RFC2822)]['changes'][] = $historyentry;
                                // update ref
                                $ref[$key] = $value;
                            }
                        }
                    }
                }
            }
        }

        // updates
        foreach ($event->getUpdates() as $update) {
            if (! array_key_exists($update->getCreatedOn()->format(DATE_RFC2822), $history)) {
                $entry = array();
                $entry['date'] = $update->getCreatedOn();
                $entry['changes'] = array();
                $entry['user'] = $this->getUpdateAuthor($update);
                $history[$update->getCreatedOn()->format(DATE_RFC2822)] = $entry;
            }
            $historyentry = array();
            $historyentry['fieldname'] = 'note';
            $historyentry['oldvalue'] = '';
            $historyentry['newvalue'] = $update->getText();
            $history[$update->getCreatedOn()->format(DATE_RFC2822)]['changes'][] = $historyentry;
        }
        // fiche reflexe
        foreach ($event->getChildren() as $child) {
            if (($child->getCategory() instanceof \Application\Entity\ActionCategory) && ! $child->getStatus()->isOpen()) {
                if (! array_key_exists($child->getLastModifiedOn()->format(DATE_RFC2822), $history)) {
                    $entry = array();
                    $entry['date'] = $child->getLastModifiedOn();
                    $entry['changes'] = array();
                    $entry['user'] = $this->getLastUpdateAuthorName($child);
                    $history[$child->getLastModifiedOn()->format(DATE_RFC2822)] = $entry;
                }
                $historyentry = array();
                $historyentry['fieldname'] = 'action';
                $historyentry['oldvalue'] = '';
                $historyentry['newvalue'] = $this->getName($child);
                $historyentry['status'] = $child->getStatus();
                $history[$child->getLastModifiedOn()->format(DATE_RFC2822)]['changes'][] = $historyentry;
            }
        }
        // alertes
        foreach ($event->getChildren() as $child) {
            if (($child->getCategory() instanceof \Application\Entity\AlarmCategory) && ! $child->getStatus()->isOpen()) {
                if (! array_key_exists($child->getLastModifiedOn()->format(DATE_RFC2822), $history)) {
                    $entry = array();
                    $entry['date'] = $child->getLastModifiedOn();
                    $entry['changes'] = array();
                    $entry['user'] = $this->getLastUpdateAuthorName($child);
                    $history[$child->getLastModifiedOn()->format(DATE_RFC2822)] = $entry;
                }
                $historyentry = array();
                $historyentry['fieldname'] = 'alarm';
                $historyentry['oldvalue'] = '';
                $historyentry['newvalue'] = $this->getName($child);
                $historyentry['status'] = $child->getStatus();
                $history[$child->getLastModifiedOn()->format(DATE_RFC2822)]['changes'][] = $historyentry;
            }
        }
        uksort($history, array(
            $this,
            "sortbydate"
        ));
        return $history;
    }

    public function getJSON(Event $event, $extendedFormat = false) {
        $objectManager = $this->em;
        $logsRepo = $objectManager->getRepository("Application\Entity\Log");
        $customfieldslogs = $objectManager->getRepository("Application\Entity\CustomFieldLog");
        $json = array(
            'id' => $event->getId(),
            'name' => $this->getName($event),
            'modifiable' => $this->isModifiable($event) ? true : false,
            'deleteable' => $this->getRbac()->isGranted('events.delete') ? true : false,
            'readonly' => $event->isReadOnly() ? true : false,
            'start_date' => ($event->getStartdate() ? $event->getStartdate()->format(DATE_RFC2822) : null),
            'end_date' => ($event->getEnddate() ? $event->getEnddate()->format(DATE_RFC2822) : null),
            'punctual' => $event->isPunctual() ? true : false,
            'category_root' => ($event->getCategory()->getParent() ? $event->getCategory()
                ->getParent()
                ->getName() : $event->getCategory()->getName()),
            'category_root_id' => ($event->getCategory()->getParent() ? $event->getCategory()
                ->getParent()
                ->getId() : $event->getCategory()->getId()),
            'category_root_short' => ($event->getCategory()->getParent() ? $event->getCategory()
                ->getParent()
                ->getShortName() : $event->getCategory()->getShortName()),
            'category' => $event->getCategory()->getName(),
            'category_id' => $event->getCategory()->getId(),
            'category_short' => $event->getCategory()->getShortName(),
            'category_compact' => $event->getCategory()->isCompactMode() ? true : false,
            'category_place' => ($event->getCategory()->getParent() ? $event->getCategory()->getPlace() : - 1),
            'status_name' => $event->getStatus()->getName(),
            'status_id' => $event->getStatus()->getId(),
            'impact_value' => ($event->getImpact() ? $event->getImpact()->getValue() : 60),
            'impact_name' => ($event->getImpact() ? $event->getImpact()->getName() : "Mineur"),
            'impact_style' => ($event->getImpact() ? $event->getImpact()->getStyle() : "info"),
            'files' => count($event->getFiles()),
            'url_file1' => (count($event->getFiles()) > 0 ? $event->getFiles()[0]->getPath() : ''),
            'star' => $event->isStar() ? true : false,
            'scheduled' => $event->isScheduled() ? true : false,
            'recurr' => $event->getRecurrence() ? true : false,
            'recurr_readable' => $event->getRecurrence() ? $event->getRecurrence()->getHumanReadable() : '',
            'mattermostid' => $event->getMattermostPostId()
        );

        $fields = array();
        $formatterSimple = \IntlDateFormatter::create(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            'dd LLL HH:mm'
        );
        $milestones = array();
        foreach ($event->getCustomFieldsValues() as $value) {
            if(!$value->getCustomField())
                continue;
            if($value->getCustomField()->isHidden()) //don't display
                continue;
            if($value->getCustomField()->isTraceable()) {
                foreach(array_reverse($customfieldslogs->getLogEntries($value)) as $log) {
                    $name = $formatterSimple->format($log->getLoggedAt()) . ' ' . $value->getCustomField()->getName();
                    $i = 0;
                    while(array_key_exists($name, $fields)) {
                        $i++;
                        $name = $formatterSimple->format($log->getLoggedAt()) . '-' . $i . ' ' . $value->getCustomField()->getName();
                    }
                    $formattedvalue = $this->customfieldService->getFormattedValue(
                        $value->getCustomField(),
                        $log->getData()["value"]
                    );
                    $fields[$name] = $formattedvalue;
                }
            } else {
                $formattedvalue = $this->customfieldService->getFormattedValue($value->getCustomField(), $value->getValue());
                if ($formattedvalue != null) {
                    $fields[$value->getCustomField()->getName()] = $formattedvalue;
                }
            }
            $i = 0;
            if($value->getCustomField()->isMilestone()) {
                foreach(array_reverse($customfieldslogs->getLogEntries($value)) as $log) {
                    //store only changes -> skip firt log
                    if($i > 0) {
                        $milestones[] = $log->getLoggedAt()->format(DATE_RFC2822);
                    }
                    $i++;
                }
            }
        }

        $json['milestones'] = $milestones;

        if($extendedFormat) {
            $eventLogEntries = $logsRepo->getLogEntries($event);
            $createLog = array_reverse($eventLogEntries)[0];
            $json['startdate_ini'] = $createLog->getData()['startdate']->format(DATE_RFC2822);
            $enddateIni = $createLog->getData()['enddate'];
            if($enddateIni !== null) {
                $json['enddate_ini'] = $enddateIni->format(DATE_RFC2822);
            } else {
                $json['enddate_ini'] = null;
            }
        }

        $formatter = \IntlDateFormatter::create(
            \Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            'dd LLL, HH:mm'
        );
        foreach ($event->getUpdates() as $update) {
            if($update->isHidden())
                continue;
            $key = $formatter->format($update->getCreatedOn());
            $tempkey = $formatter->format($update->getCreatedOn());
            $i = 0;
            while (array_key_exists($tempkey, $fields)) {
                $i ++;
                $tempkey = $key . '-' . $i;
            }
            $fields[$tempkey] = nl2br($update->getText());
        }
        $json['fields'] = $fields;

        return $json;
    }
}