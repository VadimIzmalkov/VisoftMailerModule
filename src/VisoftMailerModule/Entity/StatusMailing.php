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
     * @var Notification
     * @ORM\OneToOne(targetEntity="VisoftMailerModule\Entity\MailingInterface")
     * @ORM\JoinColumn(name="mailing_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $mailing;

    public function __construct() {
    	parent::__construct();
    }

    public function getMailing() { return $this->mailing; }
    public function setMailing($mailing) {
    	$this->mailing = $mailing;
    	return $this;
    }
}
