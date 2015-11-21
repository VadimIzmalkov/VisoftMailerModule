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
                'send-campaign' => [
                    'options' => [
                        'route' => 'send-campaign <statusid>',
                        'defaults' => [
                            '__NAMESPACE__' => 'VisoftMailerModule\Controller',
                            'controller'    => 'mailer',
                            'action'        => 'send-campaign',
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