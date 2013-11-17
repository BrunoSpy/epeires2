<?php
namespace Core;

use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
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
    					'Core\Service\Rbac' => 'Core\Service\RbacFactory',
    					'zfcuser_register_form' => function($sm) {
    						$options = $sm->get('zfcuser_module_options');
    						$form = new Form\Register(null, $options, $sm->get('Doctrine\ORM\EntityManager'));
    						//$form->setCaptchaElement($sm->get('zfcuser_captcha_element'));
    						$form->setInputFilter(new \ZfcUser\Form\RegisterFilter(
    								new \ZfcUser\Validator\NoRecordExists(array(
    										'mapper' => $sm->get('zfcuser_user_mapper'),
    										'key'    => 'email'
    								)),
    								new \ZfcUser\Validator\NoRecordExists(array(
    										'mapper' => $sm->get('zfcuser_user_mapper'),
    										'key'    => 'username'
    								)),
    								$options
    						));
    						return $form;
    					}
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
    					'zfcUserRegisterWidget' => function ($sm) {
    						$locator = $sm->getServiceLocator();
    						$viewHelper = new View\Helper\ZfcUserRegisterWidget;
    						$viewHelper->setViewTemplate('zfc-user/user/register.phtml');
    						$viewHelper->setRegisterForm($locator->get('zfcuser_register_form'));
    						return $viewHelper;
    					},
    			),
    	);
    }
}
