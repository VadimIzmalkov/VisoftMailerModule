<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class StatusMailerNotification extends Status
{
    /**
     * @var Notification
     * @ORM\OneToOne(targetEntity="VisoftMailerModule\Entity\MailingNotification")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     */
    protected $notification;

    public function __construct() {
    	parent::__construct();
    }

    public function getNotification() { return $this->notification; }
    public function setNotification($notification) {
    	$this->notification = $notification;
    	return $this;
    }
}
