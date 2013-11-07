<?php
return array(
		'doctrine' => array(
				'driver' => array(
						// overriding zfc-user-doctrine-orm's config
						'zfcuser_entity' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'paths' => __DIR__ . '/../src/core/Entity',
						),
		
						'orm_default' => array(
								'drivers' => array(
										'Core\Entity' => 'zfcuser_entity',
								),
						),
				),
		),
		
		'zfcuser' => array(
				// telling ZfcUser to use our own class
				'user_entity_class'       => 'Core\Entity\User',
				// telling ZfcUserDoctrineORM to skip the entities it defines
				'enable_default_entities' => false,
				'enable_username' => true,
				'enable_display_name' =>true,
				'enable_registration' => true,
				'auth_identity_fields' => array('username', 'email'),
		),
		
		'bjyauthorize' => array(
				// Using the authentication identity provider, which basically reads the roles from the auth service's identity
				'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
		
				'role_providers'        => array(
						// using an object repository (entity repository) to load all roles into our ACL
						'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
								'object_manager'    => 'doctrine.entity_manager.orm_default',
								'role_entity_class' => 'Core\Entity\Role',
						),
				),
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