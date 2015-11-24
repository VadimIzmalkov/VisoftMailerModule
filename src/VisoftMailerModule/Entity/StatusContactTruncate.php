<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface,
    VisoftMailerModule\Entity\MailingListInterface;

/**
 * @ORM\Entity
 */
class StatusContactTruncate extends Status
{
    /**
     * @var ContactListInterface
     * @ORM\ManyToOne(targetEntity="VisoftMailerModule\Entity\MailingListInterface")
     * @ORM\JoinColumn(name="mailing_list_id", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $mailingList;

    public function __construct() 
    {
    	parent::__construct();
    }

    public function getMailingList() { return $this->mailingList; }
    public function setMailingList($mailingList) {
    	$this->mailingList = $mailingList;
    	return $this;
    }
}