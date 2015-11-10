<?php

namespace VisoftMailerModule\Controller;

use Zend\Console\Request as ConsoleRequest;

use VisoftBaseModule\Controller\BaseController;

use VisoftMailerModule\Service\ContactServiceInterface,
	VisoftMailerModule\Options\ModuleOptions;

class ContactController extends BaseController
{
	protected $contactService;
	protected $moduleOptions;

	public function __construct(ContactServiceInterface $contactService, ModuleOptions $moduleOptions)
	{
		$this->moduleOptions = $moduleOptions;
		$this->contactService = $contactService;
	}

	public function persistContactsAction()
	{
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest)
            throw new \RuntimeException('You can only use parseAction() from a console!');
        $statusId = $request->getParam('statusid', false);
        
        // Set Gearman client
        $client = new \GearmanClient();
        $client->addServer('127.0.0.1', 4730); // by default host/port will be "localhost" & 4730
        $result = $client->doBackground("persistContacts", $statusId); // Send job

        // Check if worker launched
        $status = $this->getGearmanStatus();
        foreach ($status['connections'] as $key => $connection) {
            if($connection['function'] === 'persistContacts') {
                echo "I found a worker. Waiting in background... \n";
                die("Adios hommie");
            }
        }

        // Set Gearman worker
        $worker = new \GearmanWorker();
        $worker->addServer('127.0.0.1', 4730);
        $worker->setTimeout(240000);
        $worker->addFunction('persistContacts', function (\GearmanJob $job) {
            $statusId = $job->workload();
            $this->contactService->persist($statusId);
            return true;
        });

        // Infinit loop
        while(true) {
            echo "Witing a job... \n";
            $worker->work();
            if ($worker->returnCode() != GEARMAN_SUCCESS) {
                echo "return_code: " . $worker->returnCode() . "\n";
                break;
            }
        }
	}

	// TODO: put it out from there
    public function getGearmanStatus(){
        $status = null;
        // $handle = fsockopen($this->host,$this->port,$errorNumber,$errorString,30);
        $handle = fsockopen('127.0.0.1', 4730, $errorNumber, $errorString, 30);
        if($handle!=null){
            fwrite($handle,"status\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line == ".\n") {
                    break;
                }
                if( preg_match("~^(.*)[ \t](\d+)[ \t](\d+)[ \t](\d+)~", $line, $matches) ) {
                    $function = $matches[1];
                    $status['operations'][$function] = array(
                        'function' => $function,
                        'total' => $matches[2],
                        'running' => $matches[3],
                        'connectedWorkers' => $matches[4],
                    );
                }
            }
            fwrite($handle,"workers\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line==".\n"){
                    break;
                }
                // FD IP-ADDRESS CLIENT-ID : FUNCTION
                if( preg_match("~^(\d+)[ \t](.*?)[ \t](.*?) : ?(.*)~",$line,$matches) ){
                    $fd = $matches[1];
                    $status['connections'][$fd] = array(
                        'fd' => $fd,
                        'ip' => $matches[2],
                        'id' => $matches[3],
                        'function' => $matches[4],
                    );
                }
            }
            fclose($handle);
        }
        return $status;
    }
}
