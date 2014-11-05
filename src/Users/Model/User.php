<?php
namespace Users\Model;

class User
{
    public $id;
    public $email;
    public $password;
    public $captcha;
    public $failedTimes;
    public $successTimes;
    public $lastSuccesTime;
    public $isClose;
    public $phoneNumber;
    public $areaCode;
    public $sessionCode;
    public $fileName;
    public $thumbnail;
    public $deviceCode;
    public $deviceToken;
    public $notificationNumber;
    

    public function setPassword($clear_password)
    {
        $this->password = md5($clear_password);
    }

	function exchangeArray($data)
	{
		$this->id	= (isset($data['id'])) ? $data['id'] : null;
		$this->email	= (isset($data['email'])) ? $data['email'] : null;
		$this->password	= (isset($data['password'])) ? $data['password'] : null;
		$this->captcha =  (isset($data['captcha'])) ? $data['captcha'] : null;
		$this->failedTimes =  (isset($data['failedTimes'])) ? $data['failedTimes'] : null;
		$this->successTimes =  (isset($data['successTimes'])) ? $data['successTimes'] : null;
		date_default_timezone_set('Asia/Shanghai');
		$currentTime = (string) mktime();
		$this->lastSuccesTime =  (isset($data['lastSuccesTime'])) ? $data['lastSuccesTime'] : $currentTime;
		$this->phoneNumber =  (isset($data['phoneNumber'])) ? $data['phoneNumber'] : null;
		$this->areaCode =  (isset($data['areaCode'])) ? $data['areaCode'] : null;
		$this->isClose =  (isset($data['isClose'])) ? $data['isClose'] : null;
		$this->sessionCode =  (isset($data['sessionCode'])) ? $data['sessionCode'] : null;
		$this->fileName =  (isset($data['fileName'])) ? $data['fileName'] : null;
		$this->thumbnail =  (isset($data['thumbnail'])) ? $data['thumbnail'] : null;
		$this->deviceCode =  (isset($data['deviceCode'])) ? $data['deviceCode'] : null;
		$this->deviceToken =  (isset($data['deviceToken'])) ? $data['deviceToken'] : null;
		$this->notificationNumber =  (isset($data['notificationNumber'])) ? $data['notificationNumber'] : null;
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
