<?php
$emailConfig = array(
	'Host' => 'smtp.gmail.com',
	'SMTPDebug' => 0,//0 off, 1 command, 2 command and data
	'Port' => '25',
	'SMTPSecure' => 'tls',
	'SMTPAuth' => true,
	'Username' => 'l.yuhai@gmail.com',
	'Password' => 'lyh0313lyc',
	'FromName' => '密码支持服务',
	'Subject' => 'ttruus服务支持',
	'WordWrap' => 80,
	'Body' => '重置密码的验证码为',
	'MySwitch' => 'on', //if off, the function will return true directly
);