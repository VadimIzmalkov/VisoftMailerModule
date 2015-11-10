<?php

namespace VisoftMailerModule;

use Zend\Mvc\Controller\ControllerManager;

use VisoftMailerModule\Controller,
    VisoftMailerModule\Service,
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
                'VisoftMailerModule\Service\ContactService' => function($serviceLocator) {
                    $moduleOptions = $serviceLocator->get('VisoftMailerModule\Options\ModuleOptions');
                    $authenticationService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
                    $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
                    return new Service\ContactService($entityManager, $moduleOptions, $authenticationService);
                },
            ],
        ];
    }

    public function getControllerConfig() 
    {
        return array(
            'factories' => array(
                'VisoftMailerModule\Controller\Contact' => function(ControllerManager $controllerManager) {
                    $serviceManager = $controllerManager->getServiceLocator();
                    $moduleOptions = $serviceManager->get('VisoftMailerModule\Options\ModuleOptions');
                    $contactService = $serviceManager->get('VisoftMailerModule\Service\ContactService');
                    return new Controller\ContactController($contactService, $moduleOptions);
                },
            ),
        );
    }
}