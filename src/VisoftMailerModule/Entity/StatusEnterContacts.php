<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftMailerModule\Entity\UserInterface;

/**
 * @ORM\Entity
 */
class StatusEnterContacts extends Status
{	
    /**
     * @var text
     * @ORM\Column(name="emails_string", type="text", nullable=true)
     */
    protected $emailsString;

    /**
     * @var integer
     * @ORM\Column(name="num_contacts", type="integer", nullable=true)
     */
    protected $numContacts;

    /**
     * @var integer
     * @ORM\Column(name="num_contacts_added", type="integer", nullable=true)
     */
    protected $numContactsAdded;

    /**
     * @var integer
     * @ORM\Column(name="num_contacts_exist", type="integer", nullable=true)
     */
    protected $numContactsExist;

    public function __construct(UserInterface $createdBy, $emailsString) {
    	parent::__construct($createdBy);
    	$this->emailsString = $emailsString;
    	$this->numContacts = 0;
    	$this->numContactsAdded = 0;
    	$this->numContactsExist = 0;
    }

    public function getEmailsString() { return $this->emailsString; }
    // public function setEmailsString($state) {
    // 	$this->state = $state;
    // 	return $this;
    // }

    public function getNumContacts() { return $this->numContacts; }
    public function setNumContacts($numContacts) {
    	$this->numContacts = $numContacts;
    	return $this;
    }

    public function getNumContactsAdded() { return $this->numContactsAdded; }
    public function setNumContactsAdded($numContactsAdded) {
    	$this->numContactsAdded = $numContactsAdded;
    	return $this;
    }

    public function getNumContactsExist() { return $this->numContactsExist; }
    public function setNumContactsExist($numContactsExist) {
    	$this->numContactsExist = $numContactsExist;
    	return $this;
    }
}
	