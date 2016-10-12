<?php
namespace ApplicationTest\Factories;


class CategoryEntityFactoryTest extends \Codeception\Test\Unit
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
    public function testCreateMilFactory()
    {
        $em = $this->getModule('Doctrine2')->em;

        $milcategory = $this->getModule('ZF2')->grabServiceFromContainer('categoryfactory')->createMilCategory();
        $milcategory->setName("Military");
        $milcategory->setShortName("Mil");
        $milcategory->setColor("#00FF00");
        $milcategory->setCompactMode(false);
        $milcategory->setTimeline(true);
        $milcategory->setTimelineConfirmed(false);

        $em->persist($milcategory);
        $em->flush();

        $this->assertTrue(strcmp($milcategory->getName(), "Military") == 0);
    }
}