<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MailingAnnouncement extends Mailing
{
    /**
     * @var text
     * @ORM\Column(name="recipients", type="text", nullable=true)
     */
    protected $recipients;

    /**
     * @var string
     * @ORM\Column(name="email_template_path", type="string", length=255, nullable=true, unique=false)
     */
    protected $emailTemplatePath;

    /**
     * @var text
     * @ORM\Column(name="email_template_parameters", type="text", nullable=true)
     */
    protected $emailTemplateParameters;

    public function getRecipients() { return $this->recipients; }
    public function setRecipients($recipients) {
    	$this->recipients = $recipients;
    	return $this;
    }

    public function getEmailTemplatePath() { return $this->emailTemplatePath; }
    public function setEmailTemplatePath($emailTemplatePath) {
        $this->emailTemplatePath = $emailTemplatePath;
        return $this;
    }

    public function getEmailTemplateParameters() { return $this->emailTemplateParameters; }
    public function setEmailTemplateParameters($emailTemplateParameters) {
        $this->emailTemplateParameters = $emailTemplateParameters;
        return $this;
    }
}