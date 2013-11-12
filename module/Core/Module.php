<?php
namespace Core;

class Module
{
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
    
    public function getServiceConfig()
    {
    	return array(
    			'aliases' => array(
    					'service.security' => 'Core\Service\Rbac',
    			),
    			'factories' => array(
    					'ZfcRbac\Collector\RbacCollector' => function($sm) {
    						return new \ZfcRbac\Collector\RbacCollector($sm->get('Core\Service\Rbac'));
    					},
    					'Core\Service\Rbac' => 'Core\Service\RbacFactory'
    							)
    	);
    }
    
    /**
     * @return array|\Zend\ServiceManager\Config
     */
    public function getViewHelperConfig()
    {
    	return array(
    			'factories' => array(
    					'isGranted' => function($sm) {
    						$sl = $sm->getServiceLocator();
    						return new \ZfcRbac\View\Helper\IsGranted($sl->get('Core\Service\Rbac'));
    					},
    					'hasRole' => function($sm) {
    						$sl = $sm->getServiceLocator();
    						return new \ZfcRbac\View\Helper\HasRole($sl->get('Core\Service\Rbac'));
    					},
    			),
    	);
    }
}
