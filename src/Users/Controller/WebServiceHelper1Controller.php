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
            $flag = "invalidUser";
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        // parse the phoneNumber string
        $addressBookPhoneNumber = $_POST["addressBookPhoneNumber"];
        try {
            $members = $this->parseAddressBookPhoneNumber($addressBookPhoneNumber);
        } catch (\Exception $e) {
            $flag = "failedUploadAddressBook";
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        // update the phoneNumber at before bulid table
        try {
            $this->saveMembers($members);
        } catch (\Exception $e) {
            $flag = "failedUploadAddressBook";
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
        // verify user
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        if ($flag != "validUser") {
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        $user = $this->user;
        if ($user->phoneNumber == '1974071900') {
            // turn on switch
            $action = $_POST["action"];
            if ($action == "matchAddress") {
                try {
                    $this->switchOn($action);
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
                // start push
                ignore_user_abort(); // 关掉浏览器，PHP脚本也可以继续执行.
                set_time_limit(0); // 通过set_time_limit(0)可以让程序无限制的执行下去
                $interval = 5; // 每隔1s运行
                try {
                    $this->repeateMatchAddress($interval);
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
                $flag = "finish match service";
            } else {
                $flag = "what are you going to do?";
            }
        } else {
            $flag = "you are kiding";
        }
        
        // return finish match service
        date_default_timezone_set('UTC');
        return $this->returnJson(array(
            "flag" => $flag,
            "currentTime" => mktime()
        ));
    }

    public function stopCompareAddressBookPhoneNumberAction()
    {
        // verify user
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        if ($flag != "validUser") {
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        $user = $this->user;
        if ($user->phoneNumber == '1974071900') {
            // turn off switch
            $action = $_POST["action"];
            if ($action == "matchAddress") {
                try {
                    $this->switchOff($action);
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
                $flag = "switch match service off";
            } else {
                $flag = "what are you going to do?";
            }
        } else {
            $flag = "you are kiding";
        }
        // return finish match service
        date_default_timezone_set('UTC');
        return $this->returnJson(array(
            "flag" => $flag,
            "currentTime" => mktime()
        ));
    }

    protected function switchOn($action)
    {
        $adapter = $this->getAdapter();
        MyUtils::start($action, $adapter, $this->user);
    }

    protected function switchOff($action)
    {
        $adapter = $this->getAdapter();
        MyUtils::stop($action, $adapter, $this->user);
    }

    protected function repeateMatchAddress($interval)
    {
        do {
            $this->compareAddressBookPhoneNumber();
            sleep($interval);
        } while ($this->isMatchSwithOn());
    }

    protected function isMatchSwithOn()
    {
        $adapter = $this->getAdapter();
        $sql = "SELECT * FROM switch
            where id IN(SELECT MAX(id) FROM switch)
            and action = 'matchAddress'";
        $rows = $adapter->query($sql)->execute();
        $switch = $rows->current()["switch"];
        if ($switch == 'off') {
            $flag = false;
        } else {
            $flag = true;
        }
        return $flag;
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
            $helperPhoneNumber = MyUtils::deletePhoneNumber86($helperPhoneNumber);
            // check phoneNumber status
            try {
                $helper = $this->getUserByPhoneNumberOnly($helperPhoneNumber);
            } catch (\Exception $e) {
                $helper = null;
            }
            // if it's readygo user add it as 1
            $create_time = mktime();
            if ($helper->id > 10) {
                // build as readygo user
                try {
                    $this->buildRelationshipAsReadyGoUser($userId, $helperPhoneNumber,$create_time);
                } catch (\Exception $e) {}
                
                // build equal relationship
                try {
                    $this->buildEqualRelationship($helper->id, $user->phoneNumber, $create_time);
                } catch (\Exception $e) {}
            } else {
                // build as no readygo user
                try {
                    $this->buildRelationshipAsNoReadyGoUser($userId, $helperPhoneNumber,$create_time);
                } catch (\Exception $e) {}
            }
            $this->compareUser = $user;
        }
    }

    protected function buildEqualRelationship($helperId, $phoneNumber, $create_time)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT INTO relationship (owner, helper, status, create_time)
        VALUES ('$helperId', '$phoneNumber', '1', '$create_time')";
        $adapter->query($sql)->execute();
    }

    protected function buildRelationshipAsReadyGoUser($userId, $helperPhoneNumber, $create_time)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT INTO relationship (owner, helper, status, create_time)
        VALUES ('$userId', '$helperPhoneNumber', '1', '$create_time')";
        $adapter->query($sql)->execute();
    }

    protected function buildRelationshipAsNoReadyGoUser($userId, $helperPhoneNumber, $create_time)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT INTO relationship (owner, helper, status, create_time)
        VALUES ('$userId', '$helperPhoneNumber', '4', '$create_time')";
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