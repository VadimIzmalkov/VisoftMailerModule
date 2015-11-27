<?php

namespace VisoftMailerModule\Service;

use Doctrine\ORM\EntityManager;

use VisoftBaseModule\Entity\UserInterface,
    VisoftBaseModule\Service\UserServiceInterface,
    VisoftMailerModule\Options\ModuleOptions,
    VisoftMailerModule\Entity;

class ContactService implements ContactServiceInterface
{
	protected $entityManager;
	protected $moduleOptions;
	protected $authenticationService;
    protected $userService;

	public function __construct(
        EntityManager $entityManager,
        ModuleOptions $moduleOptions, 
        $authenticationService,
        UserServiceInterface $userService
    )
	{
		$this->entityManager = $entityManager;
		$this->moduleOptions = $moduleOptions;
		$this->userService = $userService;
        $this->authenticationService = $authenticationService;
		$this->checkDir($this->moduleOptions->getLogDir());
        $this->checkDir($this->moduleOptions->getContactReportsDir());
        $this->checkDir($this->moduleOptions->getContactExportedCsvDir());
	}

	public function enter($mailingLists, $emailsString)
	{
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        $status = new Entity\StatusContactEnter();
        // $status->setState(0);
        // $status->setCreatedBy($authenticatedUser);
        $status->setEmailsString($emailsString);
        $status->addMailingLists($mailingLists);
        $reportFileName = 'contacts_enter_' . $now->format('d-m-Y_H-i-s') . '.text';
        $reportFilePath = $this->moduleOptions->getContactReportsDir() . '/' . $reportFileName;
        $status->setOutputFilePath($reportFilePath);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        $statusId = $status->getId();
        // command to run exporting in separated process
        $logWorkerFilePath = $this->moduleOptions->getLogDir() 
            . '/worker_contacts_enter_' . $now->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getLogDir() 
            . '/worker_contacts_enter_' . $now->format("Y-m-d_H-i-s") . '.err';
        $shell = 'php public/index.php contacts-enter ' 
            . $statusId 
            . ' >' . $logWorkerFilePath 
            . ' 2>' . $errWorkerFilePath 
            . ' &';
        shell_exec($shell);
        return $status;
	}

	public function persist($statusId)
	{
		// update status - persist started
       	$status = $this->entityManager->getRepository('VisoftMailerModule\Entity\Status')->findOneBy(['id' => $statusId]);
       	if(empty($status)) {
       		echo "status not exists";
       		return false;
       	}
        $status->setStartedAt(new \Datetime());
        $status->setState(1);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

       	// logging
       	$reportFilePath = $status->getOutputFilePath();
       	$message = "====================  PERSIST CONTACTS REPORT  ====================\n";
       	$message .= "Status id: " . $statusId . "\n";
       	$message .= "Date: " . $this->getDateTimeWithMicroseconds()->format('d/m/Y') . "\n";
       	$message .= "------------------------------------------------------------------- \n";
       	$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Connected to worker. \n";
       	file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);

