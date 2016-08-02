<?php 
namespace ApplicationTest\Common;

use ApplicationTest\Bootstrap;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;    

    private $categoryService;

    /**
     * Get EntityManager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager) {
            return $this->entityManager;
        }
        //$serviceManager->get('doctrine.entity_resolver.orm_default');
        $serviceManager = Bootstrap::getServiceManager();
        $this->entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
        return $this->entityManager;
    }

    public function getCategoryService()
    {
        if($this->categoryService) {
            return $this->categoryService;
        }
        $serviceManager = Bootstrap::getServiceManager();
        $this->categoryService = $serviceManager->get('categoryfactory');
        return $this->categoryService;
    }
}
