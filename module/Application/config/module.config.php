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

use Application\Command\Factory\GenerateReportCommandFactory;
use Application\Command\Factory\ImportRegulationsCommandFactory;
use Application\Command\Factory\ImportZonesMilCommandFactory;
use Application\Command\GenerateReportCommand;
use Application\Command\ImportRegulationsCommand;
use Application\Command\ImportZonesMilCommand;

return array(
    'router' => array(
        'routes' => array(
            'root' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'Application\Controller\Events',
                        'action' => 'index'
                    ]
                ]
            ],
            'application' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/app[/:controller[/:action[/:id]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Events',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'laminas-cli' => array(
        'commands' => [
            'epeires2:import-regulations' => ImportRegulationsCommand::class,
            'epeires2:import-zones-mil' => ImportZonesMilCommand::class,
            'epeires2:generate-report' => GenerateReportCommand::class
        ]
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory'
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator'
        ),
        'factories' => array(
            'eventservice' => 'Application\Factories\EventServiceFactory',
            'customfieldservice' => 'Application\Factories\CustomfieldServiceFactory',
            'categoryfactory' => 'Application\Factories\CategoryEntityFactoryFactory',
            ImportRegulationsCommand::class => ImportRegulationsCommandFactory::class,
            ImportZonesMilCommand::class => ImportZonesMilCommandFactory::class,
            GenerateReportCommand::class => GenerateReportCommandFactory::class
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
            'Application\Controller\Events' => 'Application\Controller\Factory\EventsControllerFactory',
            'Application\Controller\Switchlisttab' => 'Application\Controller\Factory\SwitchlistTabControllerFactory',
            'Application\Controller\Frequencies' => 'Application\Controller\Factory\FrequenciesControllerFactory',
            'Application\Controller\Timelinetab' => 'Application\Controller\Factory\TimelineTabControllerFactory',
            'Application\Controller\Alarm' => 'Application\Controller\Factory\AlarmControllerFactory',
            'Application\Controller\Opsups' => 'Application\Controller\Factory\OpSupsControllerFactory',
            'Application\Controller\Mil' => 'Application\Controller\Factory\MilControllerFactory',
            'Application\Controller\Report' => 'Application\Controller\Factory\ReportControllerFactory',
            'Application\Controller\File' => 'Application\Controller\Factory\FileControllerFactory',
            'Application\Controller\Afis' => 'Application\Controller\Factory\AfisControllerFactory',
            'Application\Controller\Flightplans' => 'Application\Controller\Factory\FlightPlansControllerFactory',
            'Application\Controller\Sarbeacons' => 'Application\Controller\Factory\SarBeaconsControllerFactory',
            'Application\Controller\ATFCM' => 'Application\Controller\Factory\ATFCMControllerFactory',
            'Application\Controller\Briefing' => 'Application\Controller\Factory\BriefingControllerFactory',
            'Application\Controller\Sunrisesunset' => 'Application\Controller\Factory\SunrisesunsetControllerFactory',
            'Application\Controller\Splittimelinetab' => 'Application\Controller\Factory\SplitTimelineTabControllerFactory'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'accordionGroup' => 'Application\View\Helper\AccordionGroup',
            'impact' => 'Application\View\Helper\Impact',
            'eventfieldname' => 'Application\View\Helper\EventFieldName',
            'block' => 'Application\View\Helper\Block',
            'sector' => 'Application\View\Helper\Sector',
            'afViewHelper' => 'Application\View\Helper\AfisHelper',
            'flightPlanViewHelper' => 'Application\View\Helper\FlightPlanHelper',
        ),
        'factories' => array(
            'eventName' => 'Application\Factories\EventNameFactory',
            'updateAuthor' => 'Application\Factories\UpdateAuthorFactory',
            'ipo' => 'Application\Factories\IPOFactory',
            'opsup' => 'Application\Factories\OpSupFactory',
            'customfieldvalue' => 'Application\Factories\CustomFieldValueFactory',
            'isMultipleAllowed' => 'Application\Factories\CustomFieldMultipleAllowedFactory'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => false,
        'display_exceptions' => false,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'afis/helper/afadmin' => __DIR__ . '/../view/application/afis/helpers/afis-admin.phtml',
            'afis/helper/afis' => __DIR__ . '/../view/application/afis/helpers/afis.phtml',
            'flight-plans/helpers/flight-plan' => __DIR__ . '/../view/application/flight-plans/helpers/flight-plan.phtml',
            'flight-plans/helpers/alert' => __DIR__ . '/../view/application/flight-plans/helpers/alert.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/application'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    /**
     * Doctrine 2 Configuration
     */
    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'application_entities'
                )
            )
        )
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
        'IHM' => array(
            'ihm.light' => [
                'name' => 'Allégée',
                'description' => 'Activation de l\'IHM allégée'
            ]
        ),
        'Evènements' => array(
            'events.create' => array(
                'name' => 'Création',
                'description' => 'Possibilité de créer de nouveaux évènements'
            ),
            'events.recurrent' => array(
                'name' => 'Evènements récurrents',
                'description' => 'Possibilité de créer des évènements récurrents'
            ),
            'events.write' => array(
                'name' => 'Modification',
                'description' => 'Possibilité de modifier les évènements accessibles en lecture'
            ),
            'events.delete' => array(
                'name' => 'Suppression',
                'description' => 'Possibilité de supprimer des évènements'
            ),
            'events.schedule' => array(
                'name' => 'Programmer',
                'description' => 'Affiche la case &#8243;Evènement programmé&#8243;.'
            ),
            'events.confirme' => array(
                'name' => 'Statut auto',
                'description' => 'Les évènements sont créés avec le statut &#8243;Confirmé&#8243;.'
            ),
            'events.mod-files' => array(
                'name' => 'Ajouter/Modifier fichiers',
                'description' => ''
            ),
            'events.mod-ipo' => array(
                'name' => 'Modifier IPO',
                'description' => ''
            ),
            'events.mod-opsup' => array(
                'name' => 'Modifier Chef Op',
                'description' => ''
            ),
            'events.raz-checklist' => array(
                'name' => 'RAZ Checklist',
                'description' => 'Permet la remise à zéro de la checklist.'
            ),
            'events.read-sounds' => array(
                'name' => 'Lire les fichiers sonores',
                'description' => 'Active la lecture de sons lors d\'évènements comme les mémos.'
            )
        ),
        'Radio' => array(
            'frequencies.read' => array(
                'name' => 'Lecture',
                'description' => 'Donne accès à l\'onglet Radio.'
            )
        ),
        'Afis' => array(
            'afis.read' => array(
                'name' => 'Lecture',
                'description' => 'Donne accès en lecture à l\'onglet Afis'
            ),
            'afis.write' => array(
                'name' => 'Ecriture',
                'description' => 'Permet les ouvertures/fermetures'
            ),
        ),
        'Gestion PLN' => array(
            'flightplans.read' => array(
                'name' => 'Lecture',
                'description' => 'Donne accès en lecture à l\'onglet de gestion des plans de vol'
            ),
            'flightplans.write' => array(
                'name' => 'Ecriture',
                'description' => 'Donne accès en écriture à l\'onglet de gestion des plans de vol'
            ),
        ),
        'SAR Balises' => array(
            'sarbeacons.read' => array(
                'name' => 'Lecture',
                'description' => 'Donne accès à l\'onglet de recherche de terrains'
            ),
            'sarbeacons.write' => array(
                'name' => 'Ecriture',
                'description' => 'Permet d\'effectuer des plans d\'interrogations'
            )
        ),
        'Briefing' => array(
            'briefing.enable' => array(
                'name' => "Actif",
                'description' => "Active le briefing au changement de chef de salle"
            ),
            'briefing.importants' =>array(
                'name' => "Évènements importants",
                'description' => "Active l'affichage des évènements importants en cours"
            ),
            'briefing.regulations' => array(
                'name' => "Régulations",
                'description' => "Active l'affichage des régulations en cours"
            ),
            'briefing.mod' => array(
                'name' => 'Modifier Briefing',
                'description' => 'Autoriser la modification de la zone de texte libre'
            )
        )
    ),
    
    'lmc_rbac' => array(
        'guards' => array(
            'LmcRbacMvc\Guard\ControllerPermissionsGuard' => array(
                array(
                    'controller' => 'Application\Controller\Frequencies',
                    'permissions' => [
                        'frequencies.read'
                    ]
                ),
            )
        )
    ),

    'dompdf_module' => [
        'chroot' => __DIR__ . '/../../..',
        'default_paper_size' => 'a4'
    ]
);
