<?php
namespace API\V1\Rest\Event;

use Application\Paginator\Adapter;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class EventResource extends AbstractResourceListener
{
    protected $em;
    protected $eventService;
    
    public function __construct(EntityManager $entityManager, EventService $eventService)
    {
        $this->em = $entityManager;
        $this->eventService = $eventService;
    }
    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        return new ApiProblem(405, 'The POST method has not been defined');
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
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
