<?php

namespace VisoftMailerModule\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
	protected $contactDir;
    protected $contactLogDir;
    protected $contactExportedCsvDir;
    protected $contactReportsDir;

    public function __construct($options)
    {
    	$this->contactDir = getcwd() . '/data/VisoftMailerModule/contact';
    	$this->contactLogDir = $this->contactDir . '/log';
    	$this->contactExportedCsvDir = $this->contactDir . '/exported-csv';
        $this->contactReportsDir = $this->contactDir . '/reports';
    	parent::__construct($options);
    }

    public function getContactDir() { return $this->contactDir; }
    public function setContactDir($contactDir) { 
    	$this->contactDir = $contactDir;
        return $this;
    }

    public function getContactLogDir() { return $this->contactLogDir; }
    public function setContactLogDir($contactLogDir) { 
    	$this->contactLogDir = $contactLogDir;
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