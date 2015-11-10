<?php

namespace VisoftMailerModule\Entity;

interface ContactInterface 
{
	public function getFullName();
	public function setFullname();
	public function getEmail();
	public function setEmail();
	public function getState();
	public function setState();
}