<?php
namespace ApplicationTest\Repository;


use Application\Entity\Category;

class CategoryRepositoryTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $em = $this->getModule('Doctrine2')->em;

        $milcategory = $this->getModule('ZF2')->grabServiceFromContainer('categoryfactory')->createMilCategory();
        $milcategory->setName("ToDelete");
        $milcategory->setShortName("Mil");
        $milcategory->setColor("#00FF00");
        $milcategory->setCompactMode(false);
        $milcategory->setTimelineConfirmed(false);

        $em->persist($milcategory);
        $em->flush();
    }

    protected function _after()
    {
    }

    // tests
    public function testDelete()
    {
        $em = $this->getModule('Doctrine2')->em;
        $category = $em->getRepository('Application\Entity\Category')->findOneBy(array('name' => "ToDelete"));

        $em->getRepository(Category::class)->delete($category);

        $this->assertCount(0, $em->getRepository(Category::class)->findBy(array('name'=>"ToDelete")));
    }
}