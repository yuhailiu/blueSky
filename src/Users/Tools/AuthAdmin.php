<?php
//get the user id, if false return to users/login
$email = $this->getAuthService()
->getStorage()
->read();

// check the loging users
if ($email != "l.yuhai@gmail.com") {
	return $this->redirect()->toRoute('users/login');
}