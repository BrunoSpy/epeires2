<?php
/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return array(
    'Laminas\Mvc\I18n',
    'Laminas\Mvc\Plugin\FlashMessenger',
    'Laminas\Mvc\Plugin\Prg',
    'Laminas\Mvc\Console',
    'Laminas\Db',
    'Laminas\Log',
    'Laminas\Cache',
    'Laminas\Paginator',
    'Laminas\Form',
    'Laminas\InputFilter',
    'Laminas\Filter',
    'Laminas\Hydrator',
    'Laminas\I18n',
    'Laminas\Router',
    'Laminas\Validator',
    'DoctrineModule',
    'DoctrineORMModule',
    'LmcRbacMvc',
    'LmcUser',
    'LmcUserDoctrineORM',
    'AssetManager',
    'DompdfModule',
    'OpentbsBundle',
    'MaglMarkdown',
    'MattermostMessenger',
    'Core',
    'Application',
    'Administration',
    'IPO',
    'Laminas\\ApiTools',
    'Laminas\\ApiTools\\Provider',
    'Laminas\\ApiTools\\ApiProblem',
    'Laminas\\ApiTools\\MvcAuth',
    'Laminas\\ApiTools\\OAuth2',
    'Laminas\\ApiTools\\Hal',
    'Laminas\\ApiTools\\ContentNegotiation',
    'Laminas\\ApiTools\\ContentValidation',
    'Laminas\\ApiTools\\Rest',
    'Laminas\\ApiTools\\Rpc',
    'Laminas\\ApiTools\\Versioning',
//        'Laminas\\DevelopmentMode',
    'Laminas\\ApiTools\\Documentation',
    'Laminas\\ApiTools\\Documentation\Swagger',
    'Laminas\\ApiTools\\Configuration',
    'API'
);