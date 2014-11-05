<?php
namespace Users\Model;

class RequestJoin
{
//     public $id;
    public $requester;
    public $request_org_id;
    public $create_time;
    public $status;
    public $addition_info;
    
    
  
	function exchangeArray($data)
	{
// 		$this->id		= (isset($data['id'])) ? $data['id'] : null;
		$this->requester		= (isset($data['requester'])) ? $data['requester'] : null;
		$this->request_org_id		= (isset($data['request_org_id'])) ? $data['request_org_id'] : null;
		$this->create_time		= null;
		$this->status		= (isset($data['status'])) ? $data['status'] : 0;
		$this->addition_info		= (isset($data['addition_info'])) ? $data['addition_info'] : null;
	}

	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
