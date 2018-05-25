<?php

namespace Api;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Api\Controller;

return [
    'router' => [
        'routes' => [
            'grant-user' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/user/grant/:groupId/:userId/:status',
                    'constraints' => [
                        'groupId' => '[0-9]*',
                        'userId' => '[0-9]*',
                        'status' => '[0-1]{1}',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'grant',
                    ],
                ],
            ],
            'get-all-events' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/export/events',
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'export',
                    ],
                ],
            ],
            'get-event' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/event/:id',
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'getInfo',
                    ],
                ],
            ],
            'event-live' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/event/live/:id/:set/:last',
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'live',
                    ],
                ],
            ],
            'group-event' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/group/name/:name',
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'name',
                    ],
                ],
            ],
            'api-save-stats' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/stats/save',
                    'defaults' => [
                        'controller'    => Controller\StatsController::class,
                        'action'        => 'save',
                    ],
                ],
            ],
            'export' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/event/get/all',
                    'defaults' => [
                        'controller'    => Controller\EventController::class,
                        'action'        => 'get',
                    ],
                ],
            ],
            'search-data' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/search/data',
                    'defaults' => [
                        'controller'    => Controller\SearchController::class,
                        'action'        => 'data',
                    ],
                ],
            ],
            'user-display' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/user/display/:display',
                    'constraints' => [
                        'display' => '[1-3]{1}',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'display',
                    ],
                ],
            ],
            'guest' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/guest/response/:eventId/:response',
                    'constraints' => [
                        'response' => '[1-3]{1}',
                        'eventId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\GuestController::class,
                        'action'        => 'response',
                    ],
                ],
            ],
            'notif' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/user/params/:id/:status',
                    'constraints' => [
                        'id' => '[0-9]*',
                        'status' => '[1-2]{1}',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'params',
                    ],
                ],
            ],
            'recurent-enable' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/recurrent/enable/:id/:status',
                    'constraints' => [
                        'id' => '[0-9]*',
                        'status' => '[1-2]{1}',
                    ],
                    'defaults' => [
                        'controller'    => Controller\RecurrentController::class,
                        'action'        => 'enable',
                    ],
                ],
            ],
            'cache-comment' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/comment/cache/:eventId',
                    'constraints' => [
                        'eventId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\CommentController::class,
                        'action'        => 'cache',
                    ],
                ],
            ],
            'comment' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/comment/:eventId/:groupId',
                    'constraints' => [
                        'eventId' => '[0-9]*',
                        'groupId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\CommentController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'share' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/group/share/:groupId',
                    'constraints' => [
                        'eventId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
