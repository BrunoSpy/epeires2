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

use Zend\Mvc\Controller\AbstractActionController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class HomeController extends AbstractActionController
{

    private $doctrinemigrations;
    private $config;

    public function __construct($doctrinemigrations, $config) {
        $this->doctrinemigrations = $doctrinemigrations;
        $this->config = $config;
    }

    public function indexAction() {
        $this->doctrinemigrations->setMigrationsTableName($this->config['doctrine']['migrations']['migrations_table']);
        $this->doctrinemigrations->validate();
        $executedMigrations = $this->doctrinemigrations->getMigratedVersions();
        $availableMigrations = $this->doctrinemigrations->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count($availableMigrations) - count($executedMigrations);

        $extensions = array();
        $extensions['gd'] = extension_loaded('gd');
        $extensions['iconv'] = extension_loaded('iconv');
        $extensions['intl'] = extension_loaded('intl');
        $extensions['soap'] = extension_loaded('soap');
        $extensions['openssl'] = extension_loaded('openssl');

        return array(
            'db' => $this->doctrinemigrations->getConnection()->getDatabase(),
            'version' => $this->doctrinemigrations->formatVersion($this->doctrinemigrations->getCurrentVersion()),
            'latestversion' => $this->doctrinemigrations->formatVersion($this->doctrinemigrations->getLatestVersion()),
            'table' => $this->doctrinemigrations->getMigrationsTableName(),
            'migrations' => $newMigrations,
            'extensions' => $extensions
        );
    }
}
