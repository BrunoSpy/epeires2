<?php 

namespace ApplicationTest\Repository;

use ApplicationTest\Common\TestCase;

class EventRepositoryTest extends TestCase
{
    
    public function testGetOrganisations()
    {
        $em = $this->getEntityManager();
        $organisations = $em->getRepository('Application\Entity\Organisation')->findAll();
        
        $this->assertCount(2, $organisations);
    }
}
