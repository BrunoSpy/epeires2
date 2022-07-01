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

namespace Administration\Controller;

use Application\Command\GenerateReportCommand;
use Application\Command\ImportRegulationsCommand;
use Application\Command\ImportZonesMilCommand;
use Application\Entity\ATFCMCategory;
use Application\Entity\Event;
use Application\Entity\Organisation;
use Core\Service\NMB2BService;
use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * @author Bruno Spyckerelle
 */
class MaintenanceController extends AbstractActionController
{

    private EntityManager $entityManager;
    private bool $nmb2bConfigured = false;
    private bool $mapdConfigured = false;

    public function __construct(EntityManager $entityManager, $config) {
        $this->entityManager = $entityManager;
        if(array_key_exists('nm_b2b', $config)) {
            $this->nmb2bConfigured = true;
        }
        if(array_key_exists('mapd', $config)) {
            $this->mapdConfigured = true;
        }
    }

    public function indexAction()
    {
        $viewModel = new ViewModel();

        $organisations = $this->entityManager->getRepository(Organisation::class)->findAll();

        $viewModel->setVariable('organisations', $organisations);
        $viewModel->setVariable('nmb2b', $this->nmb2bConfigured);
        $viewModel->setVariable('mapd', $this->mapdConfigured);

        return $viewModel;
    }


    private function pipeExec($cmd, $input=''): array
    {
        $proc = proc_open($cmd, array(array('pipe', 'r'),
            array('pipe', 'w'),
            array('pipe', 'w')), $pipes);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $return_code = (int)proc_close($proc);

        return array($return_code, $stdout, $stderr);
    }

    public function importregulationsAction()
    {

        $org = $this->params()->fromQuery('org', null);
        $messages = array();

        if($org == null || strlen($org) == 0) {
            $messages['error'][] = "Aucune organisation transmise.";
        } else {
            $orga = $this->entityManager->getRepository(Organisation::class)->findOneBy(array('shortname'=>$org));
            if($orga == null) {
                $messages['error'][] = "Impossible de trouver l'organisation spécifiée.";
            } else {
                $user =  $this->lmcUserAuthentication()->getIdentity();
                list($returnCode, $stdout, $stderr) =
                    $this->pipeExec("vendor/bin/laminas ".ImportRegulationsCommand::getDefaultName()
                        ." ".$org
                        ." ".$user->getUsername());
                if(strlen(trim($stderr)) > 0) {
                    $messages['error'][] = $stderr;
                }
                foreach (preg_split('/\r\n|\r|\n/', $stdout) as $line) {
                    $messages['success'][] = $line;
                }

            }

        }
        return new JsonModel($messages);
    }

    public function importzonesmapdAction()
    {
        $org = $this->params()->fromQuery('org', null);
        $messages = array();

        if($org == null || strlen($org) == 0) {
            $messages['error'][] = "Aucune organisation transmise.";
        } else {
            $orga = $this->entityManager->getRepository(Organisation::class)->findOneBy(array('shortname'=>$org));
            if($orga == null) {
                $messages['error'][] = "Impossible de trouver l'organisation spécifiée.";
            } else {
                $user =  $this->lmcUserAuthentication()->getIdentity();
                list($returnCode, $stdout, $stderr) =
                    $this->pipeExec("vendor/bin/laminas ".ImportZonesMilCommand::getDefaultName()
                        ." mapd"
                        ." ".$org
                        ." ".$user->getUsername());
                if(strlen(trim($stderr)) > 0) {
                    $messages['error'][] = $stderr;
                }
                foreach (preg_split('/\r\n|\r|\n/', $stdout) as $line) {
                    $messages['success'][] = $line;
                }

            }

        }
        return new JsonModel($messages);
    }

    public function importzonesnmb2bAction()
    {
        $org = $this->params()->fromQuery('org', null);
        $messages = array();

        if($org == null || strlen($org) == 0) {
            $messages['error'][] = "Aucune organisation transmise.";
        } else {
            $orga = $this->entityManager->getRepository(Organisation::class)->findOneBy(array('shortname'=>$org));
            if($orga == null) {
                $messages['error'][] = "Impossible de trouver l'organisation spécifiée.";
            } else {
                $user =  $this->lmcUserAuthentication()->getIdentity();
                list($returnCode, $stdout, $stderr) =
                    $this->pipeExec("vendor/bin/laminas ".ImportZonesMilCommand::getDefaultName()
                        ." nmb2b"
                        ." ".$org
                        ." ".$user->getUsername());
                if(strlen(trim($stderr)) > 0) {
                    $messages['error'][] = $stderr;
                }
                foreach (preg_split('/\r\n|\r|\n/', $stdout) as $line) {
                    $messages['success'][] = $line;
                }

            }

        }
        return new JsonModel($messages);
    }

    public function sendreportAction()
    {
        $org = $this->params()->fromQuery('org', null);
        $messages = array();

        if($org == null || strlen($org) == 0) {
            $messages['error'][] = "Aucune organisation transmise.";
        } else {
            $orga = $this->entityManager->getRepository(Organisation::class)->findOneBy(array('shortname' => $org));
            if ($orga == null) {
                $messages['error'][] = "Impossible de trouver l'organisation spécifiée.";
            } else {
                $this->sendreport($orga, 0, $messages);
            }
        }

        return new JsonModel($messages);
    }

    public function sendreportdeltaAction()
    {
        $org = $this->params()->fromQuery('org', null);
        $messages = array();

        if($org == null || strlen($org) == 0) {
            $messages['error'][] = "Aucune organisation transmise.";
        } else {
            $orga = $this->entityManager->getRepository(Organisation::class)->findOneBy(array('shortname' => $org));
            if ($orga == null) {
                $messages['error'][] = "Impossible de trouver l'organisation spécifiée.";
            } else {
                $this->sendreport($orga, -1, $messages);
            }
        }

        return new JsonModel($messages);
    }

    private function sendreport(Organisation $org, int $delta, &$messages)
    {
        list($returnCode, $stdout, $stderr) =
            $this->pipeExec("vendor/bin/laminas ".GenerateReportCommand::getDefaultName()
                ." ".$org
                ." --email"
                .($delta !== 0 ? $delta : ""));
        if(strlen(trim($stderr)) > 0) {
            $messages['error'][] = $stderr;
        }
        foreach (preg_split('/\r\n|\r|\n/', $stdout) as $line) {
            $messages['success'][] = $line;
        }

    }

}