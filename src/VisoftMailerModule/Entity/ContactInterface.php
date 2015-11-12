<?php

namespace VisoftMailerModule\Entity;

interface ContactInterface 
{
	public function getFullName();
	public function setFullname($fullName);
	public function getEmail();
	public function setEmail($email);
	public function getState();
	public function setState($state);
	public function getSubscribed();
	public function addSubscribedSingle(MailingListInterface $mailingList);
	public function addSubscribedPlural($mailingLists);
	public function removeSubscribedSingle(MailingListInterface $mailingList);
	public function removeSubscribedPlural($mailingLists);
	public function removeAllSubscribtions();
	public function getUnsubsribed();
	public function addUnsubsribedSingle(MailingListInterface $mailingList);
	public function addUnsubsribedPlural($mailingLists);
	public function removeUnsubsribedSingle(MailingListInterface $mailingList);
	public function removeUnsubsribedPlural($mailingLists);
}