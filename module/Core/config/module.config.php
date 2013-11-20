<?php
return array(
		'doctrine' => array(
				'eventmanager' => array(
						'orm_default' => array(
								'subscribers' => array(
									'doctrine.loggable',
								),
						),
				),
				'driver' => array(
						// overriding zfc-user-doctrine-orm's config
						'zfcuser_entity' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'paths' => __DIR__ . '/../src/Core/Entity',
						),
						'RbacUserDoctrineEntity' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'paths' => __DIR__ . '/../src/Core/Entity',
						),
						'orm_default' => array(
								'drivers' => array(
										'Core\Entity' => 'zfcuser_entity',
								),
						),
				),
		),
		'service_manager' => array(
				'factories' => array(
						'doctrine.loggable' => 'Core\Factory\LoggableListenerFactory',
				),
		),
		'rbac-user-doctrine-orm' => array(
				'mapper' => array(
						'role' => array(
								'entityClass' => 'Core\Entity\Role'
						)
				)
		),
		'zfcrbac' => array(
				'firewallRoute' => true,
				'firewallController' => false,
				'anonymousRole' => 'anonymous',
		),
		'zfcuser' => array(
				// telling ZfcUser to use our own class
				'user_entity_class'       => 'Core\Entity\User',
				// telling ZfcUserDoctrineORM to skip the entities it defines
				'enable_default_entities' => false,
				'enable_username' => true,
				'enable_display_name' =>true,
				'enable_registration' => true,
				'login_after_registration' => false,
				'use_redirect_parameter_if_present' => true,
				'auth_identity_fields' => array('username', 'email'),
		),
		'view_manager' => array(
				'display_not_found_reason' => true,
				'display_exceptions'       => true,
				'doctype'                  => 'HTML5',
				'template_path_stack' => array(
						__DIR__ . '/../view',
				),
		),
		'view_helpers' => array(
				'invokables' => array(
						'modalwindow' => 'Core\View\Helper\ModalWindow',
				),
		),
);