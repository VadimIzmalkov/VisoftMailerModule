<?php

namespace VisoftMailerModule\Controller;

use VisoftBaseModule\Controller\BaseController,    
	VisoftBaseModule\Service\ProcessingService,
	VisoftMailerModule\Entity\MailingListInterface;

class MailerController extends BaseController
{
	protected $entityManager;
    protected $processingService;

	public function __construct(
		EntityManager $entityManager,
		ProcessingService $processingService
	)
	{
		$this->entityManager = $entityManager;
		$this->processingService = $processingService;
	}

	public function sendMailsAction()
	{
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest)
            throw new \RuntimeException('You can only use from a console');
        $statusId = $request->getParam('statusid', false);
        if(!$statusId)
            throw new \RuntimeException('Status id not specified');
        $process = $this->processingService->createBackgroundProcess("sendMails", $statusId);
        $process->getWorker()->addFunction('sendMails', function (\GearmanJob $job) {
            $this->mailerService->performSending($job->workload());
            return true;
        });
        $process->run();
	}
}