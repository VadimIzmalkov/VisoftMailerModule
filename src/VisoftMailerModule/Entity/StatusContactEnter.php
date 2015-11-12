<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftMailerModule\Entity\UserInterface,
    VisoftMailerModule\Entity\MailingListInterface;

/**
 * @ORM\Entity
 */
class StatusContactEnter extends Status
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

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="VisoftMailerModule\Entity\MailingListInterface")
     * @ORM\JoinTable(name="statuses_contact_enter",
     * joinColumns={@ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="mailing_list_id", referencedColumnName="id", unique=false)}
     * )
     */
    protected $mailingLists;

    public function __construct(UserInterface $createdBy, $emailsString) {
    	parent::__construct($createdBy);
    	$this->emailsString = $emailsString;
    	$this->numContacts = 0;
    	$this->numContactsAdded = 0;
    	$this->numContactsExist = 0;
        $this->mailingLists = new ArrayCollection();
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

    public function getContactLists() { return $this->mailingLists; }
    public function addContactList(MailingListInterface $mailingList) {
        $this->mailingLists->add($mailingList);
        return $this;
    }
    public function addContactLists($mailingLists) {
        foreach ($mailingLists as $mailingList) $this->mailingLists->add($mailingList);
        return $this;
    }
    public function removeContactList(MailingListInterface $mailingList) {
        $this->mailingLists->removeElement($mailingList);
        return $this;
    }
    public function removeContactLists($mailingLists) {
        foreach ($mailingLists as $mailingList) $this->mailingLists->removeElement($mailingList);
        return $this;
    }
}
	