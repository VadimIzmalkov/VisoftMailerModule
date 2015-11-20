<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="visoft_mailer_recipient_states")
 */
class RecipientState
{
	/**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @var Campaign
     * @ORM\OneToOne(targetEntity="VisoftMailerModule\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    protected $campaign;

    /**
     * @var \DateTime
     * @ORM\Column(name="time_sent", type="datetime", nullable=true)
     */
    protected $timeSent;

    public function __construct() {
        $this->timeSent = new \DateTime();
    }

    public function getId() { return $this->id; }
    public function getTimeSent() { return $this->timeSent; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) {
    	$this->email = $email;
    	return $this;
    }

    public function getCampaign() { return $this->campaign; }
    public function setCampaign($campaign) {
    	$this->campaign = $campaign;
    	return $this;
    }
}