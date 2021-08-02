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

use Core\Version;
use Laminas\Mvc\Controller\AbstractActionController;
//use Roave\PsrContainerDoctrine\MigrationsConfigurationFactory;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class HomeController extends AbstractActionController
{

    private $doctrinemigrations;
    private $config;

    private $mapd;

    public function __construct( $config, $mapd) {
        $this->config = $config;
        $this->mapd = $mapd;
    }

    public function indexAction() {

        $git = false;

        if(is_dir(ROOT_PATH . '/.git')) {
            $git = array();
            $git['branchname'] = shell_exec("git rev-parse --abbrev-ref HEAD"); // get the one that is always the branch name

            $git['revision'] = shell_exec("git log -n 1 --pretty=format:'%h' --abbrev-commit");

            $git['commit'] = shell_exec("git log -n 1 --pretty=format:'%s (%ci)' --abbrev-commit");

            $tag = shell_exec("git describe --exact-match --tags");

            $hasTag = ! (substr($tag, 0, strlen("fatal")) === "fatal" || substr($tag, 0, strlen("warning")) === "warning" || empty($tag));

            if($hasTag) {
                $git['tag'] = $tag;
            }
        }

    /*
        $this->doctrinemigrations->validate();
        $executedMigrations = $this->doctrinemigrations->getMigratedVersions();
        $availableMigrations = $this->doctrinemigrations->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count($availableMigrations) - count($executedMigrations);
*/
        $extensions = array();
        $extensions['gd'] = extension_loaded('gd');
        $extensions['iconv'] = extension_loaded('iconv');
        $extensions['intl'] = extension_loaded('intl');
        $extensions['soap'] = extension_loaded('soap');
        $extensions['openssl'] = extension_loaded('openssl');
        $extensions['curl'] = extension_loaded('curl');
        $extensions['dom'] = extension_loaded('dom');
        if(PHP_VERSION_ID < 70200) {
            $extensions['mcrypt'] = extension_loaded('mcrypt');
        }

        if(array_key_exists('nm_b2b', $this->config)) {
            $certifValidTo = new \DateTime();
            if(is_readable(ROOT_PATH . $this->config['nm_b2b']['cert_path']) && $fileContent = file_get_contents(ROOT_PATH . $this->config['nm_b2b']['cert_path'])) {
                $certif = openssl_x509_parse($fileContent);
                $certifValidTo->setTimestamp($certif['validTo_time_t']);
                $certifAssignTo = array_key_exists('OU', $certif['subject']) ? $certif['subject']['OU'][0] : '';
                $certifName = $certif['subject']['CN'];
            } else {
                $certifValidTo = false;
                $certifAssignTo = null;
                $certifName = null;
            }
        } else {
            $certifValidTo = null;
            $certifAssignTo = null;
            $certifName = null;
        }

        if(array_key_exists('IHM_OPE_Light', $this->config) && $this->config['IHM_OPE_Light'] == true) {
            $IHMLight = true;
        } else {
            $IHMLight = false;
        }

        if($this->mapd->isEnabled()) {
            $mapdStatus = $this->mapd->getStatus();
        } else {
            $mapdStatus = null;
        }

        return array(
           /* 'db' => $this->doctrinemigrations->getConnection()->getDatabase(),
            'version' => $this->doctrinemigrations->getDateTime($this->doctrinemigrations->getCurrentVersion()),
            'latestversion' => $this->doctrinemigrations->getDateTime($this->doctrinemigrations->getLatestVersion()),
            'table' => $this->doctrinemigrations->getMigrationsTableName(),
            'migrations' => $newMigrations,*/
            'extensions' => $extensions,
            'phpversionid' => PHP_VERSION_ID,
            'certifvalid' => $certifValidTo,
            'certifassign' => $certifAssignTo,
            'certifname' => $certifName,
            'myversion' => Version::VERSION,
            'hostname' => getenv('COMPUTERNAME') ? getenv('COMPUTERNAME') : shell_exec('uname -n'),
            'git' => $git,
            'IHMLight' => $IHMLight,
            'mapd' => $mapdStatus
        );
    }
}
