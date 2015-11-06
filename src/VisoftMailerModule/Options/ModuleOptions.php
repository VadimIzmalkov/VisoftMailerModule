<?php

namespace VisoftMailerModule\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    protected $logExportFilesDir = '/var/www/html/callcenter_v1_22/data/log/export-contacts/';

    public function getLogExportFilesDir() { return $this->logExportFilesDir; }
    public function setLogExportFilesDir($logExportFilesDir) { 
    	$this->logExportFilesDir = $logExportFilesDir;
        return $this;
    }
}