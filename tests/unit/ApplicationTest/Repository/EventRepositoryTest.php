<?php
namespace ApplicationTest\Repository;


class EventRepositoryTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testOrganisation()
    {
        $em = $this->getModule('Doctrine2')->em;
        $organisations = $em->getRepository('Application\Entity\Organisation')->findAll();

        $this->assertCount(2, $organisations);
    }
}