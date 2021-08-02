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

use Application\Entity\MilCategory;
use Application\Entity\Organisation;
use Core\Entity\User;
use Core\Service\NMB2BService;
use Core\Service\MAPDService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportZonesMilCommand
 * @package Application\Command
 */
class ImportZonesMilCommand extends Command
{
    private EntityManager $entityManager;
    private NMB2BService $nmb2b;
    private MAPDService $mapd;

    protected static $defaultName = 'epeires2:import-zones-mil';

    public function __construct(EntityManager $entityManager, NMB2BService $nmb2b, MAPDService $mapd)
    {
        $this->entityManager = $entityManager;
        $this->nmb2b = $nmb2b;
        $this->mapd = $mapd;
        parent::__construct(null);
    }

    /**
     * [--delta=] [--email] service orgshortname username
     */
    protected function configure()
    {
        $this
            ->setDescription('Import military activity from NM B2B or MAPD.')
            ->addArgument('origin', InputArgument::REQUIRED, 'Which service to use : mapd OR nmb2b')
            ->addArgument('organisation', InputArgument::REQUIRED, 'Organisation to affect imported events')
            ->addArgument('user', InputArgument::REQUIRED, 'Username to use to create events')
            ->addOption('delta', null, InputOption::VALUE_REQUIRED, 'Days to add to the current day. (Import yesterday : --delta=-1)', 0)
            ->addOption('email', null, InputOption::VALUE_NONE, 'Send an email to IPO if failure.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
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


        $verbose = false;
        if($input->getOption('verbose') == true){
            $verbose = true;
        }
        $email = false;
        if($input->getOption('email') == true) {
            $email = true;
        }
        $service = $input->getArgument('origin');
        if(strcmp($service, 'nmb2b') == 0)  {
            return $this->importNMB2B($day, $organisation, $user, $email, $verbose, $output);
        } elseif (strcmp($service, 'mapd') == 0) {
            return $this->importMAPD($day, $organisation, $user, $email, $output);
        } else {
            $output->writeln('Service '.$service.' non reconnue');
            return Command::FAILURE;
        }

    }

    private function importNMB2B(\DateTime $day, Organisation $organisation, User $user, $email, $verbose, $output): int
    {
        if($email) {
            $this->nmb2b->activateErrorEmail();
        } else {
            $this->nmb2b->deActivateErrorEmail();
        }

        if($verbose) {
            $this->nmb2b->setVerbose(true);
        } else {
            $this->nmb2b->setVerbose(false);
        }

        $startImport = microtime(true);
        $totalDL = 0;
        $totalTR = 0;
        $totalEvents = 0;
        $output->writeln("Lancement du téléchargement de l'AUP pour " . $organisation->getName());

        try {
            $startSeq = microtime(true);
            $output->writeln( "Récupération du nombre de séquences.");
            $eaupchain = $this->nmb2b->getEAUPCHain($day);
            $dl = microtime(true) - $startSeq;
            $totalDL += $dl;
            $output->writeln( "Séquences récupérées en ".$dl." secondes");
        } catch(\Exception $e) {
            $dl = microtime(true) - $startSeq;
            $output->writeln( "Erreur au bout de ". $dl . " secondes.");
            $output->writeln( "Erreur fatale pendant le téléchargement");
            $output->writeln( "Les données téléchargées sont incomplètes");
            $output->writeln( "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré");
            return Command::FAILURE;
        }

        $lastAUPSequenceNumber = $eaupchain->getAUPSequenceNumber();

        //$lastSequence = $eaupchain->getLastSequenceNumber();
        $milcats = $this->getEntityManager()->getRepository('Application\Entity\MilCategory')->findBy(array(
            'archived' => false,
            'origin' => MilCategory::NMB2B
        ));

        //filter by state
        $designators = array();
        foreach ($milcats as $cat){
            $filter = $cat->getFilter();
            if(strlen($filter) > 2) {
                $start = substr($filter, 0, 2);
                if(!in_array($start, $designators)){
                    $designators[] = $start;
                }
            }
        }
        //foreach state
        //to avoid multiple requests, we do not take into account filter but only the entire state
        foreach ($designators as $designator) {
            try {
                $startSeq = microtime(true);
                $output->writeln( "Téléchargement des zones ".$designator.", séquence ".$lastAUPSequenceNumber);
                $eauprsas = $this->nmb2b->getEAUPRSA($designator.'*', $day, $lastAUPSequenceNumber);
                $dl = microtime(true) - $startSeq;
                $totalDL += $dl;
                $output->writeln( "Téléchargement terminé en ".$dl." secondes");
            } catch(\Exception $e) {
                $dl = microtime(true) - $startSeq;
                $output->writeln( "Erreur au bout de ". $dl . " secondes.");
                $output->writeln( "Erreur fatale pendant le téléchargement");
                $output->writeln( "Les données téléchargées sont incomplètes");
                $output->writeln( "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré");
                //abort import
                //email sent by service
                return Command::FAILURE;
            }
            $startEpeires = microtime(true);

            $output->writeln( "Création des évènements ".$designator.' séquence '.$lastAUPSequenceNumber." dans Epeires...");
            $evts = 0;
            foreach ($milcats as $cat) {
                $evts += $this->getEntityManager()->getRepository('Application\Entity\Event')->addZoneMilEvents($eauprsas, $cat, $organisation, $user);
            }
            $tr = microtime(true) - $startEpeires;
            $totalTR += $tr;
            $output->writeln( $evts . " évènements créés en ".$tr.' secondes');
            $totalEvents += $evts;
        }
        //}
        $output->writeln( "Fin de l'import de l'AUP en ".(microtime(true)-$startImport).' secondes');
        $output->writeln( 'Téléchargement : '.$totalDL.' secondes');
        $output->writeln( 'Traitement : '.$totalTR.' secondes');
        $output->writeln( "Nombre d'évènements créés : ".$totalEvents);
        return Command::SUCCESS;
    }

    /**
     * @param \DateTime $day
     * @param Organisation $organisation
     * @param User $user
     * @param $email
     * @param $output
     * @return int
     */
    private function importMAPD(\DateTime $day, Organisation $organisation, User $user, $email, $output) : int
    {

        $milcats = $this->getEntityManager()->getRepository(MilCategory::class)->findBy(array('archived'=>false, 'origin' => MilCategory::MAPD));
        foreach ($milcats as $milcat) {
            try {
                $this->mapd->updateCategory($milcat, $day, $user);
                $output->writeln( $milcat->getName(). ' importée');
            } catch (\Exception $e) {
                $output->writeln( $e->getMessage());
                return Command::FAILURE;
            }
        }
        return Command::SUCCESS;
    }

    private function getEntityManager() : EntityManager
    {
        return $this->entityManager;
    }
}