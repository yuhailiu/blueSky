<?php
namespace Users\Model;

class Comment
{

    public $id;

    public $comment;

    public $file_name;

    public $target_id;

    public $create_time;

    public $create_user;

    function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->comment = (isset($data['comment'])) ? $data['comment'] : null;
        $this->file_name = (isset($data['file_name'])) ? $data['file_name'] : null;
        $this->target_id = (isset($data['target_id'])) ? $data['target_id'] : null;
        $this->create_time = (isset($data['create_time'])) ? $data['create_time'] : null;
        $this->create_user = (isset($data['create_user'])) ? $data['create_user'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
