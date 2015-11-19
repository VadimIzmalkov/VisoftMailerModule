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
            ],
        ],
    ],
    // 'router' => [
    //     'routes' => [
    //         'visoft-mailer' => [
    //             'type' => 'Segment',
    //             'options' => [
    //                 'route' => '/visoft-mailer/:controller/:action[/:entityId[/]]',
    //                 'defaults' => [
    //                     '__NAMESPACE__' => 'VisoftMailerModule\Controller',
    //                 ],
    //             ],
    //         ],
    //     ],
    // ],
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
        // 'entity_resolver' => array(
        //     'orm_default' => array(
        //         'resolvers' => array(
        //             'VisoftBaseModule\Entity\UserInterface' => 'Application\Entity\User',
        //             'VisoftMailerModule\Entity\MailingListInterface' => 'Application\Entity\MailingList',
        //             // 'VisoftMailerModule\Entity\ContactInterface' => 'Application\Entity\Contact',
        //         ),
        //     ),
        // ),
    ),
];