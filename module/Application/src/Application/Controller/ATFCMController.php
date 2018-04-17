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

namespace Application\Controller;


use Application\Entity\ATFCMCategory;
use Application\Entity\Event;
use Core\Controller\AbstractEntityManagerAwareController;
use Core\NMB2B\RegulationListReply;
use Core\Service\NMB2BService;
use Doctrine\ORM\EntityManager;
use Zend\Console\Request as ConsoleRequest;

/**
 * Class ATFCMController
 * @package Application\Controller
 */
class ATFCMController extends AbstractEntityManagerAwareController
{

    private $nmb2b;

    public function __construct(EntityManager $entityManager, NMB2BService $nmb2b)
    {
        parent::__construct($entityManager);
        $this->nmb2b = $nmb2b;
    }

    public function importRegulationsAction()
    {

        $request = $this->getRequest();

        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $j = $request->getParam('delta');

        $org = $request->getParam('orgshortname');

        $email = $request->getParam('email');

        if($email) {
            $this->nmb2b->activateErrorEmail();
        } else {
            $this->nmb2b->deActivateErrorEmail();
        }

        $organisation = $this->getEntityManager()->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));

        if (! $organisation) {
            throw new \RuntimeException('Unable to find organisation.');
        }

        $username = $request->getParam('username');

        $user = $this->getEntityManager()->getRepository('Core\Entity\User')->findOneBy(array(
            'username' => $username
        ));

        if (! $user) {
            throw new \RuntimeException('Unable to find user.');
        }

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
        $end->setTime(23,59);

        $startImport = microtime(true);
        $totalDL = 0;
        $totalTR = 0;
        $totalEvents = 0;
        echo "Lancement du téléchargement des reguls pour " . $organisation->getName()."\n";
        foreach ($this->getEntityManager()->getRepository(ATFCMCategory::class)->findBy(array('nmB2B' => true)) as $cat) {
            try {
                $startSeq = microtime(true);
                echo "Récupération des régulations\n";
                $regulations = $this->nmb2b->getRegulationsList($day, $end, $cat->getTvs());
                $dl = microtime(true) - $startSeq;
                $totalDL += $dl;
                echo "Régulations récupérées en ".$dl." secondes\n";
            } catch(\RuntimeException $e) {
                $dl = microtime(true) - $startSeq;
                echo "Erreur au bout de ". $dl . " secondes.\n";
                echo "Erreur fatale pendant le téléchargement"."\n";
                echo "Les données téléchargées sont incomplètes"."\n";
                echo "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré"."\n";
                return;
            }
            foreach ($regulations->getRegulations() as $regulation) {
                $starttr = microtime(true);
                $totalEvents += $this->getEntityManager()->getRepository(Event::class)->addRegulation($regulation, $cat, $organisation, $user, $day);
                $tr = microtime(true) - $starttr;
                $totalTR += $tr;
            }
        }

        echo "Fin de l'import des reguls en ".(microtime(true)-$startImport).' secondes'."\n";
        echo 'Téléchargement : '.$totalDL.' secondes'."\n";
        echo 'Traitement : '.$totalTR.' secondes'."\n";
        echo "Nombre d'évènements créés : ".$totalEvents."\n";
    }

}