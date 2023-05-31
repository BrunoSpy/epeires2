<?php
namespace API\V1\Rest\Event;

use Application\Paginator\Adapter;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Application\Entity\CustomField;

class EventResource extends AbstractResourceListener
{
    protected $em;
    protected $eventService;
    
    public function __construct(EntityManager $entityManager, EventService $eventService)
    {
        $this->em = $entityManager;
        $this->eventService = $eventService;
    }

    public function getCategoryNumber($category)
    {
        switch ($category) {
            case "TR - Militaires - Patrouilles":
                return 99;
            case "TR - EPT":
                return 100;
            case "TR - Mission Photo - courses":
                return 101;
            case "TR - Parachutisme":
                return 102;
            case "TR - Laser":
                return 103;
            case "TR - Evénementiel":
                return 104;
            case "TR - Vols Spéciaux":
                return 105;
            case "TR - Divers":
                return 106;
            default:
                return 0;
        }
    }

    public function createEventFromJSON($data)
    {
        if (is_object($data)) {
            $jsonString = json_encode($data);
            if ($jsonString === false) {
                return "Error in converting object to JSON";
            }
            $data = $jsonString;
        }

        $jsonData = json_decode($data, true);
        if ($jsonData === null) {
            return "Invalid JSON data";
        }

        // Extract the values from the JSON
        $category = $jsonData['category'];
        $startDate = $jsonData['dateDebut'];
        $endDate = $jsonData['dateFin'];
        $customfieldfromjson = $jsonData['customFields'];
        $organisation = $jsonData['organisation'];

        // Create the event
        $event = new Event();
        $event->setAuthor($this->em->getRepository('Core\Entity\User')->findOneBy(['username' => 'api']));
        $event->setCategory($this->em->getRepository('Application\Entity\Category')->findOneBy(['name' => $category]));
        $event->setScheduled(0);
        $event->setPunctual(0);
        $event->setStatus($this->em->getRepository('Application\Entity\Status')->find(3));
        $event->setStartdate(\DateTime::createFromFormat('Y-m-d H:i', $startDate));
        $event->setEnddate(\DateTime::createFromFormat('Y-m-d H:i', $endDate));
        $event->setOrganisation($this->em->getRepository('Application\Entity\Organisation')->findOneBy(['name' => $organisation]));

        $customfield = $event->getCategory()->getCustomfields();
        
        foreach ($customfield as $field) {
            $customvalue = new CustomFieldValue();
            $customvalue->setEvent($event);
            $customvalue->setCustomField($this->em->getRepository('Application\Entity\CustomField')->find($field->getId()));
            $customvalue->setValue($customfieldfromjson[$field->getName()]);
            $event->addCustomFieldValue($customvalue);
        }
        $this->em->persist($event);
        $this->em->flush();
        
        return $event;
    }

    public function updateEventFromJSON($event, $data)
    {
        if (is_object($data)) {
            $jsonString = json_encode($data);
            if ($jsonString === false) {
                return "Error in converting object to JSON";
            }
            $data = $jsonString;
        }

        $jsonData = json_decode($data, true);
        if ($jsonData === null) {
            return "Invalid JSON data";
        }

        // Extract the values from the JSON
        $category = $jsonData['category'];
        $startDate = $jsonData['dateDebut'];
        $endDate = $jsonData['dateFin'];
        $customfieldfromjson = $jsonData['customFields'];

        $event->setCategory($this->em->getRepository('Application\Entity\Category')->findOneBy(['name' => $category]));
        $event->setStartdate(\DateTime::createFromFormat('Y-m-d H:i', $startDate));
        $event->setEnddate(\DateTime::createFromFormat('Y-m-d H:i', $endDate));

        $customfield = $event->getCategory()->getCustomfields();
        
        foreach ($customfield as $field) {
            $customvalue = new CustomFieldValue();
            $customvalue->setEvent($event);
            $customvalue->setCustomField($this->em->getRepository('Application\Entity\CustomField')->find($field->getId()));
            $customvalue->setValue($customfieldfromjson[$field->getName()]);
            $event->addCustomFieldValue($customvalue);
        }
        $this->em->persist($event);
        $this->em->flush();
        
        return $event;
    }

    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $eventFromJSON = $this->createEventFromJSON($data);

        return $this->eventService->getJSON($eventFromJSON);
    }
    
    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }
    
    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }
    
    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $event = $this->em->getRepository('Application\Entity\Event')->find($id);
        if($event) {
            $result = array();
            $result['name'] = $this->eventService->getName($event);
            $result['date'] = $event->getStartdate();
            $files = array();
            foreach ($event->getFiles() as $file) {
                $f = array();
                $f['name'] = $file->getName();
                $f['filename'] = $file->getFileName();
                $f['mimetype'] = $file->getMimeType();
                $f['path'] = $file->getPath();
                $f['size'] = $file->getSize();
                $files[] = $f;
            }
            $result['files'] = $files;
            return $result;
        } else {
            return new ApiProblem(404, 'No event found.');
        }
    }
    
    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        return new EventCollection(new Adapter($this->em->getRepository('Application\Entity\Event')));
    }
    
    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }
    
    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }
    
    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }
    
    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        $event = $this->em->getRepository('Application\Entity\Event')->find($id);
        if($event) {
            $eventFromJSON = $this->updateEventFromJSON($event, $data);
            return $this->eventService->getJSON($eventFromJSON);
        } else {
            return new ApiProblem(404, 'No event found.');
        }
        return $this->eventService->getJSON($event);

    }
}
