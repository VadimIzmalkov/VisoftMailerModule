<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface;

/**
 * @ORM\Entity
 */
class StatusMailer extends Status
{
    /**
     * @var string
     * @ORM\Column(name="template_path", type="string", nullable=true)
     */
    protected $templatePath;

    /**
     * @var text
     * @ORM\Column(name="template_parameters", type="text", nullable=true)
     */
    protected $templateParameters;

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

    public function __construct(UserInterface $createdBy, $templatePath, $templateParameters) {
    	parent::__construct($createdBy);
        $this->templatePath = $templatePath;
        $this->templateParameters = json_encode($templateParameters);
    }

    public function getTemplatePath() { return $this->templatePath; }
    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
        return $this;
    }

    public function getTemplateParameters() { return json_decode($this->templateParameters); }
    public function setTemplateParameters($templateParameters) {
        $this->templateParameters = json_encode($templateParameters);
        return $this;
    }

    public function getMailingLists() { return $this->mailingLists; }
    public function addMailingList(MailingListInterface $mailingList) {
        $this->mailingLists->add($mailingList);
        return $this;
    }
    public function addMailingLists($mailingLists) {
        foreach ($mailingLists as $mailingList) $this->mailingLists->add($mailingList);
        return $this;
    }
    public function removeMailingList(MailingListInterface $mailingList) {
        $this->mailingLists->removeElement($mailingList);
        return $this;
    }
    public function removeMailingLists($mailingLists) {
        foreach ($mailingLists as $mailingList) $this->mailingLists->removeElement($mailingList);
        return $this;
    }
}
