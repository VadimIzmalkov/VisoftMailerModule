<?php

namespace VisoftMailerModule\Entity;

interface ContactInterface 
{
	public function getId();

	public function getToken();

	public function getRegisterDate();
	
	public function getFullName();
	public function setFullname($fullName);

	public function getEmail();
	public function setEmail($email);

	public function getState();
	public function setState($state);

    public function getRegisterBy();
    public function setRegisterBy($registerBy);

	public function getSubscribedOnMailingLists();
	public function addSubscribedOnMailingList(MailingListInterface $mailingList);
	public function addSubscribedOnMailingLists($mailingLists);
	public function removeSubscribedOnMailingList(MailingListInterface $mailingList);
	public function removeSubscribedOnMailingLists($mailingLists);

	public function getUnsubscribedFromMailingLists();
	public function addUnsubscribedFromMailingList(MailingListInterface $mailingList);
	public function addUnsubscribedFromMailingLists($mailingLists);
	public function removeUnsubscribedFromMailingList(MailingListInterface $mailingList);
	public function removeUnsubscribedFromMailingLists($mailingLists);

	public function removeAllSubscribtions();
}