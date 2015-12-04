<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

use VisoftBaseModule\Entity\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="visoft_mailer_mailings")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap( {
 *  "mailing" = "Mailing",
 *  "campaign" = "MailingCampaign",
 *  "notification" = "MailingNotification",
 *  "announcement" = "MailingAnnouncement",
 * } )
 */
class Mailing
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
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    protected $token;

    /**
     * @var string
     * @ORM\Column(name="subject", type="string", length=255, nullable=true, unique=false)
     */
    protected $subject;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var UserInterface
     * @ORM\ManyToOne(targetEntity="VisoftBaseModule\Entity\UserInterface")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", nullable=true,  unique=false)
     */
    protected $createdBy;

    public function __construct() {
    	$this->createdAt = new \DateTime();
        $this->token = md5(uniqid(mt_rand(), true));
    }

    public function getId() { return $this->id; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getToken() { return $this->token; }

    public function getSubject() { return $this->subject; }
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function getCreatedBy() { return $this->createdBy; }
    public function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;
        return $this;
    }
}