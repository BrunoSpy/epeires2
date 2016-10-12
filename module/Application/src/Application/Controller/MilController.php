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

use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class MilController extends AbstractActionController
{

    public function importNMB2BAction()
    {
        $request = $this->getRequest();
        
        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $j = $request->getParam('delta');
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));
        
        if (! $organisation) {
            throw new \RuntimeException('Unable to find organisation.');
        }
        
        $username = $request->getParam('username');
        
        $user = $objectManager->getRepository('Core\Entity\User')->findOneBy(array(
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
        $startImport = microtime(true);
        $totalDL = 0;
        $totalTR = 0;
        echo "Lancement du téléchargement de l'AUP pour " . $organisation->getName()."\n";

        $nmservice = $this->serviceLocator->get('nmb2b');

        try {
            $startSeq = microtime(true);
            echo "Récupération du nombre de séquences\n";
            $eaupchain = new \Core\NMB2B\EAUPChain($nmservice->getEAUPCHain($day));
            $dl = microtime(true) - $startSeq;
            $totalDL += $dl;
            echo "Séquences récupérées en ".$dl." secondes\n";
        } catch(\RuntimeException $e) {
            echo "Erreur fatale pendant le téléchargement"."\n";
            echo "Les données téléchargées sont incomplètes"."\n";
            echo "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré"."\n";
            return;
        }

        $lastSequence = $eaupchain->getLastSequenceNumber();
        $milcats = $objectManager->getRepository('Application\Entity\MilCategory')->findBy(array(
            'nmB2B' => true
        ));

        $designators = array();
        foreach ($milcats as $cat){
            $regex = $cat->getZonesRegex();
            if(strlen($regex) > 2) {
                $start = substr($regex, 1, 2);
                if(!in_array($start, $designators)){
                    $designators[] = $start;
                }
            }
        }

        for ($i = 1; $i <= $lastSequence; $i ++) {
            foreach ($designators as $designator) {
                try {
                    $startSeq = microtime(true);
                    echo "Téléchargement des zones ".$designator.", séquence ".$i."\n";
                    $eauprsas = new \Core\NMB2B\EAUPRSAs($nmservice->getEAUPRSA($designator.'*', $day, $i));
                    $dl = microtime(true) - $startSeq;
                    $totalDL += $dl;
                    echo "Téléchargement terminé en ".$dl." secondes"."\n";
                } catch(\RuntimeException $e) {
                    echo "Erreur fatale pendant le téléchargement"."\n";
                    echo "Les données téléchargées sont incomplètes"."\n";
                    echo "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré"."\n";
                    //abort import
                    //email sent by service
                    return;
                }
                $startEpeires = microtime(true);
                echo "Création des évènements ".$designator.' séquence '.$i." dans Epeires..."."\n";
                foreach ($milcats as $cat) {
                    $objectManager->getRepository('Application\Entity\Event')->addZoneMilEvents($eauprsas, $cat, $organisation, $user);
                }
                $tr = microtime(true) - $startEpeires;
                $totalTR += $tr;
                echo "Evènements créés en ".$tr.' secondes'."\n";
            }
        }
        echo "Fin de l'import de l'AUP en ".(microtime(true)-$startImport).' secondes'."\n";
        echo 'Téléchargement : '.$totalDL.' secondes'."\n";
        echo 'Traitement : '.$totalTR.' secondes';
    }
}