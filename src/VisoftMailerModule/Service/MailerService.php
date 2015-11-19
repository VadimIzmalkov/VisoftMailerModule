<?php

namespace VisoftMailerModule\Service;

use Doctrine\ORM\EntityManager;

use VisoftMailerModule\Entity,
	VisoftMailerModule\Options\ModuleOptions;

class MailerService implements MailerServiceInterface
{
	protected $entityManager;
	protected $moduleOptions;
	protected $authenticationService;

	public function __construct(
		EntityManager $entityManager,
		ModuleOptions $moduleOptions, 
		$authenticationService
	)
	{
		$this->entityManager = $entityManager;
		$this->moduleOptions = $moduleOptions;
		$this->authenticationService = $authenticationService;
	}

	public function send($mailingLists, $template)
	{
		$now = new \DateTime();
		$authenticatedUser = $this->authenticationService->getIdentity();
		$status = new Entity\StatusMailer($authenticatedUser, $template['path'], $template['parameters']);
		$this->entityManager->persist($newsletterStatus);
        $this->entityManager->flush();
	}

	public function createMailingList($name)
	{
        $entityInfo = $this->entityManager->getClassMetadata('VisoftMailerModule\Entity\MailingListInterface');
        $entity = new $entityInfo->name;
        $entity->setName($name);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return $entity;
	}

	public function createCampaign($name)
	{
        $entity = new Entity\Campaign();
        $entity->setName($name);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return $entity;
	}
}