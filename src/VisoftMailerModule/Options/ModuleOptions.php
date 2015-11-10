<?php

namespace VisoftMailerModule\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    protected $contactEnterLogDir;
    protected $contactExportLogDir;

    public function __construct($options)
    {
    	$this->contactEnterLogDir = getcwd() . '/data/VisoftMailerModule/log/contact/enter/';
    	$this->contactExportLogDir = getcwd() . '/data/VisoftMailerModule/log/contact/export/';
    	parent::__construct($options);
    }

    public function getContactEnterLogDir() { return $this->contactEnterLogDir; }
    public function setContactEnterLogDir($contactEnterLogDir) { 
    	$this->contactEnterLogDir = $contactEnterLogDir;
        return $this;
    }

    public function getContactExportLogDir() { return $this->contactExportLogDir; }
    public function setContactExportLogDir($contactExportLogDir) { 
    	$this->contactExportLogDir = $contactExportLogDir;
        return $this;
    }
}