        // logging
       	$message = "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Persisting started. \n";
       	$message .= "------------------------------------------------------------------- \n";
       	file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);

       	// persist
        $emailsString = $status->getEmailsString();
        if(!empty($emailsString)) {
        	$pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
        	preg_match_all($pattern, $emailsString, $emails);
        	$countEmails = count($emails[0]);
        }
        $countContacts = 0;
        $countContactAdded = 0;
        $countContactExist = 0;
        $emailsProcessed = []; // emails that alredy processed for avoiding rapids
        $mailingLists = $status->getMailingLists();
        $contactState = $this->entityManager->find('VisoftMailerModule\Entity\ContactState', 2); // 2 - Not Confirmed
        $subscriberRole = $this->entityManager->find('VisoftBaseModule\Entity\UserRole', $this->userService->getOptions()->getRoleSubscriberId()); // 4 - subscriber
        while(true) {
        	if(!empty($emailsString)) {
				if($countContacts >= $countEmails)  
                    break;
                else
                    $email = $emails[0][$countContacts];
        	}
        	// check if contact already exist
        	$contact = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findOneBy(['email' => $email]);
        	$contactNotExist = empty($contact);
        	$emailProcessed = in_array(strtolower($email), array_map('strtolower', $emailsProcessed)); 
        	if($contactNotExist && !$emailProcessed) {
                $contactEntityInfo = $this->entityManager->getClassMetadata('VisoftMailerModule\Entity\ContactInterface');
                $contact = new $contactEntityInfo->name;
                $contact->setState($contactState);
                $contact->addSubscribedOnMailingLists($mailingLists);
                $contact->setEmail($email);
                if($contact instanceof UserInterface) {
                    $contact->setRole($subscriberRole);
                }
                $this->entityManager->persist($contact);
                $countContactAdded++;
        	} else {
				$message = "Warning: " . $email . " alredy exists and cannot be added \n";
       			file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);
                $countContactExist++;
        	}
        	array_push($emailsProcessed, $email);
        	$countContacts++;
            if (!($countContacts % 2000)) { # do flushing once per 2000 emails
                $status->setNumContacts($countContacts);
                $status->setNumContactsAdded($countContactAdded);
                $status->setNumContactsExist($countContactExist);
                $this->entityManager->persist($status);
                $this->entityManager->flush();
                unset($emailsProcessed);
                $emailsProcessed = [];
            }
        }
        $status->setFinishedAt(new \Datetime());
        $status->setState(2);
        $status->setNumContacts($countContacts);
        $status->setNumContactsAdded($countContactAdded);
        $status->setNumContactsExist($countContactExist);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        // logging
        $message = "------------------------------------------------------------------- \n";
		$message .= "- total contacts: " . $countContacts . " \n";
		$message .= "- contacts added: " . $countContactAdded . " \n";
		$message .= "- contacts exist: " . $countContactExist . " \n";
		$message .= "------------------------------------------------------------------- \n";
		$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Persisting successfully completed. \n";
   		file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);
	}

    public function export(Entity\MailingListInterface $mailingList)
    {
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        $status = new Entity\StatusContactExport($authenticatedUser);
        $status->setState(0);
        $status->setMailingList($mailingList);
        $csvFileName = 'contacts_export_' . $now->format('d-m-Y_H-i-s') . '.csv';
        $csvFilePath = $this->moduleOptions->getContactExportedCsvDir() . '/' . $csvFileName;
        $status->setOutputFilePath($csvFilePath);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        $statusId = $status->getId();
        // command to run exporting in separated process
        $logWorkerFilePath = $this->moduleOptions->getLogDir() 
            . '/worker_contacts_export_' . $now->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getLogDir() 
            . '/worker_contacts_export_' . $now->format("Y-m-d_H-i-s") . '.err';
        $shell = 'php public/index.php contacts-export ' 
            . $statusId 
            . ' >' . $logWorkerFilePath 
            . ' 2>' . $errWorkerFilePath 
            . ' &';
        shell_exec($shell);
        return $status;
    }

    public function dump($status)
    {
        $contactsSubscribed = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findBySibscribedOnMailingLists($status->getMailingList()->getId());
        $csvFilePath = $status->getOutputFilePath();
        $line = "Email, State \n";
        foreach ($contactsSubscribed as $contact) 
            $line .= $contact['email'] . ', '. $contact['stateName'] . "\n";
        file_put_contents($csvFilePath, $line, FILE_APPEND | LOCK_EX);
        $line = null;
        $contactsUnsubscribed = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findByUnibscribedFromMailingLists($status->getMailingList()->getId());
        foreach ($contactsUnsubscribed as $contact) {
            if(isset($contact['stateName']))
                $state = $contact['stateName'];
            else 
                $state = 'Unknown';
            $line .= $contact['email'] . ', ' . $state . "\n";
        }
        file_put_contents($csvFilePath, $line, FILE_APPEND | LOCK_EX);
        unset($line);
    }

    public function emptyMailingList($mailingList)
    {
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        // status init
        $status = new Entity\StatusContactTruncate();
        $status->setMailingList($mailingList);
        $status->setState(0);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        $statusId = $status->getId();
        // command to run exporting in separated process
        $logWorkerFilePath = $this->moduleOptions->getLogDir() 
            . '/worker_contacts_truncate_' . $now->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getLogDir() 
            . '/worker_contacts_truncate_' . $now->format("Y-m-d_H-i-s") . '.err';
        $shell = 'php public/index.php contacts-truncate ' 
            . $statusId 
            . ' >' . $logWorkerFilePath 
            . ' 2>' . $errWorkerFilePath 
            . ' &';
        shell_exec($shell);
        return $status;
    }

    public function truncate($status)
    {
        // update status
        $contacts = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findBySibscribedOnMailingLists($status->getMailingList()->getId());
        foreach ($contacts as $contactArray) {
            $contact = $this->entityManager->find('VisoftMailerModule\Entity\ContactInterface', $contactArray['id']);
            if($contact instanceof UserInterface) {
                if($contact->getRole()->getId() === $this->userService->getOptions()->getRoleSubscriberId())
                    $this->entityManager->remove($contact);
            } else {
                $this->entityManager->remove($contact);
            }   
        }
        $this->entityManager->flush();
    }

    public function search($searchBy)
    {
        return $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->search($searchBy);
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

    public function getOptions()
    {
        return $this->selfModuleOptions;
    }

    protected function checkDir($path)
    {
        if (!is_dir($path)) {
            $oldmask = umask(0);
            if (!mkdir($path, 0777, true)) {
                die('Failed to create folders' . $path );
            }
            umask($oldmask);
        }        
    }

    protected function getDateTimeWithMicroseconds()
    {
    	$time = microtime(true);
		$micro = sprintf("%06d",($time - floor($time)) * 1000000);
		return new \DateTime(date('Y-m-d H:i:s.' . $micro, $time));
    }

    public function downloadExportCsv($mailingListId)
    {
        $mailingList = $this->entityManager->getRepository('VisoftMailerModule\Entity\MailingListInterface')->findOneBy(['id' => $mailingListId]);
        $status = $this->entityManager->getRepository('VisoftMailerModule\Entity\StatusContactExport')->findOneBy(['mailingList' => $mailingList], ['createdAt' => 'DESC']);
        $outputFilePath = $status->getOutputFilePath();
        $fileName = end(explode('/', $outputFilePath));
        if (file_exists($outputFilePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            // header('Content-Length: ' . filesize($filename)); // $file));
            ob_clean();
            flush();
            // readfile($file);
            readfile($file);
        } else {
            echo 'The file $fileName does not exist';
        }
    }
}