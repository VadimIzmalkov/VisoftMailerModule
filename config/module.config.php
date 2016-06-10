<?php

namespace VisoftMailerModule;

return [
    'console' => [
        'router' => [
            'routes' => [
                'contacts-enter' => [
                    'options' => [
                        'route' => 'contacts-enter <statusid>',
                        'defaults' => [
                            '__NAMESPACE__' => 'VisoftMailerModule\Controller',
                            'controller'    => 'contact',
                            'action'        => 'contacts-enter',
                        ],
                    ]
                ],
                'contacts-export' => [
                    'options' => [
                        'route' => 'contacts-export <statusid>',
                        'defaults' => [
                            '__NAMESPACE__' => 'VisoftMailerModule\Controller',
                            'controller'    => 'contact',
                            'action'        => 'contacts-export',
                        ],
                    ]
                ],
                'contacts-truncate' => [
                    'options' => [
                        'route' => 'contacts-truncate <statusid>',
                        'defaults' => [
                            '__NAMESPACE__' => 'VisoftMailerModule\Controller',
                            'controller'    => 'contact',
                            'action'        => 'contacts-truncate',
                        ],
                    ]
                ],
                'send-bulk' => [
                    'options' => [
                        'route' => 'send-bulk <statusid>',
                        'defaults' => [
                            '__NAMESPACE__' => 'VisoftMailerModule\Controller',
                            'controller'    => 'mailer',
                            'action'        => 'send-bulk',
                        ],
                    ]
                ],
                'send-individual' => [
                    'options' => [
                        'route' => 'send-individual <statusid>',
                        'defaults' => [
                            '__NAMESPACE__' => 'VisoftMailerModule\Controller',
                            'controller'    => 'mailer',
                            'action'        => 'send-individual',
                        ],
                    ]
                ],
            ],
        ],
    ],
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/' . __NAMESPACE__ . '/Entity',
                ),
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                )
            )
        ),
    ),
];