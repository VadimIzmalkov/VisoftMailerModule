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
	}

	// public function sendCampaign($campaign)
	// {
	// 	$now = new \DateTime();
	// 	$authenticatedUser = $this->authenticationService->getIdentity();
	// 	$status = new Entity\StatusMailer();
	// 	$status->setCampaign($campaign);
	// 	$this->entityManager->persist($status);
 //        $this->entityManager->flush();
 //        $statusId = $status->getId();
 //        // command to run exporting in separated process
 //        $logWorkerFilePath = $this->moduleOptions->getLogDir() 
 //            . '/worker_send_mails_' . $now->format("Y-m-d_H-i-s") . '.log';
 //        $errWorkerFilePath = $this->moduleOptions->getLogDir() 
 //            . '/worker_send_mails_' . $now->format("Y-m-d_H-i-s") . '.err';
 //        $shell = 'php public/index.php send-campaign ' 
 //            . $statusId 
 //            . ' >' . $logWorkerFilePath 
 //            . ' 2>' . $errWorkerFilePath 
 //            . ' &';
 //        shell_exec($shell);
 //        return $status;
	// }

    // public function sendMailing($mailing)
    // {
    //     $now = new \DateTime();
    //     $authenticatedUser = $this->authenticationService->getIdentity();
    //     $status = new Entity\StatusMailing();
    //     $status->setMailing($mailing);
    //     $status->setCreatedBy($authenticatedUser);
    //     $this->entityManager->persist($status);
    //     $this->entityManager->flush();
    //     $statusId = $status->getId();
    //     // command to run exporting in separated process
    //     $logWorkerFilePath = $this->moduleOptions->getLogDir() 
    //         . '/worker_send_mailing_' . $now->format("Y-m-d_H-i-s") . '.log';
    //     $errWorkerFilePath = $this->moduleOptions->getLogDir() 
    //         . '/worker_send_mailing_' . $now->format("Y-m-d_H-i-s") . '.err';
    //     $shell = 'php public/index.php send-mailing ' 
    //         . $statusId 
    //         . ' >' . $logWorkerFilePath 
    //         . ' 2>' . $errWorkerFilePath 
    //         . ' &';
    //     shell_exec($shell);
    //     return $status;

    // }

	public function send($status)
	{
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
        var_dump($contactsProcessedJsonFilePath );

        $numSent = 0;
        $contactsProcessedArray = [];
        foreach ($contactsArray as $contact) {
            if(empty($contactsProcessedArray) && file_exists($contactsProcessedJsonFilePath)) {
                $contactsProcessedJson = file_get_contents($contactsProcessedJsonFilePath);
                $contactsProcessedArray = json_decode($contactsProcessedJson, true);
            }
        	if(in_array($contact['email'], $contactsProcessedArray))
        		continue;

            // TODO: Sending emails

            array_push($contactsProcessedArray, $contact['email']);
            $numSent++;
            if(!($numSent % 2000)) {
                $contactsProcessedJson = json_decode($contactsProcessedArray);
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
        $status->setNumContactsProcessed($numSent);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
	}

	// public function createMailingList($name)
	// {
 //        $entityInfo = $this->entityManager->getClassMetadata('VisoftMailerModule\Entity\MailingListInterface');
 //        $entity = new $entityInfo->name;
 //        $entity->setName($name);
 //        $this->entityManager->persist($entity);
 //        $this->entityManager->flush();
 //        return $entity;
	// }

	// public function createCampaign($name)
	// {
 //        $entity = new Entity\Campaign();
 //        $entity->setName($name);
 //        $this->entityManager->persist($entity);
 //        $this->entityManager->flush();
 //        return $entity;
	// }

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
        $status->setState(1);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        return $status;
    }

    public function processCompleted($status)
    {
        $status->setFinishedAt(new \Datetime());
        $status->setState(2);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        return $status;
    }

    // protected function checkDir($path)
    // {
    //     if (!is_dir($path)) {
    //         $oldmask = umask(0);
    //         if (!mkdir($path, 0777, true)) {
    //             die('Failed to create folders' . $path );
    //         }
    //         umask($oldmask);
    //     }        
    // }

    protected function getDateTimeWithMicroseconds()
    {
        $time = microtime(true);
        $micro = sprintf("%06d",($time - floor($time)) * 1000000);
        return new \DateTime(date('Y-m-d H:i:s.' . $micro, $time));
    }
}