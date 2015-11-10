<?php

namespace VisoftMailerModule\Service;

use Doctrine\ORM\EntityManager;

use VisoftMailerModule\Entity,
	VisoftMailerModule\Options\ModuleOptions;

class ContactService implements ContactServiceInterface
{
	protected $entityManager;
	protected $moduleOptions;
	protected $authenticationService;

	public function __construct(EntityManager $entityManager, ModuleOptions $moduleOptions, $authenticationService)
	{
		$this->entityManager = $entityManager;
		$this->moduleOptions = $moduleOptions;
		$this->authenticationService = $authenticationService;
		$this->checkDir($this->moduleOptions->getContactEnterLogDir());
        $this->checkDir($this->moduleOptions->getContactExportLogDir());
	}

	public function enter($emails)
	{
		// create status entity
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        $status = new Entity\StatusContactEnter($authenticatedUser, $emails);
        $status->setState(0);
        $status->setLogFilePath($this->moduleOptions->getContactEnterLogDir() . 'import-contacts_' . $now->format('d-m-Y_H-i-s') . '.log');
        $this->entityManager->persist($status);
        $this->entityManager->flush();
    	$statusId = $status->getId();
    	$logWorkerFilePath = $this->moduleOptions->getContactEnterLogDir() 
            . 'worker_' . $now->format("Y-m-d_H-i-s") . '.log';
    	$errWorkerFilePath = $this->moduleOptions->getContactEnterLogDir() 
            . 'worker_' . $now->format("Y-m-d_H-i-s") . '.err';
        $shell = 'php public/index.php contact-persist '. $statusId 
            . ' >' . $logWorkerFilePath . ' 2>' . $errWorkerFilePath . ' &';
        shell_exec($shell);
	}

	private function persist($statusId)
	{
		// update status - persist started
       	$status = $this->entityManager->getRepository('VisoftMailerModule\Entity\Status')->findOneBy(['id' => $statusId]);
       	if(empty($status)) {
       		echo "status not exists";
       		return true;
       	}
       	
       	// logging
       	$logFile = $status->getLogFilePath();
       	$message = "====================  PERSIST CONTACTS REPORT  ====================\n";
       	$message .= "Status id: " . $statusId . "\n";
       	$message .= "Date: " . $this->getDateTimeWithMicroseconds()->format('d/m/Y') . "\n";
       	$message .= "------------------------------------------------------------------- \n";
       	$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Connected to worker. \n";
       	file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
       	
       	// update state status
        $status->setStartedAt(new \Datetime());
        $status->setState(1);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        // logging
       	$message = "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Persisting started. \n";
       	$message .= "------------------------------------------------------------------- \n";
       	file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);

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
        while(true) {
        	if(!empty($emailsString)) {
				if($countContacts >= $countEmails) {
                    break;
                } else {
                    $email = $emails[0][$countContacts];
                    var_dump($email);
                }
        	}
        	// check if contact already exist
        	// TODO User Repository move to module config
        	$contact = $this->entityManager->getRepository('Application\Entity\Lead')->findOneBy(['email' => $email]);
        	$contactNotExist = empty($contact);
        	$emailProcessed = in_array(strtolower($email), array_map('strtolower', $emailsProcessed)); 
        	if($contactNotExist && !$emailProcessed) {
        		$countContactAdded++;
                $this->persistContact($email);
        	} else {
        		$countContactExist++;
				$message = "Warning: " . $email . " alredy exists and cannot be added \n";
       			file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
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
   		file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
	}

    protected function persistContact($email)
    {
        $contact = new \Application\Entity\Lead();
        $contact->setEmail($email);
        $contact->setState(6);
        $this->entityManager->persist($contact);
    }

    public function export(Entity\ContactListInterface $contactList)
    {
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        $status = new Entity\StatusContactExport($authenticatedUser);
        $status->setState(0);
        $status->setContactList($contactList);
        $status->setLogFilePath($this->moduleOptions->getContactExportLogDir() . 'export-contacts_' . $now->format('d-m-Y_H-i-s') . '.log');
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        $statusId = $status->getId();
        $nowTime = new \DateTime();
        $logWorkerFilePath = $this->moduleOptions->getContactExportLogDir() . 'worker_' . $nowTime->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getContactExportLogDir() . 'worker_' . $nowTime->format("Y-m-d_H-i-s") . '.err';
        $shell = 'php public/index.php contact-extract '. $statusId . ' >' . $logWorkerFilePath . ' 2>' . $errWorkerFilePath . ' &';
        // shell_exec($shell);
        return $status->getId();
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
}