<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="visoft_mailer_statuses")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap( {
 *  "status" = "Status",
 *  "status_contact_enter" = "StatusContactEnter",
 *  "status_contact_export" = "StatusContactExport",
 *  "status_mailer" = "StatusMailer",
 * } )
 */
class Status
{
	/**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    protected $token;

    /**
     * @var integer 0-created | 1-pending | 2-in progress | 3-finished | 4-error 
     * @ORM\Column(name="state", type="integer", nullable=true)
     */
    protected $state;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     */
    protected $startedAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="finished_at", type="datetime", nullable=true)
     */
    protected $finishedAt;

    /**
     * @var UserInterface
     * @ORM\ManyToOne(targetEntity="VisoftBaseModule\Entity\UserInterface")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $createdBy;

    /**
     * @var string
     * @ORM\Column(name="output_file_path", type="string", nullable=true)
     */
    protected $outputFilePath;

    public function __construct(UserInterface $createdBy)
    {
    	$this->createdAt = new \DateTime();
        $this->createdBy = $createdBy;
    	$this->token = md5(uniqid(mt_rand(), true));
    }

    public function getId() { return $this->id; }

    public function getToken() { return $this->token; }

    public function getState() { return $this->state; }
    public function setState($state) {
    	$this->state = $state;
    	return $this;
    }

    public function getOutputFilePath() { return $this->outputFilePath; }
    public function setOutputFilePath($outputFilePath) {
        $this->outputFilePath = $outputFilePath;
        return $this;
    }

    public function getCreatedAt() { return $this->createdAt; }

    public function getStartedAt() { return $this->startedAt; }
    public function setStartedAt($startedAt) {
    	$this->startedAt = $startedAt;
    	return $this;
    }

    public function getCreatedBy() { return $this->createdBy; }
    // public function setCreatedBy($createdBy) {
    //     $this->createdBy = $createdBy;
    //     return $this;
    // }

    public function getFinishedAt() { return $this->finishedAt; }
    public function setFinishedAt($finishedAt) {
    	$this->finishedAt = $finishedAt;
    	return $this;
    }
}