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
        $this->checkDir($this->moduleOptions->getContactEnterJsonDir());
	}

	public function enter($database, array $contactsArray)
	{
        // convert array to json 
        $contactsTotal = count($contactsArray);
        $contactsJson = json_encode($contactsArray, JSON_UNESCAPED_UNICODE);
        $contactsJsonFilePath = $this->moduleOptions->getContactEnterJsonDir() . '/' . md5(uniqid(mt_rand(), true)) . '.json';
        
        // saving json to file
        file_put_contents($contactsJsonFilePath, $contactsJson);
        
        // create and set status entity
        $identity = $this->authenticationService->getIdentity();
        $status = new Entity\StatusContactEnter();
        if(!empty($identity))
            $status->setCreatedBy($identity);
        $status->setNumTotalContacts($contactsTotal);
        $status->setContactsJsonFilePath($contactsJsonFilePath);
        // $status->addMailingLists($database);
        $status->setDatabase($database);
        $now = new \DateTime();
        $reportFileName = 'contacts_enter_' . $now->format('d-m-Y_H-i-s') . '.txt';
        $reportFilePath = $this->moduleOptions->getContactReportsDir() . '/' . $reportFileName;
        $status->setOutputFilePath($reportFilePath);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        // log and error files
        $logWorkerFilePath = $this->moduleOptions->getLogDir() . '/worker_contacts_enter_' . $now->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getLogDir() . '/worker_contacts_enter_' . $now->format("Y-m-d_H-i-s") . '.err';
        // command to run exporting in separated process
        $shell = 'php public/index.php contacts-enter ' . $status->getId() . ' >' . $logWorkerFilePath . ' 2>' . $errWorkerFilePath . ' &';
        shell_exec($shell);

        return $status;
	}

	public function persist($status)
	{
       	// logging
       	$reportFilePath = $status->getOutputFilePath();
       	$message = "====================  PERSIST CONTACTS REPORT  ====================\n";
       	$message .= "Status id: " . $status->getId() . "\n";
       	$message .= "Date: " . $this->getDateTimeWithMicroseconds()->format('d/m/Y') . "\n";
       	$message .= "------------------------------------------------------------------- \n";
       	$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Connected to worker. \n";
       	$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Persisting started. \n";
       	$message .= "------------------------------------------------------------------- \n";
       	file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);

       	// JSON decode
        $contactsJsonFilePath = $status->getContactsJsonFilePath();
        $contactsJson = file_get_contents($contactsJsonFilePath);
        $contactsArray = json_decode($contactsJson, true);
        // set unique field - email, phone, name etc.
        $uniqueField = $this->moduleOptions->getUniqueField();
    
        // counters
        $countContactProcessed = 0;
        $countContactAdded = 0;
        $countContactExist = 0;
        
        // emails that alredy processed for avoiding rapids
        $emailsProcessed = []; 

        // $mailingLists = $status->getMailingLists();
        $database = $status->getDatabase();

        // TODO: move below to inherit class
        // this data should be dependence
        // $contactState = $this->entityManager->find('VisoftMailerModule\Entity\ContactState', $this->moduleOptions->getRecentlyAddedStateId()); // 2 - Not Confirmed

        // start to process every contact
        foreach ($contactsArray as $contactInfo) {
            // previously contact not exist
            $contactNotExist = true;
            
            // check if contact already exist by unique data
            // unique data can be
            // - e-mail
            // - phone
            // unique data sets in visoftmailermodule.global.php
            // peristing allowed only if same field exists in contact's info - exmp.: $contactsInfo['email']
            if(isset($contactInfo[$uniqueField])) {
                $contact = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findOneBy([$uniqueField => $contactInfo[$uniqueField]]);
                
                // check if contact already in list for persisting
                // convert email to lowercase
                $emailsProcessedLower = array_map('strtolower', $emailsProcessed);
                $emailContactLower = strtolower($contactInfo[$uniqueField]);

                $emailProcessedFlag = in_array($emailContactLower, $emailsProcessedLower); 
                
                // update flag
                $contactNotExist = empty($contact) && !$emailProcessedFlag;
                
                // contact already in database
                if(!$contactNotExist) {
                    $message = "Warning: " . $contactInfo[$uniqueField] . " alredy exists and cannot be added \n";
                    array_push($emailsProcessed, $contactInfo[$uniqueField]);
                    file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);
                    $countContactExist++;
                }
            } 
            
            // create new contact if one has not been find before
            if($contactNotExist) {
                $contactEntityInfo = $this->entityManager->getClassMetadata('VisoftMailerModule\Entity\ContactInterface');
                $contact = new $contactEntityInfo->name;

                $this->setContact($contact, $database, $contactInfo);
                // $contact->setState($contactState);
                // $contact->addSubscribedOnMailingLists($mailingLists);
                
                // // set info save all information from CSV
                // // name of the columns creates in VisoftMailerModule\Controller\Plugin\MailerPlugin, method - importCsvFile()
                // // Full Name -> full-name etc.
                // $contact->setInfo($contactInfo);
                
                // // for cases when users === contacts
                // if($contact instanceof UserInterface) {
                //     $contact->setRole($subscriberRole);
                // }
                
                $this->entityManager->persist($contact);
                $countContactAdded++;
            } 
            
            $countContactProcessed++;

            if (!($countContactProcessed % 2000)) { # do flushing once per 2000 emails
                $status->setNumContactsProcessed($countContactProcessed);
                $status->setNumContactsAdded($countContactAdded);
                $status->setNumContactsExist($countContactExist);
                $this->entityManager->persist($status);
                $this->entityManager->flush();
                unset($emailsProcessed);
                $emailsProcessed = [];
            }
        }
        $status->setNumContactsProcessed($countContactProcessed);
        $status->setNumContactsAdded($countContactAdded);
        $status->setNumContactsExist($countContactExist);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        // logging
        $message = "------------------------------------------------------------------- \n";
		$message .= "- total contacts: " . $countContactProcessed . " \n";
		$message .= "- contacts added: " . $countContactAdded . " \n";
		$message .= "- contacts exist: " . $countContactExist . " \n";
		$message .= "------------------------------------------------------------------- \n";
		$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Persisting successfully completed. \n";
   		file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);
	}

    public function export(Entity\DatabaseInterface $database)
    {
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        $status = new Entity\StatusDatabaseExport($authenticatedUser);
        $status->setState(0);
        $status->setDatabase($database);
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
        $contactsSubscribed = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findBySibscribedOnMailingLists($status->getDatabase()->getId());
        $csvFilePath = $status->getOutputFilePath();
        $line = null;
        foreach ($contactsSubscribed as $contact) {
            // find header
            if(is_null($line)) {
                // Fields and header determinates in findBySibscribedOnMailingLists function. 
                // If needs to change selection of exported data please refer to findBySibscribedOnMailingLists function
                $line = implode(",", array_keys($contact)) . "\n";; // header
            }
            $line .= implode(',', $contact) . "\n";
            // $line .= $contact['email'] . ', '. $contact['stateName'] . "\n";
        }
        $contactsUnsubscribed = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findByUnibscribedFromMailingLists($status->getDatabase()->getId());
        if(!empty($contactsUnsubscribed))   {
            $line .= "\n" . "Unsubscribed" . "\n";
            foreach ($contactsUnsubscribed as $contact) {
                $line .= implode(',', $contact) . "\n";
                // if(isset($contact['stateName']))
                //     $state = $contact['stateName'];
                // else 
                //     $state = 'Unknown';
                // $line .= $contact['email'] . ', ' . $state . "\n";
            }
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

    protected function setContact(&$contact, $database)
    {

    }

    protected function getDateTimeWithMicroseconds()
    {
    	$time = microtime(true);
		$micro = sprintf("%06d",($time - floor($time)) * 1000000);
		return new \DateTime(date('Y-m-d H:i:s.' . $micro, $time));
    }

    public function downloadExportCsv($mailingListId)
    {
        $mailingList = $this->entityManager->getRepository('VisoftMailerModule\Entity\DatabaseInterface')->findOneBy(['id' => $mailingListId]);
        $status = $this->entityManager->getRepository('VisoftMailerModule\Entity\StatusDatabaseExport')->findOneBy(['database' => $mailingList], ['createdAt' => 'DESC']);
        $outputFilePath = $status->getOutputFilePath();
        // var_dump($outputFilePath);
        // die();
        $fileName = end(explode('/', $outputFilePath));
        // var_dump($fileName);
        // die();
        if (file_exists($outputFilePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            // header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($outputFilePath);
        } else {
            echo 'The file $fileName does not exist';
        }
    }
}