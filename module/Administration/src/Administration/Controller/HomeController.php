<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class HomeController extends AbstractActionController
{
    public function indexAction()
    {
    	$doctrinemigrations = $this->getServiceLocator()->get('doctrine.migrations.configuration');
             
        $doctrinemigrations->setMigrationsTableName($this->getServiceLocator()->get('config')['doctrine']['migrations']['migrations_table']);
        
        $doctrinemigrations->validate();
        
        $executedMigrations = $doctrinemigrations->getMigratedVersions();
        $availableMigrations = $doctrinemigrations->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count($availableMigrations) - count($executedMigrations);
        
        return array('db'=> $doctrinemigrations->getConnection()->getDatabase(),
                     'version' => $doctrinemigrations->formatVersion($doctrinemigrations->getCurrentVersion()),
                     'latestversion' => $doctrinemigrations->formatVersion($doctrinemigrations->getLatestVersion()),
                     'table' => $doctrinemigrations->getMigrationsTableName(),
                     'migrations' => $newMigrations);
    }
    
    
}
