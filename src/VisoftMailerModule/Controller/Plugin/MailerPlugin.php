<?php
namespace VisoftMailerModule\Controller\Plugin;

use VisoftMailerModule\Entity;

class MailerPlugin extends \Zend\Mvc\Controller\Plugin\AbstractPlugin
{
	protected $authenticationService;
	protected $entityManager;
    protected $moduleOptions;

	public function __construct($entityManager, $authenticationService, $moduleOptions)
	{
		$this->entityManager = $entityManager;
		$this->authenticationService = $authenticationService;
        $this->moduleOptions = $moduleOptions;
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
}
