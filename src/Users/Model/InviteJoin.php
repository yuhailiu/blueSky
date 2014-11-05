<?php
namespace Users\Model;

class InviteJoin
{
    public $invite_org_id;
    public $inviter;
    public $create_time;
    public $status;
    public $addition_info;
    
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
