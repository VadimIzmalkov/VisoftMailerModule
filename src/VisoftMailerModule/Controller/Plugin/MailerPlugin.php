<?php
namespace VisoftMailerModule\Controller\Plugin;

use VisoftMailerModule\Entity;

class MailerPlugin extends \Zend\Mvc\Controller\Plugin\AbstractPlugin
{
	protected $authenticationService;
	protected $entityManager;
    protected $moduleOptions;
    protected $contactService;

	public function __construct($entityManager, $authenticationService, $moduleOptions, $contactService)
	{
		$this->entityManager = $entityManager;
		$this->authenticationService = $authenticationService;
        $this->moduleOptions = $moduleOptions;
        $this->contactService = $contactService;
	}

	public function send(array $contactsArray, $emailTemplate, $parametersArray, $subject, $type)
	{
		$now = new \DateTime();

		/* 1. Save caontacts to intermidate file */
		// convertinf contacts array to json 
		$contactsJson = json_encode($contactsArray);
		// generate json files with random name
		$contactsJsonFilePath = $this->moduleOptions->getMailingContactsJsonDir() . '/contacts_' . md5(uniqid(mt_rand(), true)) . '.json';
        $contactsProcessedJsonFilePath = $this->moduleOptions->getMailingContactsJsonDir() . '/contacts_processed_' . md5(uniqid(mt_rand(), true)) . '.json';
		// saving json data to file
        file_put_contents($contactsJsonFilePath, $contactsJson);

        /* 2. Create status for mailing process */
        // file for report
        $reportFileName = 'mailing_' . $now->format('d-m-Y_H-i-s') . '.txt';
        $reportFilePath = $this->moduleOptions->getContactReportsDir() . '/' . $reportFileName;
        $identity = $this->authenticationService->getIdentity();
        $status = new Entity\StatusMailing();
        if(!empty($identity))
            $status->setCreatedBy($identity);
        $status->setNumTotalContacts(count($contactsArray));
        $status->setContactsJsonFilePath($contactsJsonFilePath);
        $status->setContactsProcessedJsonFilePath($contactsProcessedJsonFilePath);
        $status->setEmailTemplate($emailTemplate);
        $status->setParametersJson(json_encode($parametersArray));        
        $status->setOutputFilePath($reportFilePath);
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        /* 3. Start sending in separate process */
        $logWorkerFilePath = $this->moduleOptions->getLogDir() . '/worker_send_mails_' . $now->format("Y-m-d_H-i-s") . '.log';
        $errWorkerFilePath = $this->moduleOptions->getLogDir() . '/worker_send_mails_' . $now->format("Y-m-d_H-i-s") . '.err';
        switch ($type) {
        	case 'bulk':
        		$shell = 'php public/index.php send-bulk ' . $status->getId() . ' >' . $logWorkerFilePath . ' 2>' . $errWorkerFilePath . ' &';
        		break;
        	case 'individual':
        		$shell = 'php public/index.php send-individual ' . $status->getId() . ' >' . $logWorkerFilePath . ' 2>' . $errWorkerFilePath . ' &';
        		break;
        	default:
        		# code...
        		break;
        }
        
        shell_exec($shell);

        /* return status for further tracking */
        return $status;
	}

    public function importCsvFile($filePath, $database)
    {
        // get dir for uploaded csv
        $targetDir = $this->moduleOptions->getContactUploadedCsvDir();

        // transfer uploded file
        $now = new \DateTime();
        $fileInfo = pathinfo($filePath);
        $receiver = new \Zend\File\Transfer\Adapter\Http();
        $receiver->setDestination($targetDir)
            ->setFilters([
                new \Zend\Filter\File\Rename([
                    "target" => $targetDir . '/uploaded_csv_' . $now->format('Y_m_d-H:i') . '_' . '.' . $fileInfo['extension'],
                    "randomize" => true,
                ]),
            ]);
        // file upload element should have name - 'csv-file'
        $receiver->receive('csv-file');
        $uploadedCsvFilePath = $receiver->getFileName('csv-file');

        // detect delimiter for csv
        $delimiter = self::detectCsvFileDelimiter($uploadedCsvFilePath);

        // convert file to array 
        $contactsArray = file($uploadedCsvFilePath);

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
            // var_dump($columnNames);
            // var_dump($contact);
            // die('ddd');
            $contact = array_combine($columnNames, $contact);

            // detect Windows-1251 ecoding and change to UTF-8
            array_walk($contact, function(&$item) {
                if(mb_check_encoding($item, 'CP1251')){
                    $item = iconv('CP1251', 'UTF-8', $item);
                }
            });

            // rewrite current element to new one
            $contactsArray[$key] = $contact;
        }

        // remove column header
        array_shift($contactsArray);

        // save contacts to database
        $this->contactService->enter($database, $contactsArray);
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
