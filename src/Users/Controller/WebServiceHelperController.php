<?php
namespace Users\Controller;

use Users\Tools\MyUtils;
use Users\Model\User;

class WebServiceHelperController extends CommController
{

    protected $user;

    protected function index()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $result = array(
            "flag" => "WebServiceHelperController"
        );
        return $this->returnJson($result);
    }

    protected function isAuthrizedUser($areaCode, $phoneNumber, $password)
    {
        $user = new User();
        try {
            $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
        } catch (\Exception $e) {
            return false;
        }
        if ($user->password == md5($password)) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $phoneNumber            
     * @return User
     */
    protected function getUserByPhoneNumber($phoneNumber, $areaCode)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserByPhoneNumber($phoneNumber, $areaCode);
        return $user;
    }

    public function isUserOfReadyGoAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "isUserOfReadygo";
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $checkPhoneNumber = $_POST["checkPhoneNumber"];
        $checkPhoneNumber = MyUtils::clearNumber($checkPhoneNumber);
        if (MyUtils::isValidateTel($checkPhoneNumber)) {
            try {
                $user = $this->getUserByPhoneNumberOnly($checkPhoneNumber);
                if ($user->id) {
                    $flag = "readyGoUser";
                }
            } catch (\Exception $e) {
                $flag = "notReadyGoUser";
            }
        } else {
            $flag = "checkPhoneNumberError";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    public function getHelpersByPhoneNumberAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "getHelperByPhoneNumber";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $status = $_POST["status"];
        $lastGetTime = $_POST["lastGetTime"];
        $lastGetTime = $lastGetTime ? $lastGetTime : 0;
        try {
//             $helpers = $this->getHelpersByOwnerId($this->user->id, $status);
            $helpers = $this->newGetHelpersByOwnerId($this->user->id, $status, $lastGetTime);
            $flag = "successGetHelpers";
        } catch (\Exception $e) {
            $flag = "failedGetHelper";
        }
        
        $result = array(
            "flag" => $flag,
            "helpers" => $helpers,
            "ownerId" => $this->user->id ? $this->user->id : "noSuchUser"
        );
        return $this->returnJson($result);
    }

    protected function getHelpersByOwnerId($ownerId, $status)
    {
        if ($status) {
            if (MyUtils::isValidateStatus($status)) {
                $sql = "select * from relationship WHERE
                owner = '$ownerId' and `status` = '$status'
                ORDER BY create_time DESC";
            } else {
                $sql = null;
            }
        } else {
            $sql = "select * from relationship WHERE
                owner = '$ownerId' and `status` <> '3' 
                ORDER BY create_time DESC";
        }
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        // push the result to a helpers array
        $helpers = array();
        foreach ($rows as $row) {
            array_push($helpers, $row);
        }
        return $helpers;
    }
    
    protected function newGetHelpersByOwnerId($ownerId, $status, $lastGetTime)
    {
        if ($status) {
            if (MyUtils::isValidateStatus($status)) {
                $sql = "select * from relationship WHERE
                owner = '$ownerId' and `status` = '$status' 
                and create_time > '$lastGetTime'
                ORDER BY create_time DESC";
            } else {
                $sql = null;
            }
        } else {
            $sql = "select * from relationship WHERE
                owner = '$ownerId' and status <> '4'
                and create_time > '$lastGetTime'
                ORDER BY create_time DESC";
        }
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        // push the result to a helpers array
        $helpers = array();
        foreach ($rows as $row) {
            array_push($helpers, $row);
        }
        return $helpers;
    }

