<?php

namespace VisoftMailerModule\Controller;

use Zend\Console\Request as ConsoleRequest,
    Zend\View\Model\JsonModel;

use Doctrine\ORM\EntityManager;

use VisoftBaseModule\Controller\BaseController,
    VisoftBaseModule\Service\ProcessingService,
    VisoftMailerModule\Options\ModuleOptions,
    VisoftMailerModule\Service\ContactServiceInterface;

class ContactController extends BaseController
{
    protected $entityManager;
	protected $contactService;
	protected $moduleOptions;
    protected $processingService;

	public function __construct(
        EntityManager $entityManager,
        ContactServiceInterface $contactService, 
        ModuleOptions $moduleOptions, 
        ProcessingService $processingService
    )
	{
        $this->entityManager = $entityManager;
		$this->moduleOptions = $moduleOptions;
		$this->contactService = $contactService;
        $this->processingService = $processingService;
	}

	public function contactsEnterAction()
	{
        $status = $this->getStatusFromRoute();
        $process = $this->processingService->createBackgroundProcess("contactsEnter", $status->getId());
        $process->getWorker()->addFunction('contactsEnter', function (\GearmanJob $job) {
            $status = $this->contactService->processStarted($job->workload());
            $this->contactService->save2Database($status);
            $this->contactService->processCompleted($status);
            return true;
        });
        $process->run();
	}

    public function contactsExportAction()
    {
        $status = $this->getStatusFromRoute();
        $process = $this->processingService->createBackgroundProcess("contactsExport", $status->getId());
        $process->getWorker()->addFunction('contactsExport', function (\GearmanJob $job) {
            $status = $this->contactService->processStarted($job->workload());
            $this->contactService->createCsvFile($status);
            $this->contactService->processCompleted($status);
            return true;
        });
        $process->run();
    }

    public function contactsTruncateAction()
    {
        $status = $this->getStatusFromRoute();
        $process = $this->processingService->createBackgroundProcess("contactsTruncate", $status->getId());
        $process->getWorker()->addFunction('contactsTruncate', function (\GearmanJob $job) {
            $status = $this->contactService->processStarted($job->workload());
            $this->contactService->truncate($status);
            $this->contactService->processCompleted($status);
            return true;
        });
        $process->run();
    }

    public function updateStateStatusExportAjaxAction()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $statusId = $this->params()->fromRoute('entityId');
            $status = $this->entityManager->find('VisoftMailerModule\Entity\Status', $statusId);
            if($status->getState() === 2)
                return new JsonModel(['code' => true]);
            else
                return new JsonModel(['code' => false]);
        }
        return $this->notFoundAction();
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
        return $status;
    }
}
