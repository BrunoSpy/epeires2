<?php
//set APP_ENV = 'development' in httpd.conf or virtualhost conf to set up a dev environment
$env = getenv('APP_ENV') ?: 'production';

// Production modules
$modules = array(
		'DoctrineModule',
        'DoctrineORMModule',
        'ZfcBase',
		'ZfcRbac',
		'ZfcUser',
        'ZfcUserDoctrineORM',
        'AssetManager',
		'Core',
        'Application',
        'Administration',
        'IPO',
);

if($env == 'development') {
	$modules[] = 'ZendDeveloperTools';
}

return array(
    'modules' => $modules,
		
    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor'
            ),
        'config_glob_paths' => array(
        	sprintf('config/autoload/{,*.}{global,%s,local}.php', $env)
        ),

    	//cache only for production
  		'config_cache_enabled' => ($env == 'production'),
    	'config_cache_key' => 'app_config',
    	'module_map_cache_enabled' => ($env == 'production'),
    	'module_map_cache_key' => 'module_map',
    	'cache_dir' => 'data/cache/',
    		
        )
    );
