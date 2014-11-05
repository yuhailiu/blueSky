<?php
namespace Users\Model;

class TargetMembers
{
    public $id;
    public $target_id;
    public $members_id;
    public $member_status;
    public $last_update_time;
    
	function exchangeArray($data)
	{
	    $this->id	= (isset($data['id'])) ? $data['id'] : null;
	    $this->target_id	= (isset($data['target_id'])) ? $data['target_id'] : null;
		$this->members_id	= (isset($data['members_id'])) ? $data['members_id'] : null;
		$this->member_status	= (isset($data['member_status'])) ? $data['member_status'] : null;
		$this->last_update_time	= (isset($data['last_update_time'])) ? $data['last_update_time'] : null;
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
