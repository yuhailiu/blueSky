<?php
namespace Users\Controller;

use Users\Tools\MyUtils;

class WebServiceTarget1Controller extends WebServiceTargetController
{

    public function getTargetsAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "getTargets";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $target_id = $_POST["target_id"];
        $target_id = str_replace(" ", "", $target_id);
        if ($target_id > 10) {
            try {
                $targets = $this->newGetTargetStatusById($target_id);
                $flag = "successGetMemberStatus";
            } catch (\Exception $e) {
                $flag = "failedGetTargets";
            }
        } else {
            $target_status = $_POST["target_status"];
            try {
                $targetsByCreater = $this->getTargetsByCreaterWithPhoneNumber($target_status, $target_id);
                $targetsByReceiver = $this->getTargetsByReceiverWithPhoneNumber($target_status, $target_id);
                $targets = array_merge_recursive($targetsByCreater, $targetsByReceiver);
                sort($targets);
                $flag = "successGetTargets";
            } catch (\Exception $e) {
                $flag = "failedGetTargets";
            }
            $targets = $this->removeDuplicateTargets($targets);
        }
        
        // $targets = "";
        $result = array(
            "flag" => $flag,
            "targets" => $targets
        );
        return $this->returnJson($result);
    }

    protected function newGetTargetStatusById($target_id)
    {
        $user = $this->user;
        $target = $this->getTargetById($target_id);
        if ($target->target_creater == $user->id) {
            // getStatus as creater
            $targets = $this->getTargetStatusById($target_id);
        } else {
            // get status by receiver self
            $targets = $this->getTargetStatusByIdForReceiver($target_id);
        }
        return $targets;
    }

    public function deleteHelpersFromTargetAction()
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
        $target_id = $_POST["target_id"];
        $members = $_POST["members"];
        
        try {
            $this->deleteMembersFromTarget($target_id, $members);
            $flag = "successDeleteHelpers";
        } catch (\Exception $e) {
            $flag = "failedDeleteMembers";
//             throw new \Exception($e);
        }
        
        return $this->returnJson(array(
            "flag" => $flag
        ));
    }

    protected function deleteMembersFromTarget($target_id, $members)
    {
        if (strlen($members) < 5) {
            throw new \Exception("noMembers");
        }
        if ($target_id < 1) {
            throw new \Exception("noTargetId");
        }
        $user = $this->user;
        // does the user has right
        if ($this->isUserOfTargetCreater($target_id, $user->id)) {
            // get and parse helper phoneNumbers
            $members = $this->parseAddressBookPhoneNumber($members);
            // delete the helpers
            foreach ($members as $memberPhoneNumber) {
                try {
                    $this->deleteMemberFromTarget($target_id, $memberPhoneNumber);
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
            }
        } else {
            throw new \Exception("noPermition");
        }
    }

    protected function deleteMemberFromTarget($target_id, $memberPhoneNumber)
    {
        // delete if it's not an agreed member
        $adapter = $this->getAdapter();
        $sql = "DELETE from targetMembers
    	       where targetMembers.target_id = '$target_id' and member_status <> 'agree' and targetMembers.members_id =
    	       (SELECT id from users where phoneNumber = '$memberPhoneNumber')";
        $adapter->query($sql)->execute();
    }
}

?>