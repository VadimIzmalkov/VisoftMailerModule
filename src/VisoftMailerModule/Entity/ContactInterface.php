<?php

namespace VisoftMailerModule\Entity;

interface ContactInterface 
{
	public function getId();

	public function getToken();

	public function getCreatedAt();

	public function setInfo(array $info);
	
	public function getFullName();
	public function setFullname($fullName);

	public function getEmail();
	public function setEmail($email);

	public function getState();
	public function setState($state);

    public function getCreatedBy();
    public function setCreatedBy(\VisoftBaseModule\Entity\UserInterface $registerBy);

	public function getSubscribedOnMailingLists();
	public function addSubscribedOnMailingList($mailingList);
	public function addSubscribedOnMailingLists($mailingLists);
	public function removeSubscribedOnMailingList($mailingList);
	public function removeSubscribedOnMailingLists($mailingLists);

	public function getUnsubscribedFromMailingLists();
	public function addUnsubscribedFromMailingList($mailingList);
	public function addUnsubscribedFromMailingLists($mailingLists);
	public function removeUnsubscribedFromMailingList($mailingList);
	public function removeUnsubscribedFromMailingLists($mailingLists);

	public function removeAllSubscribtions();
}