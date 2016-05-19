<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class StatusMailing extends Status
{
    /**
     * @var string
     * @ORM\Column(name="email_template", type="string", length=255, nullable=true, unique=false)
     */
    protected $emailTemplate;
    
    /**
     * @var string
     * @ORM\Column(name="parameters_json", type="string", length=255, nullable=true, unique=false)
     */
    protected $parametersJson;
    
    /**
     * @var string
     * @ORM\Column(name="subject", type="string", length=255, nullable=true, unique=false)
     */
    protected $subject;

    /**
     * @var string
     * @ORM\Column(name="mailing_type", type="string", length=255, nullable=true, unique=false)
     */
    protected $mailingType;

    /**
     * @var integer
     * @ORM\Column(name="num_clicks", type="integer", nullable=true)
     */
    protected $numClicks;

    /**
     * @var integer
     * @ORM\Column(name="num_unsubscribe", type="integer", nullable=true)
     */
    protected $numUnsubscribe;

    public function __construct() {
        $this->numClicks = 0;
        $this->numUnsubscribe = 0;
    	parent::__construct();
    }

    public function getEmailTemplate() { return $this->emailTemplate; }
    public function setEmailTemplate($emailTemplate) {
        $this->emailTemplate = $emailTemplate;
        return $this;
    }

    public function getParametersJson() { return $this->parametersJson; }
    public function setParametersJson($parametersJson) {
        $this->parametersJson = $parametersJson;
        return $this;
    }

    public function getSubject() { return $this->subject; }
    public function setSubject($subject) {
    	$this->subject = $subject;
    	return $this;
    }

    public function getMailingType() { return $this->mailingType; }
    public function setMailingType($mailingType) { $this->mailingType = $mailingType; }

    public function getNumClicks() { return $this->numClicks; }
    public function setNumClicks($numClicks) {
        $this->numClicks = $numClicks;
        return $this;
    }

    public function getNumUnsubscribe() { return $this->numUnsubscribe; }
    public function setNumUnsubscribe($numUnsubscribe) {
        $this->numUnsubscribe = $numUnsubscribe;
        return $this;
    }
}
