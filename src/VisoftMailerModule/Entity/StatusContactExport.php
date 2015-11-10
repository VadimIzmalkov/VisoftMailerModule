<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftMailerModule\Entity\UserInterface;

/**
 * @ORM\Entity
 */
class StatusContactExport extends Status
{	
    /**
     * @var ContactInterface
     * @ORM\ManyToOne(targetEntity="VisoftMailerModule\Entity\ContactInterface")
     * @ORM\JoinColumn(name="contact_list", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $contactList;

    public function __construct(UserInterface $createdBy) {
    	parent::__construct($createdBy);
    }

    public function getContactList() { return $this->contactList; }
    public function setContactList($contactList) {
    	$this->contactList = $contactList;
    	return $this;
    }
}
	