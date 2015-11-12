<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface;

/**
 * @ORM\Entity
 */
class StatusContactExport extends Status
{
    /**
     * @var ContactListInterface
     * @ORM\ManyToOne(targetEntity="VisoftMailerModule\Entity\MailingListInterface")
     * @ORM\JoinColumn(name="contact_list", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $mailingList;

    public function __construct(UserInterface $createdBy) {
    	parent::__construct($createdBy);
    }

    public function getMailingList() { return $this->mailingList; }
    public function setMailingList($mailingList) {
    	$this->mailingList = $mailingList;
    	return $this;
    }
}
	