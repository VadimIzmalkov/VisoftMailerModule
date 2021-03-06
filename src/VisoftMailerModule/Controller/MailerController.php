<?php

namespace VisoftMailerModule\Controller;

use Zend\Console\Request as ConsoleRequest;

use Doctrine\ORM\EntityManager;

use VisoftBaseModule\Controller\BaseController,    
	VisoftBaseModule\Service\ProcessingService,
    VisoftMailerModule\Options\ModuleOptions,
	VisoftMailerModule\Service\MailerServiceInterface;

class MailerController extends BaseController
{
	protected $entityManager;
    protected $processingService;
    protected $moduleOptions;
    protected $mailerService;

	public function __construct(
		EntityManager $entityManager,
		MailerServiceInterface $mailerService, 
		ModuleOptions $moduleOptions,
		ProcessingService $processingService
	)
	{
		$this->entityManager = $entityManager;
		$this->mailerService = $mailerService;
		$this->moduleOptions = $moduleOptions;
		$this->processingService = $processingService;
	}

	public function sendBulkAction()
	{
        $status = $this->getStatusFromRoute();
        $serverUrl = $this->getRequest()->getParam('serverurl', false);

        $parameters = [
            'statusId' => $status->getId(),
            'serverUrl' => $serverUrl,
        ];
        $workload = json_encode($parameters);
        $process = $this->processingService->createBackgroundProcess("sendBulk", $workload);
        $process->getWorker()->addFunction('sendBulk', function (\GearmanJob $job) {
            $parameters = json_decode($job->workload(), true);
        	$status = $this->mailerService->processStarted($parameters['statusId']);
            $this->mailerService->send($status, $parameters['serverUrl']);
            $this->mailerService->processCompleted($status);
            return true;
        });
        $process->run();
	}

    public function sendIndividualAction()
    {
        $status = $this->getStatusFromRoute();
        $serverUrl = $this->getRequest()->getParam('serverurl', false);

        $parameters = [
            'statusId' => $status->getId(),
            'serverUrl' => $serverUrl,
        ];
        $workload = json_encode($parameters);
        $process = $this->processingService->createBackgroundProcess("sendIndividual", $workload);
        $process->getWorker()->addFunction('sendIndividual', function (\GearmanJob $job) {
            $parameters = json_decode($job->workload(), true);
            $status = $this->mailerService->processStarted($parameters['statusId']);
            $this->mailerService->send($status, $parameters['serverUrl']);
            $this->mailerService->processCompleted($status);
            return true;
        });
        $process->run();
    }

    private function getStatusFromRoute()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest)
            throw new \RuntimeException('You can only use from a console');
        $statusId = $request->getParam('statusid', false);
        if(!$statusId)
            throw new \RuntimeException('Status id not specified');
        $status = $this->entityManager->find('VisoftMailerModule\Entity\Status', $statusId);

        $status->setState(1);
        $this->entityManager->persist($status);
        $this->entityManager->flush();
        return $status;
    }
}