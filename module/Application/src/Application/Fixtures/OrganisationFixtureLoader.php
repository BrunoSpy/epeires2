<?php
namespace ApplicationFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Application\Entity\Organisation;

class OrganisationFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $organisation = new Organisation();
        $organisation->setName('CRNA-T');
        $organisation->setShortname('LFTT');
        $organisation->setIpoNumber('0611223344');
        $organisation->setLongname('CRNA Test');
        $organisation->setAddress('1 rue de test\n12345 Test');
        $organisation->setIpoEmail('test@acgv.fr');
        
        $manager->persist($organisation);
        $manager->flush();
    }
}