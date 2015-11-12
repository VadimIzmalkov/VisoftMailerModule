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
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest)
            throw new \RuntimeException('You can only use from a console');
        $statusId = $request->getParam('statusid', false);
        if(!$statusId)
            throw new \RuntimeException('Status id not specified');
        $process = $this->processingService->createBackgroundProcess("contactsEnter", $statusId);
        $process->getWorker()->addFunction('contactsEnter', function (\GearmanJob $job) {
            $this->contactService->persist($job->workload());
            return true;
        });
        $process->run();
	}

    public function contactsExportAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest)
            throw new \RuntimeException('You can only use from a console');
        $statusId = $request->getParam('statusid', false);
        if(!$statusId)
            throw new \RuntimeException('Status id not specified');
        $process = $this->processingService->createBackgroundProcess("contactsExport", $statusId);
        $process->getWorker()->addFunction('contactsExport', function (\GearmanJob $job) {
            $this->contactService->dump($job->workload());
            return true;
        });
        $process->run();
    }

    public function updateStateStatusExportAjaxAction()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $statusId = $this->params()->fromRoute('entityId');
            $status = $this->entityManager->getRepository('VisoftMailerModule\Entity\Status')->findOneBy(['id' => $statusId]);
            if($status->getState() === 2)
                return new JsonModel(['code' => true]);
            else
                return new JsonModel(['code' => false]);
        }
        return $this->notFoundAction();
    }
}
