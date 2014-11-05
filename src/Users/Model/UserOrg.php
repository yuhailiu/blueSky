<?php
namespace Users\Model;

class UserOrg
{
    public $user_email;
    public $org_id;
    public $create_time;

	function exchangeArray($data)
	{
		$this->user_email	= (isset($data['user_email'])) ? $data['user_email'] : null;
		$this->org_id	= (isset($data['org_id'])) ? $data['org_id'] : null;
		$this->create_time =  (isset($data['create_time'])) ? $data['create_time'] : null;
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
