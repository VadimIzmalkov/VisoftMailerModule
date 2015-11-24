<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface,
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
     * @ORM\JoinTable(name="visoft_mailer_statuses_contacts_enter_to_mailing_lists",
     * joinColumns={@ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="mailing_list_id", referencedColumnName="id", unique=false)}
     * )
     */
    protected $mailingLists;

    public function __construct() {
    	parent::__construct();
    	$this->numContacts = 0;
    	$this->numContactsAdded = 0;
    	$this->numContactsExist = 0;
        $this->mailingLists = new ArrayCollection();
    }

    public function getEmailsString() { return $this->emailsString; }
    public function setEmailsString($emailsString) {
    	$this->emailsString = $emailsString;
    	return $this;
    }

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

    public function getMailingLists() { return $this->mailingLists; }
    public function addMailingList(MailingListInterface $mailingList) {
        $this->mailingLists->add($mailingList);
        return $this;
    }
    public function addMailingLists($mailingLists) {
        if(is_array($mailingLists) || $mailingLists instanceof Traversable || $mailingLists instanceof ArrayCollection)
            foreach ($mailingLists as $mailingList) 
                $this->mailingLists->add($mailingList);
        elseif($mailingLists instanceof MailingListInterface)
            $this->mailingLists->add($mailingLists);
        else 
            throw new \Exception("$mailingLists is expected to be instance of MailingListInterface or Traversable", 1);
        return $this;
    }
    public function removeMailingList(MailingListInterface $mailingList) {
        $this->mailingLists->removeElement($mailingList);
        return $this;
    }
    public function removeMailingLists($mailingLists) {
        foreach ($mailingLists as $mailingList) 
            $this->mailingLists->removeElement($mailingList);
        return $this;
    }
}
	