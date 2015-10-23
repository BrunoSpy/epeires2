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

    public function indexAction()
    {
        $doctrinemigrations = $this->getServiceLocator()->get('doctrine.migrations.configuration');
        
        $doctrinemigrations->setMigrationsTableName($this->getServiceLocator()
            ->get('config')['doctrine']['migrations']['migrations_table']);
        
        $doctrinemigrations->validate();
        
        $executedMigrations = $doctrinemigrations->getMigratedVersions();
        $availableMigrations = $doctrinemigrations->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count($availableMigrations) - count($executedMigrations);
        
        return array(
            'db' => $doctrinemigrations->getConnection()->getDatabase(),
            'version' => $doctrinemigrations->formatVersion($doctrinemigrations->getCurrentVersion()),
            'latestversion' => $doctrinemigrations->formatVersion($doctrinemigrations->getLatestVersion()),
            'table' => $doctrinemigrations->getMigrationsTableName(),
            'migrations' => $newMigrations
        );
    }
}
