<?php
namespace Users\Model;

class PushStack
{

    public $id;

    public $deviceToken;

    public $message;

    public $target_id;

    public $status;

    public $notificationNumber;

    public $userStatus;

    function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->deviceToken = (isset($data['deviceToken'])) ? $data['deviceToken'] : null;
        $this->message = (isset($data['message'])) ? $data['message'] : null;
        $this->target_id = (isset($data['target_id'])) ? $data['target_id'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->notificationNumber = (isset($data['notificationNumber'])) ? $data['notificationNumber'] : null;
        $this->userStatus = (isset($data['userStatus'])) ? $data['userStatus'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
