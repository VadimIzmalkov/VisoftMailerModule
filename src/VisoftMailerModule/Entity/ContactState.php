<?php

namespace VisoftMailerModule\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="visoft_mailer_contact_states")
 */
class ContactState
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
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;

    public function getId() { return $this->id; }

    public function getName() { return $this->name; }
    public function setName($name) {
    	$this->name = $name;
    	return $this;
    }
}