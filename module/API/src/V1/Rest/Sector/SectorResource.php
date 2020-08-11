<?php
namespace API\V1\Rest\Sector;

use Application\Paginator\Adapter;
use Doctrine\ORM\EntityManager;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class SectorResource extends AbstractResourceListener
{
    protected $em;
    protected $eventService;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
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
        $sector = $this->em->getRepository('Application\Entity\Sector')->findOneBy(array('name' => $id));
        $result = array();
        if($sector) {
            $result['id'] = $sector->getId();
            $result['name'] = $sector->getName();
            if($sector->getFrequency()) {
                $result['default_frequency_id'] = $sector->getFrequency()->getId();
                $result['default_frequency_value'] = $sector->getFrequency()->getValue();
                $events = $this->em->getRepository('Application\Entity\Event')->getFrequencyEvents($sector->getFrequency());
                if(count($events) == 1) {
                    $category = $events[0]->getCategory();
                    $currentFrequencyId = $events[0]->getCustomFieldValue($category->getOtherFrequencyField())->getValue();
                    $currentFrequency = $this->em->getRepository('Application\Entity\Frequency')->find($currentFrequencyId);
                    $result['current_frequency_id'] = $currentFrequency->getId();
                    $result['current_frequency_value'] = $currentFrequency->getValue();
                }
            }
            return $result;
        } else {
            return new ApiProblem(404, 'No sector found with this name.');
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
        return new SectorCollection(new Adapter($this->em->getRepository('Application\Entity\Sector')));
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
