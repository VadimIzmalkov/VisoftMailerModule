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

	public function sendCampaignAction()
	{
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest)
            throw new \RuntimeException('You can only use from a console');
        $statusId = $request->getParam('statusid', false);
        if(!$statusId)
            throw new \RuntimeException('Status id not specified');
        $process = $this->processingService->createBackgroundProcess("sendCampaign", $statusId);
        $process->getWorker()->addFunction('sendCampaign', function (\GearmanJob $job) {
            $this->mailerService->send($job->workload());
            return true;
        });
        $process->run();
	}
}