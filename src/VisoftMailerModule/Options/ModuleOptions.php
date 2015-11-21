<?php

namespace VisoftMailerModule\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
	protected $dataModuleDir;
    protected $contactLogDir;
    protected $contactExportedCsvDir;
    protected $contactReportsDir;

    protected $mailerDir;
    protected $mailerLogDir;

    // tempaltes paths
    protected $emailTemplateLayoutAPath;

    public function __construct($options)
    {
    	$this->dataModuleDir = getcwd() . '/data/VisoftMailerModule';
    	$this->logDir = $this->dataModuleDir . '/log';
    	$this->contactExportedCsvDir = $this->dataModuleDir . '/contacts/exported-csv';
        $this->contactReportsDir = $this->dataModuleDir . '/contacts/reports';

    	parent::__construct($options);
    }

    public function getDataModuleDir() { return $this->dataModuleDir; }
    public function setDataModuleDir($dataModuleDir) { 
    	$this->dataModuleDir = $dataModuleDir;
        return $this;
    }

    public function getLogDir() { return $this->logDir; }
    public function setLogDir($logDir) { 
    	$this->logDir = $logDir;
        return $this;
    }

    public function getContactExportedCsvDir() { return $this->contactExportedCsvDir; }
    public function setContactExportedCsvDir($contactExportedCsvDir) { 
    	$this->contactExportedCsvDir = $contactExportedCsvDir;
        return $this;
    }

    public function getContactReportsDir() { return $this->contactReportsDir; }
    public function setContactReportsDir($contactReportsDir) { 
        $this->contactReportsDir = $contactReportsDir;
        return $this;
    }
}