<?php

namespace Admin;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Admin\Controller;

return [
    'router' => [
        'routes' => [
            'admin-user' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/user[/:action[/:id]]',
                    'constraints' => [
                        'id' => '[0-9]*',
                        'action' => '[a-zA-Z]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'admin-event' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/event[/:action[/:id]]',
                    'constraints' => [
                        'id' => '[0-9]*',
                        'action' => '[a-zA-Z]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'admin-group' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/group[/:action[/:id]]',
                    'constraints' => [
                        'id' => '[0-9]*',
                        'action' => '[a-zA-Z]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'admin-index' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin',
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'admin/layout/layout'           => __DIR__ . '/../view/admin/layout/layout.phtml',
            'admin/index/index'       => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/admin/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/admin/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
