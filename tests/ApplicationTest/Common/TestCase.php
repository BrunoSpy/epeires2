<?php 
namespace ApplicationTest\Common;

use ApplicationTest\Bootstrap;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;    
    
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
}
