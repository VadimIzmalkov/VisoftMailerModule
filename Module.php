<?php

namespace VisoftMailerModule;

use Zend\Mvc\Controller\ControllerManager,
    Zend\Mvc\Controller\PluginManager;

use VisoftMailerModule\Controller,
    VisoftBaseModule\Service as BaseService,
    VisoftMailerModule\Service as MailerService,
    VisoftMailerModule\Options;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() 
    {
        return [
            'factories' => [
                'VisoftMailerModule\Options\ModuleOptions' => function($serviceLocator) {
                    $config = $serviceLocator->get('Config');
                    return new Options\ModuleOptions(isset($config['visoftmailermodule']) ? $config['visoftmailermodule'] : []);
                },
                'VisoftBaseModule\Service\ProcessingService' => function($serviceLocator) {
                    $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                    return new BaseService\ProcessingService($entityManager);
                },
                'VisoftMailerModule\Service\ContactService' => \Core\Crm\Service\ContactServiceFactory::class,
                // 'VisoftMailerModule\Service\ContactService' => function($serviceLocator) {
                //     $moduleOptions = $serviceLocator->get('VisoftMailerModule\Options\ModuleOptions');
                //     $authenticationService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
                //     $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                //     $userService = $serviceLocator->get('VisoftBaseModule\Service\UserService');
                //     return new MailerService\ContactService($entityManager, $moduleOptions, $authenticationService, $userService);
                // },
                'VisoftMailerModule\Service\MailerService' => function($serviceLocator) {
                    $moduleOptions = $serviceLocator->get('VisoftMailerModule\Options\ModuleOptions');
                    $authenticationService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
                    $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                    $acMailService = $serviceLocator->get('AcMailer\Service\MailService');
                    return new MailerService\MailerService($entityManager, $moduleOptions, $authenticationService, $acMailService);
                },
            ],
        ];
    }

    public function getControllerConfig() 
    {
        return array(
            'factories' => array(
                'VisoftMailerModule\Controller\Contact' => function(ControllerManager $controllerManager) {
                    $serviceLocator = $controllerManager->getServiceLocator();
                    $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                    $moduleOptions = $serviceLocator->get('VisoftMailerModule\Options\ModuleOptions');
                    $contactService = $serviceLocator->get('VisoftMailerModule\Service\ContactService');
                    $processingService = $serviceLocator->get('VisoftBaseModule\Service\ProcessingService');
                    return new Controller\ContactController($entityManager, $contactService, $moduleOptions, $processingService);
                },
                'VisoftMailerModule\Controller\Mailer' => function(ControllerManager $controllerManager) {
                    $serviceLocator = $controllerManager->getServiceLocator();
                    $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                    $moduleOptions = $serviceLocator->get('VisoftMailerModule\Options\ModuleOptions');
                    $processingService = $serviceLocator->get('VisoftBaseModule\Service\ProcessingService');
                    $mailerService = $serviceLocator->get('VisoftMailerModule\Service\MailerService');
                    return new Controller\MailerController($entityManager, $mailerService, $moduleOptions, $processingService);
                },
            ),
        );
    }

    public function getControllerPluginConfig()
    {
        return array(
            'factories' => [
                'mailerPlugin' => function(PluginManager $pluginManager) {
                    $serviceLocator = $pluginManager->getServiceLocator();
                    $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                    $authenticationService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
                    $moduleOptions = $serviceLocator->get('VisoftMailerModule\Options\ModuleOptions');
                    $contactService = $serviceLocator->get('VisoftMailerModule\Service\ContactService');
                    return new Controller\Plugin\MailerPlugin($entityManager, $authenticationService, $moduleOptions, $contactService);
                }
            ],
            // 'invokables' => [
            //     'detectCsvFileDelimiter' => 'VisoftMailerModule\Controller\Plugin\DetectCsvFileDelimiter',
            // ],
        );
    }
}