<?php
/**
 * Epeires 2
 * Admin module configuration
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
return array(
    'router' => array(
        'routes' => array(
            'administration' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin[/:controller[/:action]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Administration\Controller',
                        'controller' => 'Home',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'delete-events' => array(
                    'options' => array(
                        'route' => 'delete-events <orgshortname>',
                        'defaults' => array(
                            'controller' => 'Administration\Controller\Maintenance',
                            'action' => 'deleteEvents'
                        )
                    )
                ),
                'clean-logs' => array(
                    'options' => array(
                        'route' => 'clean-logs',
                        'defaults' => array(
                            'controller' => 'Administration\Controller\Maintenance',
                            'action' => 'cleanLogs'
                        )
                    )
                ),
                'initDB' => array(
                    'options' => array(
                        'route' => 'initDB',
                        'defaults' => array(
                            'controller' => 'Administration\Controller\Maintenance',
                            'action' => 'initDB'
                        )
                    )
                ),
                'initbtivDB' => array(
                    'options' => array(
                        'route' => 'initbtivDB',
                        'defaults' => array(
                            'controller' => 'Administration\Controller\Maintenance',
                            'action' => 'initbtivDB'
                        )
                    )
                ),
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory'
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator'
        )
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    'controllers' => array(
        'factories' => array(
            'Administration\Controller\Home' => 'Administration\Controller\Factory\HomeControllerFactory',
            'Administration\Controller\Config' => 'Administration\Controller\Factory\ConfigControllerFactory',
            'Administration\Controller\Centre' => 'Administration\Controller\Factory\CentreControllerFactory',
            'Administration\Controller\Radio' => 'Administration\Controller\Factory\RadioControllerFactory',
            'Administration\Controller\Switchobjects' => 'Administration\Controller\Factory\SwitchObjectsControllerFactory',
            'Administration\Controller\Users' => 'Administration\Controller\Factory\UsersControllerFactory',
            'Administration\Controller\Roles' => 'Administration\Controller\Factory\RolesControllerFactory',
            'Administration\Controller\Ipos' => 'Administration\Controller\Factory\IPOSControllerFactory',
            'Administration\Controller\Opsups' => 'Administration\Controller\Factory\OpSupsControllerFactory',
            'Administration\Controller\Categories' => 'Administration\Controller\Factory\CategoriesControllerFactory',
            'Administration\Controller\Models' => 'Administration\Controller\Factory\ModelsControllerFactory',
            'Administration\Controller\Tabs' => 'Administration\Controller\Factory\TabsControllerFactory',
            'Administration\Controller\Mil' => 'Administration\Controller\Factory\MilControllerFactory',
            'Administration\Controller\Fields' => 'Administration\Controller\Factory\FieldsControllerFactory',
            'Administration\Controller\Maintenance' => 'Administration\Controller\Factory\MaintenanceControllerFactory',
            'Administration\Controller\Afis' => 'Administration\Controller\Factory\AfisControllerFactory',
            'Administration\Controller\Atfcm' => 'Administration\Controller\Factory\ATFCMControllerFactory'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => false,
        'display_exceptions' => false,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'admin/layout' => __DIR__ . '/../view/layout/adminlayout.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/administration'
        )
    ),

    'view_helpers' => array(
        'invokables' => array(
            'afViewHelper' => 'Application\View\Helper\AfisHelper',
        ),
    ),  
    /**
     * Automatically use module assets
     */
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public'
            )
        )
    ),
    'permissions' => array(
        'Administration' => array(
            'admin.access' => array(
                'name' => 'Accès',
                'description' => 'Donne accès à la page d\'administration. '
            ),
            'admin.centre' => array(
                'name' => 'Centre',
                'description' => ''
            ),
            'admin.users' => array(
                'name' => 'Utilisateurs',
                'description' => ''
            ),
            'admin.categories' => array(
                'name' => 'Catégories',
                'description' => ''
            ),
            'admin.models' => array(
                'name' => 'Modèles',
                'description' => ''
            ),
            'admin.radio' => array(
                'name' => 'Radio',
                'description' => ''
            ),
            'admin.zonesmil' => array(
                'name' => 'Zones militaires',
                'description' => ''
            ),
            'admin.tabs' => array(
                'name' => 'Onglets',
                'description' => ''
            ),
            'admin.switchobjects' => array(
                'name' => 'Objets commutables',
                'description' => ''
            ),
            'admin.atfcm' => array(
                'name' => 'ATFCM',
                'description' => ''
            )
        ),
        'Messagerie instantanée' => array(
            'chat.access' => array(
                'name' => 'Actif',
                'description' => 'Activer le module de messagerie instantanée'
            ),
            'chat.write' => array(
                'name' => 'Envoi',
                'description' => 'Autoriser l\'envoi de messages.'
            )
        )
    ),
    'lmc_rbac' => array(
        'guards' => array(
            'LmcRbacMvc\Guard\RoutePermissionsGuard' => array(
                'administration' => array(
                    'admin.access'
                )
            ),
            'LmcRbacMvc\Guard\ControllerPermissionsGuard' => array(
                array(
                    'controller' => 'Administration\Controller\Categories',
                    'permissions' => [
                        'admin.categories'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\Models',
                    'permissions' => [
                        'admin.models'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\Users',
                    'permissions' => [
                        'admin.users'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\Roles',
                    'permissions' => [
                        'admin.users'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\IPOS',
                    'permissions' => [
                        'admin.users'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\OpSups',
                    'permissions' => [
                        'admin.users'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\Radio',
                    'permissions' => [
                        'admin.radio'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\Mil',
                    'permissions' => [
                        'admin.zonesmil'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\Tabs',
                    'permissions' => [
                        'admin.tabs'
                    ]
                ),
                array(
                    'controller' => 'Administration\Controller\ATFCM',
                    'permissions' => [
                        'admin.atfcm'
                    ]
                )
            )
        )
    )
);
