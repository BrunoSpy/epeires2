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

namespace Application\Command;

use Application\Entity\ATFCMCategory;
use Application\Entity\Event;
use Application\Entity\Organisation;
use Core\Entity\User;
use Core\Service\NMB2BService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportRegulationsCommand
 * @package Application\Command
 */
class ImportRegulationsCommand extends Command
{

    protected static $defaultName = 'epeires2:import-regulations';

    protected EntityManager $entitymanager;
    protected NMB2BService $nmb2b;

    public function __construct(EntityManager $entitymanager, NMB2BService $nmb2b)
    {
        $this->entitymanager = $entitymanager;
        $this->nmb2b = $nmb2b;
        parent::__construct();
    }

    /**
     * [--delta=] [--email] <orgshortname> <username>
     */
    protected function configure()
    {
        $this
            ->setDescription('Import regulations from NM B2B.')

            ->addArgument('orgshortname', InputArgument::REQUIRED, 'Which organisation will receive the imported events.')
            ->addArgument('username', InputArgument::REQUIRED, 'User to use to create new events.')
            ->addOption('delta', null, InputOption::VALUE_REQUIRED, 'Days to add to the current day. (Import yesterday : --delta=-1)', 0)
            ->addOption('email', null, InputOption::VALUE_NONE, 'Send an email to IPO if failure.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orgName = $input->getArgument('orgshortname');
        $userName = $input->getArgument('username');

        $organisation = $this->entitymanager->getRepository(Organisation::class)->findOneBy(array('name'=>$orgName));
        $user = $this->entitymanager->getRepository(User::class)->findOneBy(array('username'=>$userName));

        if($organisation == null) {
            $output->writeln("Impossible de trouver l'organisation spécifiée.");
            return Command::FAILURE;
        }
        if($user == null) {
            $output->writeln("Impossible de trouver l'utilisateur spécifié.");
            return Command::FAILURE;
        }

        if($input->getOption('email') == true) {
            $this->nmb2b->activateErrorEmail();
        } else {
            $this->nmb2b->deActivateErrorEmail();
        }

        if($input->getOption('verbose') == true) {
            $this->nmb2b->setVerbose(true);
        }

        $j = $this->getOtion('delta');

        $day = new \DateTime('now');
        if ($j) {
            if ($j > 0) {
                $day->add(new \DateInterval('P' . $j . 'D'));
            } else {
                $j = - $j;
                $interval = new \DateInterval('P' . $j . 'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }
        $day->setTime(0,0);
        $end = clone $day;
        //get regulations for the next day too
        //to avoid having to wait for 00h01 to get regulations
        $end->add(new \DateInterval('P1D'));
        $end->setTime(23,59);

        $startImport = microtime(true);
        $totalDL = 0;
        $totalTR = 0;
        $totalEvents = 0;
        $output->writeln("Lancement du téléchargement des reguls pour " . $organisation->getName());
        foreach ($this->getEntityManager()->getRepository(ATFCMCategory::class)->findBy(array('nmB2B' => true)) as $cat) {
            try {
                $startSeq = microtime(true);
                $output->writeln("Récupération des régulations");
                $regulations = $this->nmb2b->getRegulationsList($day, $end, $cat->getTvs(), $cat->getRegex());
                $dl = microtime(true) - $startSeq;
                $totalDL += $dl;
                $output->writeln("Régulations récupérées en ".$dl." secondes");
            } catch(\Exception $e) {
                $dl = microtime(true) - $startSeq;
                $output->writeln("Erreur au bout de ". $dl . " secondes.");
                $output->writeln("Erreur fatale pendant le téléchargement");
                $output->writeln("Les données téléchargées sont incomplètes");
                $output->writeln("Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré");
                return Command::FAILURE;
            }
            foreach ($regulations->getRegulations() as $regulation) {
                $starttr = microtime(true);
                $internalId = $regulation->getRegulationName();
                if(strlen($cat->getRegex()) == 0 || preg_match($cat->getRegex(), $internalId)) {
                    $totalEvents += $this->getEntityManager()->getRepository(Event::class)->addRegulation($regulation,
                        $cat, $organisation, $user, $day);
                }
                $tr = microtime(true) - $starttr;
                $totalTR += $tr;
            }
        }

        $output->writeln("Fin de l'import des reguls en ".(microtime(true)-$startImport).' secondes');
        $output->writeln( 'Téléchargement : '.$totalDL.' secondes');
        $output->writeln( 'Traitement : '.$totalTR.' secondes');
        $output->writeln( "Nombre d'évènements créés : ".$totalEvents);

        return Command::SUCCESS;
    }

    private function getEntityManager() : EntityManager
    {
        return $this->entitymanager;
    }

}