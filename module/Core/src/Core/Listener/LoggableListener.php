<?php
namespace Core\Listener;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Loggable listener
 *
 * @author Boussekeyt Jules <jules.boussekeyt@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class LoggableListener extends \Gedmo\Loggable\LoggableListener implements ServiceManagerAwareInterface
{

    /**
     * Service Manager
     */
    protected $sm;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->sm = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->sm;
    }

    /**
     * Handle any custom LogEntry functionality that needs to be performed
     * before persisting it
     *
     * @param object $logEntry
     *            The LogEntry being persisted
     * @param object $object
     *            The object being Logged
     */
    protected function prePersistLogEntry($logEntry, $object)
    {
        $auth = $this->getServiceManager()->get('zfc_user_auth_service');
        if ($auth->hasIdentity()) {
            $logEntry->setUsername($auth->getIdentity()
                ->getUsername());
        }
    }
}
