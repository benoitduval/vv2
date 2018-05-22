<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Cache;

return [

    'service_manager' => [
        'factories' => [
            Service\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\MailService::class           => Service\Factory\MailServiceFactory::class,
            Service\Map::class                   => Service\Factory\MapServiceFactory::class,
            Service\OneSignalService::class      => Service\Factory\OneSignalFactory::class,
        ],

        'abstract_factories' => [
            Factory\TableGatewayFactory::class,
            Cache\Service\StorageCacheAbstractServiceFactory::class,
        ],
    ],

    'controllers' => [
        'abstract_factories' => [
            Factory\ControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'welcome' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/welcome',
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'welcome',
                    ],
                ],
            ],
            'event' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/event[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-z][a-z_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'detail',
                    ],
                ],
            ],
            'live-event' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/live/:id/:name',
                    'constraints' => [
                        'id'   => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'live',
                    ],
                ],
            ],
            'disponibility' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/disponibility[/:action[/:id[/:response]]]',
                    'constraints' => [
                        'action'   => '[a-z][a-z_-]*',
                        'id'       => '[0-9]+',
                        'response' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller'    => Controller\DisponibilityController::class,
                        'action'        => 'response',
                    ],
                ],
            ],
            'group-join' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/group/join[/:group]',
                    'constraints' => [
                        'group' => '[a-z0-9\-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'join',
                    ],
                ],
            ],
            'group' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/group[/:action[/:id[/:userId]]]',
                    'constraints' => [
                        'action' => '[a-z][a-z_-]*',
                        'id'     => '[0-9]+',
                        'userId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'create',
                    ],
                ],
            ],
            'training' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/training[/:action[/:id[/:trainingId]]]',
                    'constraints' => [
                        'action' => '[a-z][a-z_-]*',
                        'id'     => '[0-9]+',
                        'trainingId'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller'    => Controller\TrainingController::class,
                        'action'        => 'create',
                    ],
                ],
            ],
            'holiday' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/holiday[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-z][a-z_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller'    => Controller\HolidayController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'group-welcome' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/welcome-to[/:brand]',
                    'constraints' => [
                        'brand' => '[a-z0-9\-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'welcome',
                    ],
                ],
            ],
            'user' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/user[/:action[/:id]]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                        'action' => '[a-z][a-z_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'params',
                    ],
                ],
            ],
            'auth' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/auth[/:action[/:url]]',
                    'constraints' => [
                        'action' => '[a-z][a-z_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\AuthController::class,
                        'action'        => 'signin',
                    ],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'migration' => [
                    'options' => [
                        'route'    => 'migration [--verbose|-v]',
                        'defaults' => [
                            'controller' => Controller\ConsoleController::class,
                            'action'     => 'migration',
                        ],
                    ],
                ],
                'recurent' => [
                    'options' => [
                        'route'    => 'recurent [--verbose|-v]',
                        'defaults' => [
                            'controller' => Controller\ConsoleController::class,
                            'action'     => 'recurent',
                        ],
                    ],
                ],
                'reminder' => [
                    'options' => [
                        'route'    => 'reminder [--verbose|-v]',
                        'defaults' => [
                            'controller' => Controller\ConsoleController::class,
                            'action'     => 'reminder',
                        ],
                    ],
                ],
                'reminder' => [
                    'options' => [
                        'route'    => 'do-not-forget [--verbose|-v]',
                        'defaults' => [
                            'controller' => Controller\ConsoleController::class,
                            'action'     => 'doNotForget',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
