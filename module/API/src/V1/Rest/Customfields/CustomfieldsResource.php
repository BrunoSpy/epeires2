<?php
namespace API\V1\Rest\Customfields;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\Stdlib\Parameters;
use Doctrine\ORM\EntityManager;
use Application\Services\EventService;
use Application\Entity\CustomField;
use Application\Entity\Category;

class CustomfieldsResource extends AbstractResourceListener
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

        $category = $this->em->getRepository('Application\Entity\Category')->findOneBy(['name' => $jsonData["categoryname"]]);

        if (isset($category)) {
            $customfield = $category->getCustomfields();
            
            $customfields = array();
            foreach($customfield as $field) {
                $customfields[$field->getName()] = '';
            }
            $json['category'] = $category->getName();
            $json['organisation'] = '';
            $json['dateDebut'] = '';
            $json['dateFin'] = '';
            $json['customFields'] = $customfields;
            return $json;
        } else {
            return array("Category not found");
        }
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
        $category = $this->em->getRepository('Application\Entity\Category')->find($id);

        $customfield = $category->getCustomfields();
        
        $customfields = array();
        foreach($customfield as $field) {
            $customfields[$field->getName()] = '';
        }
        
        return $customfields;
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array|Parameters $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
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
