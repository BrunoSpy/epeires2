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


use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanLogsCommand
 * @package Administration\Command
 */
class CleanLogsCommand extends Command
{
    private EntityManager $entityManager;

    protected static $defaultName = 'epeires2:clean-logs';

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Remove useless logs entries');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectmanager = $this->entityManager;

        //get the number of rows to delete
        $dql = $objectmanager->createQueryBuilder();
        $dql->select('count(log.id)')
            ->from('Application\Entity\Log', 'log')
            ->where($dql->expr()->eq('log.action', '?1'))
            ->setParameter(1, "remove");
        try {
            $removeRowsCount = $dql->getQuery()
                ->getSingleScalarResult();
        } catch(\Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }

        $q = $objectmanager->createQuery('select l from Application\Entity\Log l where l.action = ?1');
        $q->setParameter(1, "remove");
        $iterable = $q->iterate();
        $i = 0;
        $batchSize = 50;
        $output->writeln("Nettoyage des logs en cours... Cette opération peut prendre plusieurs minutes selon la taille de la base de données.");
        try {
            while(($row = $iterable->next()) !== false) {
                $object = $row[0];
                $q2 = $objectmanager->createQuery('select l from Application\Entity\Log l where l.objectId = ?1 and l.objectClass = ?2');
                $q2->setParameters(array(1 => $object->getObjectId(), 2 => $object->getObjectClass()));
                $iterable2 = $q2->iterate();
                while(($row2 = $iterable2->next()) !== false) {
                    $objectmanager->remove($row2[0]);
                }
                $objectmanager->remove($row[0]);
                if(($i % $batchSize) === 0) {
                    $output->writeln( "%.1f %% effectué... \n",(($i / $removeRowsCount)*100) );
                    $objectmanager->flush();
                    $objectmanager->clear();
                }
                $i++;
            }

            $objectmanager->flush();
            $output->writeln("Nettoyage des logs effectué.");
            return Command::SUCCESS;
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}