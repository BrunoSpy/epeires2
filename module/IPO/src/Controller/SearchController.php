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

use Application\Entity\Category;
use Application\Entity\Event;
use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Core\Controller\AbstractEntityManagerAwareController;
use Doctrine\ORM\EntityManager;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\View\Model\JsonModel;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class SearchController extends AbstractEntityManagerAwareController
{
    /**
     * @var EventService
     */
    private $eventService;

    public function __construct(EntityManager $entityManager, EventService $eventService, CustomFieldService $customFieldService)
    {
        parent::__construct($entityManager);
        $this->eventService = $eventService;
        $this->customfieldService = $customFieldService;
    }

    public function indexAction()
    {
        $rootCategories = $this->getEntityManager()->getRepository(Category::class)->getAllRootsAsArray();
        $children = array();
        foreach ($rootCategories as $id => $r) {
            $children[$id] = $this->getEntityManager()->getRepository(Category::class)->getAllChildsAsArray($id, true);
        }

        $results = array();

        $search = $this->params()->fromQuery('search', null);
        $startdate = $this->params()->fromQuery('startdate', null);
        $enddate = $this->params()->fromQuery('enddate', null);
        $categories = $this->params()->fromQuery('categories', null);
        $onlytitle = $this->params()->fromQuery('onlytitle', null);
        if ($search !== null && strlen($search) >= 2 && $startdate !== null) {
            $results = $this->getEntityManager()->getRepository(Event::class)->searchEvents(
                $this->lmcUserAuthentication(),
                new \DateTime($startdate),
                new \DateTime($enddate),
                $search,
                $categories,
                $onlytitle
            );
        }

        $format = $this->params()->fromQuery('format', null);

        switch ($format) {
            case "json":
                return new JsonModel($this->getJSON($results));
            case "csv":
                return $this->getCSV($results);
                break;
            default:
                return array(
                    'rootcat' => $rootCategories,
                    'childrencat' => $children,
                    'results' => $results
                );
        }
    }

    private function getJSON($results)
    {
        $json = array();
        foreach ($results as $event) {
            $json[] = $this->eventService->getJSON($event, true);
        }
        return $json;
    }

    private function getCSV($results)
    {
        $header = array('id', 'name', 'category', 'start', 'end', 'initial start', 'initial end','punctual', 'status', 'star', 'scheduled', 'reccurent');
        $records = array();
        $logsRepo = $this->getEntityManager()->getRepository("Application\Entity\Log");
        $numberFields = 0;
        $numberUpdates = 0;

        //first pass to compute the required number of columns for fields and updates
        foreach ($results as $event) {
            $count = 0;
            foreach ($event->getCustomFieldsValues() as $value) {
                if($value->getCustomField()->isHidden()) //don't display
                    continue;

                $formattedvalue = $this->customfieldService->getFormattedValue($value->getCustomField(), $value->getValue());
                if ($formattedvalue != null) {
                    $count++;
                }
            }
            $numberFields  =max($numberFields, $count);
            $count = 0;
            foreach ($event->getUpdates() as $update) {
                if($update->isHidden())
                    continue;

                $count++;
            }
            $numberUpdates = max($numberUpdates, $count);
        }

        foreach ($results as $event) {
            $record = array();
            $record[] = $event->getId();
            $record[] = $this->eventService->getName($event);
            $record[] = $event->getCategory()->getName();
            $record[] = ($event->getStartdate() ? $event->getStartdate()->format(DATE_RFC2822) : null) ;
            $record[] = ($event->getEnddate() ? $event->getEnddate()->format(DATE_RFC2822) : null);

            $eventLogEntries = $logsRepo->getLogEntries($event);
            $createLog = array_reverse($eventLogEntries)[0];
            $record[] = $createLog->getData()['startdate']->format(DATE_RFC2822);
            $enddateIni = $createLog->getData()['enddate'];
            if($enddateIni !== null) {
                $record[] = $enddateIni->format(DATE_RFC2822);
            } else {
                $record[] = null;
            }
            $record[] = $event->isPunctual() ? true : false;
            $record[] = $event->getStatus()->getName();
            $record[] = $event->isStar() ? true : false;
            $record[] = $event->isScheduled() ? true : false;
            $record[] = $event->getRecurrence() ? true : false;

            $formatter = \IntlDateFormatter::create(
                'fr_FR',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::FULL,
                'UTC',
                \IntlDateFormatter::GREGORIAN,
                'dd LLL HH:mm'
            );
            $count = 0;
            foreach ($event->getCustomFieldsValues() as $value) {
                if($value->getCustomField()->isHidden()) //don't display
                    continue;

                $formattedvalue = $this->customfieldService->getFormattedValue($value->getCustomField(), $value->getValue());
                if ($formattedvalue != null) {
                    $record[] = $value->getCustomField()->getName() .' : '. $formattedvalue;
                    $count++;
                }
            }
            //padding
            for($i = $count; $i <= $numberFields; $i++) {
                $record[] = "";
            }

            $count = 0;
            foreach ($event->getUpdates() as $update) {
                if($update->isHidden())
                    continue;
                $fields[] = $formatter->format($update->getCreatedOn()) . ' : ' . nl2br($update->getText());
                $count++;
            }
            for($i = $count; $i <= $numberUpdates; $i++) {
                $record[] = "";
            }
            $records[] = $record;
        }

        for($i=1; $i <= $numberFields; $i++) {
            $header[] = "Champ " . $i;
        }
        for($i=1; $i <= $numberUpdates; $i++) {
            $header[] = "Note " . $i;
        }


        $response = new Response();

        $fp = fopen('php://output', 'w');
        ob_start();
        fputcsv($fp, $header, ',', '"');
        foreach ($records as $i => $item)
        {
            try
            {
                fputcsv($fp, $item, ',', '"');
            }
            catch (\Exception $ex)
            {
                ob_end_clean();
                throw $ex;
            }
        }
        fclose($fp);
        $response->setContent(ob_get_clean());

        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename="export.csv"',
        ));

        return $response;
    }

}
