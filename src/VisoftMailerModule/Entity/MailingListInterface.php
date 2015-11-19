<?php

namespace VisoftMailerModule\Entity;

interface MailingListInterface
{
	public function getId();
	
	public function getName();
	public function setName($name);
}