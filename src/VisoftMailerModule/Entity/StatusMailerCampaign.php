<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface;

/**
 * @ORM\Entity
 */
class StatusMailerCampaign extends Status
{
    /**
     * @var MailingCampaign
     * @ORM\OneToOne(targetEntity="VisoftMailerModule\Entity\MailingCampaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    protected $campaign;

    public function __construct() {
    	parent::__construct();
    }

    public function getCampaign() { return $this->campaign; }
    public function setCampaign($campaign) {
    	$this->campaign = $campaign;
    	return $this;
    }
}
