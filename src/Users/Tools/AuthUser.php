<?php
use Zend\Validator\EmailAddress;
//get the user id, if false return to users/login
$email = $this->getAuthService()
->getStorage()
->read();

//create validation for emaill
$validation = new EmailAddress();
// check empty and verify
if (! $email || !$validation->isValid($email)) {
	return $this->redirect()->toRoute('users/login');
}