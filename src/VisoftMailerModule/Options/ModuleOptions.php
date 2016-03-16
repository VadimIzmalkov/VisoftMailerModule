<?php

namespace VisoftMailerModule\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    // directories for storing intermediate data 
	protected $rootModuleDir;
    protected $logDir; 
    protected $contactExportedCsvDir;
    protected $contactReportsDir;
    protected $contactEnterJsonDir;
    protected $mailingContactsJsonDir;

    protected $uniqueField;
    protected $recentlyAddedStateId;


    public function __construct($options)
    {
    	$this->rootModuleDir = getcwd() . '/data/VisoftMailerModule';
    	$this->logDir = $this->rootModuleDir . '/log';
    	$this->contactExportedCsvDir = $this->rootModuleDir . '/contacts/exported-csv';
        $this->contactReportsDir = $this->rootModuleDir . '/contacts/reports';
        $this->contactEnterJsonDir = $this->rootModuleDir . '/contacts/enter-json';
        $this->mailingContactsJsonDir = $this->rootModuleDir . '/mailing/contacts-json';
        $this->mailingReportsDir = $this->rootModuleDir . '/mailing/reports';

        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->rootModuleDir);
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->logDir);
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->contactExportedCsvDir);
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->contactReportsDir);
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->contactEnterJsonDir);
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->mailingContactsJsonDir);
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->mailingReportsDir);

    	parent::__construct($options);
    }

    public function getRootModuleDir() { return $this->rootModuleDir; }
    public function setRootModuleDir($rootModuleDir) { 
    	$this->rootModuleDir = $rootModuleDir;
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

    public function getContactEnterJsonDir() { return $this->contactEnterJsonDir; }
    public function setContactEnterJsonDir($contactEnterJsonDir) { 
        $this->contactEnterJsonDir = $contactEnterJsonDir;
        return $this;
    }

    public function getMailingContactsJsonDir() { return $this->mailingContactsJsonDir; }
    public function setMailingContactsJsonDir($mailingContactsJsonDir) { 
        $this->mailingContactsJsonDir = $mailingContactsJsonDir;
        return $this;
    }

    public function getMailingReportsDir() { return $this->mailingReportsDir; }
    public function setMailingReportsDir($mailingReportsDir) { 
        $this->mailingReportsDir = $mailingReportsDir;
        return $this;
    }

    public function getUniqueField() { return $this->uniqueField; }
    public function setUniqueField($uniqueField) { 
        $this->uniqueField = $uniqueField;
        return $this;
    }

    public function getRecentlyAddedStateId() { return $this->recentlyAddedStateId; }
    public function setRecentlyAddedStateId($recentlyAddedStateId) { 
        $this->recentlyAddedStateId = $recentlyAddedStateId;
        return $this;
    }
}