//     /**
//      *
//      * @param string $ownerId            
//      * @param string $status            
//      * @return helpers helpers PhoneNumber and photoName
//      */
//     protected function NewGetHelpersByOwnerId($ownerId, $status)
//     {
//         if ($status) {
//             if (MyUtils::isValidateStatus($status)) {
//                 $sql = "select `owner`, helper, create_time, `status`,helper_areaCode, users.fileName, users.thumbnail from relationship, users
//                     WHERE
//                     owner = '$ownerId' and `status` ='$status'
//                     and relationship.helper = users.phoneNumber
//                     ORDER BY create_time DESC";
//             } else {
//                 $sql = null;
//             }
//         } else {
//             $sql = "select `owner`, helper, create_time, `status`,helper_areaCode, users.fileName, users.thumbnail from relationship, users
//                 WHERE
//                 owner = '$ownerId' and `status` <> 3
//                 and relationship.helper = users.phoneNumber
//                 ORDER BY create_time DESC";
//         }
        
//         $adapter = $this->getAdapter();
//         $rows = $adapter->query($sql)->execute();
//         // push the result to a helpers array
//         $helpers = array();
//         foreach ($rows as $row) {
//             array_push($helpers, $row);
//         }
//         return $helpers;
//     }

    /**
     *
     * @param unknown $targets            
     * @return multitype:
     */
    protected function removeDuplicateHelper($helpers)
    {
        $array = array();
        foreach ($helpers as $helper) {
            $flag = "NO";
            if (count($array) == 0) {
                $flag = "YES";
            }
            foreach ($array as $item) {
                if ($helper['helper'] != $item['helper']) {
                    $flag = "YES";
                } else {
                    $flag = "NO";
                    break;
                }
            }
            if ($flag == "YES") {
                array_push($array, $helper);
            }
        }
        return $array;
    }

    public function removeHelperByPhoneNumberAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "removeHelperByPhoneNumber";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $helperAreaCode = $_POST["helperAreaCode"];
        if (strlen($helperAreaCode) == 0) {
            $helperAreaCode = "";
        } else {
            $helperAreaCode = str_replace(" ", "", $helperAreaCode);
        }
        
        $helperPhoneNumber = $_POST["helperPhoneNumber"];
        $helperPhoneNumber = str_replace(" ", "", $helperPhoneNumber);
        $helperPhoneNumber = MyUtils::deletePhoneNumber86($helperPhoneNumber);
        if (MyUtils::isValidateTel($helperPhoneNumber)) {
            try {
                $rows = $this->removerHelperByPhoneNumber($helperAreaCode, $helperPhoneNumber);
                if ($rows > 0) {
                    $flag = "successRemoveHelper";
                } else {
                    $flag = "noRecordBeRemoved";
                }
            } catch (\Exception $e) {
                $flag = "failedRemoveHelper";
            }
        } else {
            $flag = "invalidHelperPhoneNumber";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    protected function removerHelperByPhoneNumber($helperAreaCode, $helperPhoneNumber)
    {
        $user = new User();
        $user = $this->user;
        if (strlen($helperAreaCode) > 0) {
            $sql = "DELETE FROM relationship WHERE 
    	   owner='$user->id' and helper='$helperPhoneNumber' and helper_areaCode = '$helperAreaCode'";
        } else {
            $sql = "DELETE FROM relationship WHERE
    	    owner='$user->id' and helper='$helperPhoneNumber'";
        }
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }

    public function buildRelationshipAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "buildRelationship";
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        $helperPhoneNumber = $_POST["helperPhoneNumber"];
        $helperPhoneNumber = MyUtils::clearNumber($helperPhoneNumber);
        $helperPhoneNumber = MyUtils::deletePhoneNumber86($helperPhoneNumber);
        if (MyUtils::isValidateTel($helperPhoneNumber)) {
            try {
                if ($helperPhoneNumber == $this->user->phoneNumber) {
                    throw new \Exception("noPermissionAddSelf");
                }
                $result = $this->buildRelatinship($helperPhoneNumber);
                if ($result == "blockByHelper") {
                    $flag = "blockByHelper";
                } else {
                    $flag = "successBuildRelationship";
                }
            } catch (\Exception $e) {
                $flag = "faildedBuildRelationship";
            }
        } else {
            $flag = "helperPhoneNumberError";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    protected function buildRelatinship($helperPhoneNumber)
    {
        $user = $this->user;
        $sql = "SELECT * from users WHERE phoneNumber = '$helperPhoneNumber'";
        
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        $helper = $rows->current();
        $helperId = $helper["id"];
        if ($helperId > 10) {
            $status = "1";
            // check haveBlockByHelper
            $sql = "SELECT relationship.`status` from relationship
                WHERE OWNER = '$helper[id]' and helper = '$user->phoneNumber'";
            $rows = $adapter->query($sql)->execute();
            $blockStatus = $rows->current();
            if ($blockStatus["status"] == 3) {
                return "blockByHelper";
            }
        } else {
            $status = "2";
        }
        
        $user = new User();
        $create_time = mktime();
        $user = $this->user;
        $sql = "INSERT INTO relationship (owner, helper, status, create_time) 
            VALUES ('$user->id', '$helperPhoneNumber', '$status', '$create_time')";
        $rows = $adapter->query($sql)->execute();
        // if it's an exsite user, add helper automaticatly
        if ($helperId > 10) {
            try {
                $this->buildEqualRelationship($adapter, $helperId, $user->phoneNumber, $create_time);
            } catch (\Exception $e) {
                // has builded
            }
        }
        return $rows->getAffectedRows();
    }

    protected function buildEqualRelationship($adapter, $helperId, $phoneNumber, $create_time)
    {
        $sql = "INSERT INTO relationship (owner, helper, status, create_time)
        VALUES ('$helperId', '$phoneNumber', '1', '$create_time')";
        $adapter->query($sql)->execute();
    }

    public function blockContactByPhoneNumberAction()
    {
        $flag = "blockContactByPhoneNumner";
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $blockPhoneNumber = $_POST["blockPhoneNumber"];
        $blockPhoneNumber = MyUtils::clearNumber($blockPhoneNumber);
        $blockPhoneNumber = MyUtils::deletePhoneNumber86($blockPhoneNumber);
        if (MyUtils::isValidateTel($blockPhoneNumber)) {
            $action = $_POST["action"];
            if ($action == "releaseBlock") {
                try {
                    $affectedRows = $this->releaseBlockByPhoneNumber($blockPhoneNumber);
                    $flag = "successReleaseBlock";
                } catch (\Exception $e) {
                    $flag = "failedReleaseBlock";
                }
            } elseif ($action == "block") {
                try {
                    $affectedRows = $this->blockContactByPhoneNumber($blockPhoneNumber);
                    $flag = "successBlockPhoneNumber";
                } catch (\Exception $e) {
                    $flag = "failedBlockPhoneNumber";
                }
            } else {
                $flag = "invalidAction";
            }
        } else {
            $flag = "invalidPhoneNumber";
        }
        
        $result = array(
            "flag" => $flag,
            "affectedRows" => $affectedRows
        );
        return $this->returnJson($result);
    }

    protected function blockContactByPhoneNumber($phoneNumber)
    {
        $user = $this->user;
        // get relationship status
        $sql = "SELECT relationship.`status` from relationship 
            where `owner`= '$user->id' and helper = '$phoneNumber'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $status = $rows->current();
        $create_time = mktime();
        if ($status == 3) {
            // return 0;
            return 0;
        } else 
            if ($status > 0) {
                // update status to 3
                $sql = "UPDATE relationship SET relationship.`status` = '3', create_time = '$create_time'
                    where `owner` = '$user->id' and helper = '$phoneNumber'";
                $rows = $adapter->query($sql)->execute();
            } else {
                // create new status
                $sql = "INSERT INTO relationship (owner, helper, status, create_time)
                    VALUES ('$user->id', '$phoneNumber', '3', '$create_time')";
                $rows = $adapter->query($sql)->execute();
            }
        return $rows->getAffectedRows();
    }

    protected function releaseBlockByPhoneNumber($phoneNumber)
    {
        $user = $this->user;
        $sql = "DELETE from relationship
            WHERE OWNER= '$user->id' and helper ='$phoneNumber'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }
}

?>