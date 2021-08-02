<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Administration\Command;

use Application\Entity\AfisCategory;
use Application\Entity\AlertCategory;
use Application\Entity\FieldCategory;
use Application\Entity\FlightPlanCategory;
use Application\Entity\InterrogationPlanCategory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitBTIVDBCommand
 * @package Administration\Command
 */
class InitBTIVDBCommand extends Command {

    protected static $defaultName = 'epeires2:initbtivdb';

    private EntityManager $entityManager;
    private $categoryfactory;

    public function __construct(EntityManager $entityManager, $categoryfactory)
    {
        $this->entityManager = $entityManager;
        $this->categoryfactory = $categoryfactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create mandatory categories for BTIV module activation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = $this->entityManager;

        //vérification du stade initial de la base de données
        //pas de categories BTIV (afis/pln/alertes/pio/terrains)
        $nAfisCat = count($objectManager->getRepository(AfisCategory::class)->findAll());
        $nPlnCat = count($objectManager->getRepository(FlightPlanCategory::class)->findAll());
        $nAltCat = count($objectManager->getRepository(AlertCategory::class)->findAll());
        $nPiCat = count($objectManager->getRepository(InterrogationPlanCategory::class)->findAll());
        $nFieldCat = count($objectManager->getRepository(FieldCategory::class)->findAll());

        if (array_sum([$nAfisCat, $nPlnCat, $nAltCat, $nPiCat, $nFieldCat]) > 0) {
            $output->writeln('Impossible d\'initialiser les catégories btiv dans la base de données : des modifications ont déjà été apportées.');
            return Command::FAILURE;
        }
        try {
            //ajout de la catégorie d'événement AFIS
            $afisCat = $this->categoryfactory->createAfisCategory();
            $afisCat->setName("AFIS");
            $afisCat->setShortName("AF");
            $afisCat->setCompactMode(0);
            $afisCat->setTimelineConfirmed(0);
            $afisCat->setColor("#008000");

            $objectManager->persist($afisCat);

            //ajout de la catégorie d'événement PLN
            $plnCat = $this->categoryfactory->createFlightPlanCategory();
            $plnCat->setName("GESTION PLN");
            $plnCat->setShortName("PLN");
            $plnCat->setCompactMode(0);
            $plnCat->setTimelineConfirmed(0);
            $plnCat->setColor("#0000FF");

            $objectManager->persist($plnCat);

            //ajout de la catégorie d'événement Alerte
            $AltCat = $this->categoryfactory->createAlertCategory();
            $AltCat->setName("ALERTES");
            $AltCat->setShortName("ALT");
            $AltCat->setCompactMode(0);
            $AltCat->setTimelineConfirmed(0);
            $AltCat->setColor("#FF0000");

            $objectManager->persist($AltCat);

            //ajout de la catégorie d'événement PIO
            $ipCat = $this->categoryfactory->createInterrogationPlanCategory();
            $ipCat->setName("PIO/PIA");
            $ipCat->setShortName("PI");
            $ipCat->setCompactMode(0);
            $ipCat->setTimelineConfirmed(0);
            $ipCat->setColor("#FF6600");

            $objectManager->persist($ipCat);

            //ajout de la catégorie d'événement terrains interrogés pour les PIO
            $fCat = $this->categoryfactory->createFieldCategory();
            $fCat->setName("TERRAINS");
            $fCat->setShortName("TER");
            $fCat->setCompactMode(0);
            $fCat->setTimelineConfirmed(0);
            $fCat->setColor("#800080");
            $fCat->setParent($ipCat);

            $objectManager->persist($fCat);

            $objectManager->flush();
            $output->writeln("Données BTIV correctement initialisées");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            // echo $e->getMessage();
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

}