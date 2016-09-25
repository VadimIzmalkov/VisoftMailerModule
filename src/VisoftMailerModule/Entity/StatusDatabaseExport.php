<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class StatusDatabaseExport extends Status
{
    /**
     * @var ContactListInterface
     * @ORM\ManyToOne(targetEntity="VisoftMailerModule\Entity\DatabaseInterface")
     * @ORM\JoinColumn(name="database_id", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $database;

    // use this parameter if you need to export custom data, not database
    /**
     * @var string
     * @ORM\Column(name="extra_parameter", type="string", nullable=true)
     */
    protected $extraParameter;

    public function __construct() {
    	parent::__construct();
    }

    public function getDatabase() { return $this->database; }
    public function setDatabase($database) { $this->database = $database; }

    public function getExtraParameter() { return $this->extraParameter; }
    public function setExtraParameter($extraParameter) { $this->extraParameter = $extraParameter; }
}
	