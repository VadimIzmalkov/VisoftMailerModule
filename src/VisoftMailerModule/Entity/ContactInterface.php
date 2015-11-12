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
	public function addSubscribedOnMailingList(MailingListInterface $mailingList);
	public function addSubscribedOnMailingLists($mailingLists);
	public function removeSubscribedOnMailingList(MailingListInterface $mailingList);
	public function removeSubscribedOnMailingLists($mailingLists);
	public function removeAllSubscribtions();
	public function getUnsubsribed();
	public function addUnsubsribedFromMailingList(MailingListInterface $mailingList);
	public function addUnsubsribedFromMailingLists($mailingLists);
	public function removeUnsubsribedFromMailingList(MailingListInterface $mailingList);
	public function removeUnsubsribedFromMailingLists($mailingLists);
}