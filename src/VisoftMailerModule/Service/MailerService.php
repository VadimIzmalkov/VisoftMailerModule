<?php

namespace VisoftMailerModule\Service;

use Doctrine\ORM\EntityManager;

protected $entityManager;

class MailerService 
{
	protected $entityManager;

	public function __construct(
		EntityManager $entityManager,
		ModuleOptions $moduleOptions, 
		$authenticationService
	)
	{
		$this->entityManager = $entityManager;
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
}