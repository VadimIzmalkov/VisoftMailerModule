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
     * @ORM\Column(name="template_path", type="string", length=255, nullable=true, unique=false)
     */
    protected $templatePath;
    /**
     * @var string
     * @ORM\Column(name="template_parameters_json", type="string", length=255, nullable=true, unique=false)
     */
    protected $templateParametersJson;
    /**
     * @var string
     * @ORM\Column(name="subject", type="string", length=255, nullable=true, unique=false)
     */
    protected $subject;

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

    public function getTemplatePath() { return $this->templatePath; }
    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
        return $this;
    }

    public function getTemplateParametersJson() { return $this->templateParametersJson; }
    public function setTemplateParametersJson($templateParametersJson) {
        $this->templateParametersJson = $templateParametersJson;
        return $this;
    }

    public function getMailing() { return $this->mailing; }
    public function setMailing($mailing) {
    	$this->mailing = $mailing;
    	return $this;
    }
}
