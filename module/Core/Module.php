<?php
/**
 * Epeires 2
 *
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Core;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\EventManager\EventInterface;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface
{
	
	public function onBootstrap(EventInterface $e)
	{
		$t = $e->getTarget();
	
		$t->getEventManager()->attach(
				$t->getServiceManager()->get('ZfcRbac\View\Strategy\UnauthorizedStrategy')
		);

	}
	
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

}
