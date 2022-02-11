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
use Application\Entity\Status;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanDeletedEventsCommand
 * @package Administration\Command
 */
class CleanDeletedEventsCommand extends Command
{
    private EntityManager $entityManager;

    protected static $defaultName = 'epeires2:clean-deleted-events';

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Permanently erase recurrent events (past and future) with deleted status')
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

        $eventsToDelete = 0;

        //count events
        $dql = $this->entityManager->createQueryBuilder();
        $dql->select('count(e.id)')
            ->from('Application\Entity\Event', 'e')
            ->where($dql->expr()->eq('e.organisation', '?1'))
            ->andWhere($dql->expr()->eq('e.status', '?2'))
            ->andWhere($dql->expr()->isNotNull('e.recurrence'))
            ->setParameter(1, $organisation->getId())
            ->setParameter(2, Status::DELETED);
        try {
            $eventsToDelete = $dql->getQuery()
                ->getSingleScalarResult();
        } catch(\Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output, $eventsToDelete);

        $batchSize = 20;
        $i = 0;
        $q = $this->entityManager->createQuery(
            'select e from Application\Entity\Event e where e.organisation = ?1 and e.status = ?2 and e.recurrence IS NOT NULL');
        $q->setParameter(1, $organisation->getId());
        $q->setParameter(2, Status::DELETED);
        $iterable = $q->toIterable();

        $progressBar->start();

        try {
            foreach ($iterable as $row) {
                $this->entityManager->remove($row);
                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                $progressBar->advance();
                ++ $i;
            }
            $this->entityManager->flush();
            $progressBar->finish();
            $output->writeln("Suppression de ".$i." évènements réussie.");
            return Command::SUCCESS;
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}