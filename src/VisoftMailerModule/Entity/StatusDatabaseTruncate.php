<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class StatusDatabaseTruncate extends Status
{
    /**
     * @var ContactListInterface
     * @ORM\ManyToOne(targetEntity="VisoftMailerModule\Entity\DatabaseInterface")
     * @ORM\JoinColumn(name="database_id", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $database;

    public function __construct() 
    {
    	parent::__construct();
    }

    public function getDatabase() { return $this->database; }
    public function setDatabase($database) {
    	$this->database = $database;
    	return $this;
    }
}