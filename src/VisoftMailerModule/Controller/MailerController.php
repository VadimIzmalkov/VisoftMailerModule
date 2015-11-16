<?php

namespace VisoftMailerModule\Controller;

use VisoftBaseModule\Controller\BaseController,
	VisoftMailerModule\Entity\MailingListInterface;

class MailerController extends BaseController
{
	protected $entityManager;

	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
}