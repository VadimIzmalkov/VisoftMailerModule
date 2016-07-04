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
    protected $serverUrl = null;

	public function __construct($entityManager, $moduleOptions, $authenticationService, $acMailService)
	{
		$this->entityManager = $entityManager;
		$this->moduleOptions = $moduleOptions;
		$this->authenticationService = $authenticationService;
		$this->acMailService = $acMailService;
	}

	public function send($status, $serverUrl = null)
	{
        $this->serverUrl = $serverUrl;
        // start sending
        // $mailing = $status->getMailing();
        // $mailingListsIds = $campaign->getMailingLists()->map(function($entity) { return $entity->getId(); })->toArray();
        // $contacts = $this->entityManager->getRepository('Application\Entity\Contact')->findByMailingListsIds($mailingListsIds);

        // read contacts from file
        $contactsJsonFilePath = $status->getContactsJsonFilePath();
        $contactsJson = file_get_contents($contactsJsonFilePath);
        $contactsArray = json_decode($contactsJson, true);
        
        // file for contacts that already recieved e-mailes
        $contactsProcessedJsonFilePath = $status->getContactsProcessedJsonFilePath();

        // get already proccessed contacts
        // if sending just started should be 0 (set in status constructor)
        // if sending was interrupt and continue then numProccessed will have some value
        // "TOTAL contacts" set in mailerPugin while status creating
        $numProcessed = $status->getNumContactsProcessed();

        // init new array for proccessed contacts
        $contactsProcessedArray = [];

        // start sending
        foreach ($contactsArray as $contact) {
            if(empty($contactsProcessedArray) && file_exists($contactsProcessedJsonFilePath)) {
                $contactsProcessedJson = file_get_contents($contactsProcessedJsonFilePath);
                $contactsProcessedArray = json_decode($contactsProcessedJson, true);
            }
        	if(in_array($contact['email'], $contactsProcessedArray))
        		continue;

            // TODO: Send email HERE
            $this->sendEmail($status, $contact);

            array_push($contactsProcessedArray, $contact['email']);
            $numProcessed++;
            if(!($numProcessed % 2000)) {
                $status->setNumContactsProcessed($numProcessed);
                $contactsProcessedJson = json_encode($contactsProcessedArray);
                file_put_contents($contactsProcessedJsonFilePath, $contactsProcessedJson);
                $contactsProcessedArray = [];
            }
        	// $recipientState = new Entity\RecipientState();
         //    $recipientState->setEmail($recipient['email']);
         //    $recipientState->setMailing($mailing);
			
			// set Ac Mailer
            // $this->acMailService->setSubject($mailing->getSubject());
            // $this->acMailService->setTemplate($mailing->getEmailTemplatePath(), [
            //     'preview' => false,
            //     'host' => 'http://fryday.net',
            //     'recipientToken' => $recipient['registrationToken'],
            //     'recipientFullName' => $recipient['fullName'],
            //     'mailing' => $mailing,
            // ]);
            // $message = $this->acMailService->getMessage();
            // $message->setTo($recipient['email']);
            // $result = $this->acMailService->send();
            // if (!$result->isValid()) {
            //     if ($result->hasException())
            //         echo sprintf('An error occurred. Exception: \n %s', $result->getException()->getTraceAsString());
            //     else
            //         echo sprintf('An error occurred. Message: %s', $result->getMessage());    
            //     // $emailState->setState(4);       
            // }
        }
        $contactsProcessedJson = json_encode($contactsProcessedArray);
        file_put_contents($contactsProcessedJsonFilePath, $contactsProcessedJson);
        $status->setNumContactsProcessed($numProcessed);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
	}

    // TODO: move this logic to fryday module 
    public function sendEmail($status)
    {

    }

    public function getOptions()
    {
        return $this->moduleOptions;
    }

    public function processStarted($statusId)
    {
        $status = $this->entityManager->find('VisoftMailerModule\Entity\Status', $statusId);
        if(empty($status)) {
            echo "status not exists";
            return false;
        }
        $status->setStartedAt(new \Datetime());
        $status->setState(2);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        return $status;
    }

    public function processCompleted($status)
    {
        $status->setFinishedAt(new \Datetime());
        $status->setState(3);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        return $status;
    }

    protected function getDateTimeWithMicroseconds()
    {
        $time = microtime(true);
        $micro = sprintf("%06d",($time - floor($time)) * 1000000);
        return new \DateTime(date('Y-m-d H:i:s.' . $micro, $time));
    }
}