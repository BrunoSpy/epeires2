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

use Application\Entity\Organisation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteEventsCommand
 * @package Administration\Command
 */
class DeleteEventsCommand extends Command
{

    protected static $defaultName = 'epeires2:delete-events';

    protected EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * epeires2:delete-events <orgshortname>
     */
    protected function configure()
    {
        $this
            ->setDescription('Safely delete events (but not models) from the database.')
            ->addArgument('orgshortname', InputArgument::REQUIRED, 'Which organisation to use.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $org = $input->getArgument('orgshortname');
        $organisation = $this->entityManager->getRepository(Organisation::class)->findOneBy(array('name' => $org));
        if($organisation == null) {
            $output->writeln('Impossible de trouver l\`organisation spécifiée');
            return Command::FAILURE;
        }

        $batchSize = 20;
        $i = 0;
        $q = $this->entityManager->createQuery('select e from Application\Entity\Event e where e.organisation = ?1');
        $q->setParameter(1, $organisation->getId());

        $iterable = $q->iterate();

        try {
            while (($row = $iterable->next()) !== false) {
                $this->entityManager->remove($row[0]);
                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                ++ $i;
            }
            $this->entityManager->flush();
            $output->writeln("Suppression des évènements réussie.");
            return Command::SUCCESS;
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }

    }
}