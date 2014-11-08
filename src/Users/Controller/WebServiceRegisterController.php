<?php
namespace Users\Controller;

use Users\Tools\MyUtils;
use Users\Model\User;

class WebServiceRegisterController extends CommController
{

    protected $sessionCode;

    protected function indexAction()
    {
        $result = array(
            'greeting' => 'hello world',
        );
        return $this->returnJson($result);
    }

    public function registerAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $phoneNumber = $_POST['phoneNumber'];
        $phoneNumber = MyUtils::clearNumber($phoneNumber);
        $password = $_POST['password'];
        $areaCode = $_POST["areaCode"];
        $areaCode = str_replace(" ", "", $areaCode);
        $areaCode = str_replace("+", "", $areaCode);
        $deviceCode = $_POST["deviceCode"];
        $deviceCode = str_replace(" ", "", $deviceCode);
        
        $flag = "register";
        if (MyUtils::isValidateTel($phoneNumber) && MyUtils::isValidatePassword($password) && MyUtils::isValidateAreaCode($areaCode)) {
            if (! $this->isRegistedPhoneNumber($phoneNumber, $areaCode)) {
                try {
                    $user = $this->createUser($phoneNumber, $password, $areaCode, $deviceCode);
                    $informRows = $this->informOthers($phoneNumber);
                    // add exist helpers
                    $this->addExistHelpers($user);
                    $flag = "success";
                } catch (\Exception $e) {
                    $flag = "failed";
                }
            } else {
                $flag = "registed";
            }
        } else {
            $flag = "phoneNumberError";
        }
        $result = array(
            "flag" => $flag,
            "sessionCode" => $this->sessionCode,
            "deviceCode" => $deviceCode
        );
        return $this->returnJson($result);
    }

    protected function informOthers($phoneNumber)
    {
        $sql = "UPDATE relationship SET `status` = '1' WHERE helper = '$phoneNumber' ";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }

    protected function addExistHelpers(User $user)
    {
        if ($user->id > 10) {
            // get the helers
            $sql = "SELECT users.phoneNumber from relationship, users
            where helper = '$user->phoneNumber'
            and owner = users.id";
            $adapter = $this->getAdapter();
            $rows = $adapter->query($sql)->execute();
            // add the helpers
            foreach ($rows as $row) {
                $phoneNumber = $row['phoneNumber'];
                $sql = "INSERT INTO relationship (OWNER, helper, status)
            VALUES ('$user->id', '$phoneNumber', '1')";
                try {
                    $adapter->query($sql)->execute();
                } catch (\Exception $e) {
                    // continue the adding process
                }
            }
        }
    }

    protected function isRegistedPhoneNumber($phoneNumber, $areaCode)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        try {
            $user = $userTable->getUserByPhoneNumber($phoneNumber, $areaCode);
            $userPhoneNumber = $user->phoneNumber;
        } catch (\Exception $e) {
            return false;
        }
        if (strlen($userPhoneNumber) > 5) {
            return true;
        } else {
            return false;
        }
    }

    protected function createUser($phoneNumber, $password, $areaCode, $deviceCode)
    {
        $user = new User();
        $user->phoneNumber = $phoneNumber ? $phoneNumber : "";
        $user->setPassword($password);
        $user->areaCode = $areaCode ? $areaCode : "";
        session_start();
        $user->sessionCode = session_id();
        $user->sessionCode = $user->sessionCode . $areaCode . $phoneNumber;
        $user->deviceCode = $deviceCode ? $deviceCode : "";
        
        $sql = "insert into users (phoneNumber, password, sessionCode, areaCode, deviceCode) 
            values ('$phoneNumber', '$user->password', '$user->sessionCode', '$user->areaCode', '$user->deviceCode')";
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $user->id = $rows->getGeneratedValue();
        $this->sessionCode = $user->sessionCode;
        return $user;
    }
}

?>