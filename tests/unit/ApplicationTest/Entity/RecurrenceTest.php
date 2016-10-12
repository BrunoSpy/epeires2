<?php
namespace ApplicationTest\Entity;


class RecurrenceTest extends \Codeception\Test\Unit
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
    public function testGetRSet()
    {
        $recurrence = $this->getModule('Doctrine2')->em->getRepository('Application\Entity\Recurrence')->find(1);

        $rset = $recurrence->getRSet();

        $this->assertCount(25, $rset);
    }
}