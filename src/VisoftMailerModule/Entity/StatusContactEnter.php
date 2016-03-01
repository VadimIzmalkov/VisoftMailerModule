<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\ORM\PersistentCollection,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface,
    VisoftMailerModule\Entity\DatabaseInterface;

/**
 * @ORM\Entity
 */
class StatusContactEnter extends Status
{	
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
     * @ORM\ManyToMany(targetEntity="VisoftMailerModule\Entity\DatabaseInterface")
     * @ORM\JoinTable(name="visoft_mailer_statuses_contacts_enter_to_databases",
     * joinColumns={@ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="database_id", referencedColumnName="id", unique=false)}
     * )
     */
    protected $databases;

    public function __construct() {
    	parent::__construct();
    	$this->numContacts = 0;
    	$this->numContactsAdded = 0;
    	$this->numContactsExist = 0;
        $this->databases = new ArrayCollection();
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

    public function getMailingLists() { return $this->databases; }
    public function addMailingList(DatabaseInterface $mailingList) {
        $this->databases->add($mailingList);
        return $this;
    }
    public function addMailingLists($databases) {
        if(is_array($databases) 
            || $databases instanceof Traversable 
            || $databases instanceof ArrayCollection
            || $databases instanceof PersistentCollection)
            foreach ($databases as $mailingList) 
                $this->databases->add($mailingList);
        elseif($databases instanceof DatabaseInterface)
            $this->databases->add($databases);
        else 
            throw new \Exception("mailingLists is expected to be instance of MailingListInterface or Traversable", 1);
        return $this;
    }
    public function removeMailingList(DatabaseInterface $mailingList) {
        $this->databases->removeElement($mailingList);
        return $this;
    }
    public function removeMailingLists($databases) {
        foreach ($databases as $mailingList) 
            $this->databases->removeElement($mailingList);
        return $this;
    }
}
	