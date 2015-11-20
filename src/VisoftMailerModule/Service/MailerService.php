<?php

namespace VisoftMailerModule\Service;

use Doctrine\ORM\EntityManager;

use AcMailer\Service\MailService as AcMailService;

use VisoftMailerModule\Entity,
	VisoftMailerModule\Options\ModuleOptions;

class MailerService implements MailerServiceInterface
{
	protected $entityManager;
	protected $moduleOptions;
	protected $authenticationService;
	protected $acMailService;

	public function __construct(
		EntityManager $entityManager,
		ModuleOptions $moduleOptions, 
		$authenticationService, 
		AcMailService $acMailService
	)
	{
		$this->entityManager = $entityManager;
		$this->moduleOptions = $moduleOptions;
		$this->authenticationService = $authenticationService;
		$this->acMailService = $acMailService;
		$this->checkDir($this->moduleOptions->getMailerDir());
        $this->checkDir($this->moduleOptions->getMailerLogDir());
	}

	public function send($campaign)
	{
		$now = new \DateTime();
		$authenticatedUser = $this->authenticationService->getIdentity();
		$status = new Entity\StatusMailer($authenticatedUser, $template['path'], $template['parameters']);
		$status->setcampaign($campaign);
		$this->entityManager->persist($status);
        $this->entityManager->flush();
        $statusId = $status->getId();
        // command to run exporting in separated process
        $logWorkerFilePath = $this->moduleOptions->getMailerLogDir() 
            . '/worker_send_mails_' . $now->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getMailerLogDir() 
            . '/worker_send_mails_' . $now->format("Y-m-d_H-i-s") . '.err';
        $shell = 'php public/index.php send-mails ' 
            . $statusId 
            . ' >' . $logWorkerFilePath 
            . ' 2>' . $errWorkerFilePath 
            . ' &';
        shell_exec($shell);
        return $status;
	}

	public function performSending($statusId)
	{
		// check if status exist
       	$status = $this->entityManager->find('VisoftMailerModule\Entity\Status', $statusId);
       	if(empty($status)) {
       		echo "status not exists";
       		return false;
       	}

       	// update state status
        $status->setStartedAt(new \Datetime());
        $status->setState(2);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        // start sending
        $mailingListsIds = $campaign->getMailingLists()->map(function($entity) { return $entity->getId(); })->toArray();
        $contacts = $this->entityManager->getRepository('VisoftMailerModule\Entity\Contact')->findByMailingListIds($mailingListsIds);
        $numSent = 0;
        foreach ($contacts as $contact) {
        	if(!empty($recipientState = $this->entityManager->getRepository('VisoftMailerModule\Entity\RecipientState')->findOneBy(['email' => $contact['email'], 'campaign' => $campaign])))
        		continue;
        	$recipientState = new Entity\RecipientState();
            $recipientState->setEmail($contact['email']);
            $recipientState->setCampaign($campaign);
			
			// set Ac Mailer
			$this->acMailService->setBody($campaign->getSubject());
            $this->acMailService->setSubject($campaign->getEmailTemplate()->getBodyText());
            $message = $this->acMailService->getMessage();
            $message->setTo($contact['email']);
            $result = $this->acMailService->send();
            if (!$result->isValid()) {
                if ($result->hasException())
                    echo sprintf('An error occurred. Exception: \n %s', $result->getException()->getTraceAsString());
                else
                    echo sprintf('An error occurred. Message: %s', $result->getMessage());    
                $emailState->setState(4);       
            }
        }
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