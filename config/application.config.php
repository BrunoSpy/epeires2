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

// set APP_ENV = 'development' in httpd.conf or virtualhost conf to set up a dev environment
$env = getenv('APP_ENV') ?  : 'production';

if ($env == 'production') {
    $modules[] = 'ForceHttpsModule';
}

return array(
    'modules' => require __DIR__ . '/modules.config.php',
    
    'module_listener_options' => array(
        'use_laminas_loader' => false,

        'module_paths' => array(
            './module',
            './vendor'
        ),
        'config_glob_paths' => array(
            sprintf('config/autoload/{,*.}{global,%s,local}.php', $env)
        ),
        
        // cache only for production
        'config_cache_enabled' => ($env == 'production'),
        'config_cache_key' => 'app_config',
        'module_map_cache_enabled' => ($env == 'production'),
        'module_map_cache_key' => 'module_map',
        'cache_dir' => 'data/cache/'
    )
);