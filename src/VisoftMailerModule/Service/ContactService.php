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

    public function uploadCsvFile2Db($uploadCsvFile, $database)
    {
        // move file to target dir
        $targetDir = $this->moduleOptions->getContactUploadedCsvDir();
        $uploadCsvFileArray = $uploadCsvFile->toArray();
        $uploadElementName = key($uploadCsvFileArray);
        $csvFileName = $uploadCsvFileArray[$uploadElementName]['name'];
        $csvFileInfo = pathinfo($csvFileName);
        $now = new \DateTime();
        $receiver = new \Zend\File\Transfer\Adapter\Http();
        $receiver->setDestination($targetDir)
            ->setFilters([
                new \Zend\Filter\File\Rename([
                    "target" => $targetDir . '/uploaded_csv_' . $now->format('Y_m_d-H:i') . '_' . '.' . $csvFileInfo['extension'],
                    "randomize" => true,
                ]),
            ]);
        $receiver->receive($uploadElementName);
        $newCsvFilePath = $receiver->getFileName($uploadElementName);

        // transform CSV file to array
        $contactsArray = $this->csvFile2Array($newCsvFilePath);

        // save array to databse
        $this->runProcessSave2Database($database, $contactsArray);
    }

	public function runProcessSave2Database($database, array $contactsArray)
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

	public function save2Database($status)
	{
       	// forming of header log message 
       	$reportFilePath = $status->getOutputFilePath();
        echo 'Report file: ' . $reportFilePath;
       	$message = "====================  PERSIST CONTACTS REPORT  ====================\n";
       	$message .= "Status id: " . $status->getId() . "\n";
       	$message .= "Date: " . $this->getDateTimeWithMicroseconds()->format('d/m/Y') . "\n";
       	$message .= "------------------------------------------------------------------- \n";
       	$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Connected to worker. \n";
       	$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Saving started. \n";
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

        // start to process every contact
        foreach ($contactsArray as $contactArray) {
            $contactExists = false;

            /*
            check if contact already exists
            unique data can be
            - e-mail
            - phone
            unique data sets in visoftmailermodule.global.php
            */

            // peristing allowed only if same field exists in contact's info - exmp.: $contactsInfo['email']
            if(!isset($contactArray[$uniqueField]))
                continue;

            // check if contact exists in database
            $contact = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findOneBy([$uniqueField => $contactArray[$uniqueField]]);
            if(!empty($contact))
                $contactExists = true;

            // check if contact in buffer array
            $emailsProcessedLower = array_map('strtolower', $emailsProcessed);
            $emailContactLower = strtolower($contactArray[$uniqueField]);
            if(in_array($emailContactLower, $emailsProcessedLower)) {
                $countContactExist++;
                $contactExists = true;
            } else {
                array_push($emailsProcessed, $contactArray[$uniqueField]);
            }

            if($contactExists) {
                $message = "Warning: " . $contactArray[$uniqueField] . " alredy exists and cannot be added \n";
                file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);
                $countContactExist++;
                continue;
            }

            // create new entity and save to Database
            $contact = $this->createContactEntity($contactArray, $database); 
            $this->entityManager->persist($contact);
            
            $countContactAdded++;
            $countContactProcessed++;

            if (!($countContactProcessed % 2000)) { # do flushing once per 2000 emails
                $status->setNumContactsProcessed($countContactProcessed);
                $status->setNumContactsAdded($countContactAdded);
                $status->setNumContactsExist($countContactExist);
                $this->entityManager->persist($status);
                $this->entityManager->flush();
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
		$message .= "[" . $this->getDateTimeWithMicroseconds()->format('d/m/Y H:i:s.u') . "] Saving successfully completed. \n";
   		file_put_contents($reportFilePath, $message, FILE_APPEND | LOCK_EX);
	}

    // argument $parameter can be entity of Database or string parameter if custom export needed
    public function runProcessExport($parameter)
    {
        $now = new \DateTime();
        $authenticatedUser = $this->authenticationService->getIdentity();
        $status = new Entity\StatusDatabaseExport($authenticatedUser);
        $status->setState(0);
        if($parameter instanceof \TinyCRM\Entity\Database)
            $status->setDatabase($parameter);
        else
            $status->setExtraParameter($parameter);
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

    // public function dump($status)
    // {
    //     $this->save2File($status);
    //     // var_dump($status);
    //     // die('123');
        
    //     // var_dump(count($contacts));
    //     // die('123');
    //     // $contactsSubscribed = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findBySibscribedOnMailingLists($status->getDatabase()->getId());
    //     // $csvFilePath = $status->getOutputFilePath();
    //     // $line = null;
    //     // foreach ($contactsSubscribed as $contact) {
    //     //     // find header
    //     //     if(is_null($line)) {
    //     //         // Fields and header determinates in findBySibscribedOnMailingLists function. 
    //     //         // If needs to change selection of exported data please refer to findBySibscribedOnMailingLists function
    //     //         $line = implode(",", array_keys($contact)) . "\n";; // header
    //     //     }
    //     //     $line .= implode(',', $contact) . "\n";
    //     //     // $line .= $contact['email'] . ', '. $contact['stateName'] . "\n";
    //     // }
    //     // $contactsUnsubscribed = $this->entityManager->getRepository('VisoftMailerModule\Entity\ContactInterface')->findByUnibscribedFromMailingLists($status->getDatabase()->getId());
    //     // if(!empty($contactsUnsubscribed))   {
    //     //     $line .= "\n" . "Unsubscribed" . "\n";
    //     //     foreach ($contactsUnsubscribed as $contact) {
    //     //         $line .= implode(',', $contact) . "\n";
    //     //         // if(isset($contact['stateName']))
    //     //         //     $state = $contact['stateName'];
    //     //         // else 
    //     //         //     $state = 'Unknown';
    //     //         // $line .= $contact['email'] . ', ' . $state . "\n";
    //     //     }
    //     // }
    //     // file_put_contents($csvFilePath, $line, FILE_APPEND | LOCK_EX);
    //     // unset($line);
    // }

    public function createCsvFile($status)
    {
        $database = $status->getDatabase();
        $contacts = $this->entityManager->getRepository('TinyCRM\Entity\Contact')->findArrayByDatabase($database->getId());
        $csvFilePath = $status->getOutputFilePath();
        $line = null;
        foreach ($contacts as $contact) {
            // find header
            if(is_null($line)) {
                // Fields and header determinates in findBySibscribedOnMailingLists function. 
                // If needs to change selection of exported data please refer to findBySibscribedOnMailingLists function
                $line = implode(",", array_keys($contact)) . "\n"; // header
            }
            $first = true;
            foreach ($contact as $value) {
                if($first) {
                    $line .= '"' . $value . '"';
                    $first = false;
                } else {
                    $line .= ', "' . $value . '"';
                }
            }
            $line .= "\n";
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

    protected function createContactEntity($contactArray, $database)
    {

    }

    // protected function setContact(&$contact, $database, $contactInfo)
    // {

    // }

    protected function getDateTimeWithMicroseconds()
    {
    	$time = microtime(true);
		$micro = sprintf("%06d",($time - floor($time)) * 1000000);
		return new \DateTime(date('Y-m-d H:i:s.' . $micro, $time));
    }

    public function downloadExportCsv($statusId)
    {
        // $mailingList = $this->entityManager->getRepository('VisoftMailerModule\Entity\DatabaseInterface')->findOneBy(['id' => $mailingListId]);
        $status = $this->entityManager->find('VisoftMailerModule\Entity\StatusDatabaseExport', $statusId); //->findOneBy(['database' => $mailingList], ['createdAt' => 'DESC']);
        $outputFilePath = $status->getOutputFilePath();
        // var_dump($outputFilePath);
        // die();
        $outputFilePathExploded = explode('/', $outputFilePath);
        $fileName = end($outputFilePathExploded);
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

    protected function csvFile2Array($csvFilePath)
    {
        // detect delimiter for csv
        $delimiter = self::detectCsvFileDelimiter($csvFilePath);

        // convert file to array 
        $contactsArray = file($csvFilePath);

        // get titles of columns and transform
        $columnNames = str_getcsv($contactsArray[0], $delimiter);
        array_walk($columnNames, function(&$item) {
            $item = str_replace(" ", "-", $item);
            $item = strtolower($item); 
        });

        foreach ($contactsArray as $key => $contact) {
            // get CSV line by line
            $contact = str_getcsv($contact, $delimiter);
            
            // change keys in array to column names
            $contact = array_combine($columnNames, $contact);

            // detect Windows-1251 ecoding and change to UTF-8
            array_walk($contact, function(&$item) {
                $encoding = mb_detect_encoding($item, array('UTF-8', 'Windows-1251', 'KOI8-R'));
                // if(mb_check_encoding($item, 'CP1251')){
                //     $item = iconv('CP1251', 'UTF-8', $item);
                // }
                if($encoding !== 'UTF-8') {
                    $item = iconv('CP1251', 'UTF-8', $item);
                }
                $item = \VisoftBaseModule\Service\ForceUTF8\Encoding::toUTF8($item);
                // if(mb_check_encoding($item, 'CP1251')){
                //     $item = iconv('CP1251', 'UTF-8', $item);
                // }
            });

            // rewrite current element to new one
            $contactsArray[$key] = $contact;
        }

        // remove column header
        array_shift($contactsArray);

        return $contactsArray;
    }

    public static function detectCsvFileDelimiter($csvFilePath, $checkLines = 5)
    {
        $file = new \SplFileObject($csvFilePath);
        $delimiters = [
            ',', 
            '\t', 
            ';', 
            '|', 
            ':'
        ];
        $results = array();
        $i = 0;
        while($file->valid() && $i <= $checkLines){
            $line = $file->fgets();
            foreach ($delimiters as $delimiter){
                $regExp = '/['.$delimiter.']/';
                $fields = preg_split($regExp, $line);
                if(count($fields) > 1){
                    if(!empty($results[$delimiter])){
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }   
                }
            }
           $i++;
        }
        $results = array_keys($results, max($results));
        return $results[0];
    }
}