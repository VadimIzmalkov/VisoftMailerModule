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
     * @var Campaign
     * @ORM\OneToOne(targetEntity="VisoftMailerModule\Entity\Campaign")
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
