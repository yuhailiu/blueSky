<?php
/**
 * get address book phoneNumber and compare it with database
 * 
 * 
 * @author liuyuhai
 *
 */
namespace Users\Controller;

use Users\Controller\WebServiceHelperController;
use Users\Tools\MyUtils;

class WebServiceHelper1Controller extends WebServiceHelperController
{

    protected $compareUser;

    public function indexAction()
    {
        $flag = "sendAddressPhoneBookAction";
        return $this->returnJson(array(
            "flag" => $flag
        ));
    }

    public function uploadAddressPhoneNumberAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $sessionCode = $_POST["sessionCode"];
        $phoneNumber = $_POST["phoneNumber"];
        try {
            $this->getUserBySessionCode($sessionCode, $phoneNumber);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        // parse the phoneNumber string
        $addressBookPhoneNumber = $_POST["addressBookPhoneNumber"];
        try {
            $members = $this->parseAddressBookPhoneNumber($addressBookPhoneNumber);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        // update the phoneNumber at before bulid table
        try {
            $this->saveMembers($members);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        // return successUpload
        return $this->returnJson(array(
            "flag" => "successUploadAddressBook",
            "currentTime" => mktime()
        ));
    }

    protected function parseAddressBookPhoneNumber($addressBookPhoneNumber)
    {
        if (strlen($addressBookPhoneNumber) > 5) {
            $members = MyUtils::changeStringtoArray($addressBookPhoneNumber);
        } else {
            throw new \Exception("addressBookPhoneNumberError");
        }
        return $members;
    }

    protected function saveMembers($members)
    {
        $user = $this->user;
        $adapter = $this->getAdapter();
        foreach ($members as $member) {
            try {
                $sql = "INSERT beforeBuildRelationship (phoneNumber_addressbook, user_id, status)
                    VALUES('$member', '$user->id', 'upload')";
                $adapter->query($sql)->execute();
            } catch (\Exception $e) {}
        }
    }

    public function startCompareAddressBookPhoneNumberAction()
    {
        $this->compareAddressBookPhoneNumber();
        // return successUpload
        date_default_timezone_set('UTC');
        return $this->returnJson(array(
            "flag" => "successCompareAddressBook",
            "currentTime" => mktime()
        ));
    }
    
    

    protected function compareAddressBookPhoneNumber()
    {
        // get upload phoneNumbers
        try {
            $userAndphoneNumbers = $this->getUploadPhoneNumbers();
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        // build relationship
        try {
            $this->buildRelationshipWithPhones($userAndphoneNumbers);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        
        // update the status to added
        try {
            $this->updateAddedStatus($userAndphoneNumbers);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    protected function getUploadPhoneNumbers()
    {
        $sql = "SELECT * from beforeBuildRelationship
            where status = 'upload' ORDER BY user_id";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $array = array();
        foreach ($rows as $row) {
            array_push($array, $row);
        }
        return $array;
    }

    protected function buildRelationshipWithPhones($userAndphoneNumbers)
    {
        foreach ($userAndphoneNumbers as $userAndphoneNumber) {
            // get user by id, if repeate user keep the user by compareUser
            $userId = $userAndphoneNumber["user_id"];
            if ($this->compareUser->id == $userId) {
                $user = $this->compareUser;
            } else {
                $user = $this->getUserById($userId);
            }
            
            $helperPhoneNumber = $userAndphoneNumber["phoneNumber_addressbook"];
            // check phoneNumber status
            try {
                $helper = $this->getUserByPhoneNumberOnly($helperPhoneNumber);
            } catch (\Exception $e) {}
            
            // if it's readygo user add it as 1
            if ($helper->id > 10) {
                // build as readygo user
                try {
                    $this->buildRelationshipAsReadyGoUser($userId, $helperPhoneNumber);
                } catch (\Exception $e) {}
                
                // build equal relationship
                try {
                    $this->buildEqualRelationship($helper->id, $user->phoneNumber);
                } catch (\Exception $e) {}
            } else {
                // build as no readygo user
                try {
                    $this->buildRelationshipAsNoReadyGoUser($userId, $helperPhoneNumber);
                } catch (\Exception $e) {}
            }
            $this->compareUser = $user;
        }
    }

    protected function buildEqualRelationship($helperId, $phoneNumber)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT INTO relationship (owner, helper, status)
        VALUES ('$helperId', '$phoneNumber', '1')";
        $adapter->query($sql)->execute();
    }

    protected function buildRelationshipAsReadyGoUser($userId, $helperPhoneNumber)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT INTO relationship (owner, helper, status)
        VALUES ('$userId', '$helperPhoneNumber', '1')";
        $adapter->query($sql)->execute();
    }

    protected function buildRelationshipAsNoReadyGoUser($userId, $helperPhoneNumber)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT INTO relationship (owner, helper, status)
        VALUES ('$userId', '$helperPhoneNumber', '2')";
        $adapter->query($sql)->execute();
    }

    protected function updateAddedStatus($userAndPhoneNumbers)
    {
        foreach ($userAndPhoneNumbers as $userAndPhoneNumber) {
            try {
                $this->updateAddedStatusById($userAndPhoneNumber);
            } catch (\Exception $e) {}
        }
    }

    protected function updateAddedStatusById($userAndPhoneNumber)
    {
        $id = $userAndPhoneNumber["id"];
        $adapter = $this->getAdapter();
        $sql = "UPDATE beforeBuildRelationship SET `status`= 'added'
            where id = '$id'";
        $adapter->query($sql)->execute();
    }
}

?>