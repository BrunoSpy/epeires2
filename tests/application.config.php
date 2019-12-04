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
    'Zend\Mvc\I18n',
    'ZF\Doctrine\DataFixture',
    'Zend\Mvc\Plugin\FlashMessenger',
    'Zend\Mvc\Plugin\Prg',
    'Zend\Db',
    'Zend\Log',
    'Zend\Session',
    'Zend\Cache',
    'Zend\Paginator',
    'Zend\Form',
    'Zend\InputFilter',
    'Zend\Filter',
    'Zend\Hydrator',
    'Zend\I18n',
    'Zend\Mvc\Console',
    'Zend\Router',
    'Zend\Validator',
    'DoctrineModule',
    'DoctrineORMModule',
    'DoctrineMigrationsModule',
    'ZfcRbac',
    'ZfcUser',
    'ZfcUserDoctrineORM',
    'AssetManager',
    'DompdfModule',
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
