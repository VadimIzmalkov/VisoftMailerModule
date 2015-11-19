<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="VisoftMailerModule\Entity\Repository\CampaignRepository")
 * @ORM\Table(name="visoft_mailer_campaigns")
 */
class Campaign
{
	/**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true, unique=false)
     */
    protected $name;

    /**
     * @var EmailTemplateInterface
     * @ORM\ManyToOne(targetEntity="VisoftMailerModule\Entity\EmailTemplateInterface")
     * @ORM\JoinColumn(name="email_template_id", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $emailTemplate;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="VisoftMailerModule\Entity\MailingListInterface")
     * @ORM\JoinTable(name="visoft_mailer_statuses_mailer_to_mailing_lists",
     * joinColumns={@ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="mailing_list_id", referencedColumnName="id", unique=false)}
     * )
     */
    protected $mailingLists;

    /**
     * @var integer
     * @ORM\Column(name="clicks_total", type="integer", nullable=true)
     */
    protected $numClicksTotal;

    /**
     * @var integer
     * @ORM\Column(name="unsubscribe_number", type="integer", nullable=true)
     */
    protected $numUnsubscribe;

    /**
     * @var integer
     * @ORM\Column(name="recipients_total", type="integer", nullable=true)
     */
    protected $numRecipientsTotal;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    public function __construct() {
        $this->numUnsubscribe = 0;
        $this->numClicksTotal = 0;
        $this->numRecipientsTotal = 0;
    	$this->createdAt = new \DateTime();
    	$this->mailingLists = new ArrayCollection();
    }

    public function getId() { return $this->id; }

    public function getName() { return $this->name; }
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getEmailTemplate() { return $this->emailTemplate; }
    public function setEmailTemplate($emailTemplate) {
        $this->emailTemplate = $emailTemplate;
        return $this;
    }

    public function getMailingLists() { return $this->mailingLists; }
    public function addMailingList(MailingListInterface $mailingList) {
        $this->mailingLists->add($mailingList);
        return $this;
    }
    public function addMailingLists($mailingLists) {
        if(is_array($mailingLists) || $mailingLists instanceof Traversable)
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