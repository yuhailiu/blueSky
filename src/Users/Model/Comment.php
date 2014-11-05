<?php
namespace Users\Model;

class Comment
{
    public $record_id;
    public $target_id;
    public $who;
    public $create_time;
    public $comment;

	function exchangeArray($data)
	{
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
