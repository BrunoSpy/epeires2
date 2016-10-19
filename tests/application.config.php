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


// Production modules
$modules = array(
    'DoctrineModule',
    'DoctrineORMModule',
    'DoctrineMigrationsModule',
    'DoctrineDataFixtureModule',
    'ZfcBase',
    'ZfcRbac',
    'ZfcUser',
    'ZfcUserDoctrineORM',
    'AssetManager',
    'DOMPDFModule',
    'OpentbsBundle',
    'Core',
    'Application',
    'Administration',
    'IPO'
);


return array(
    'modules' => $modules,

    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor'
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php'
        ),

    )
);
