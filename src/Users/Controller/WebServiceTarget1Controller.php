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
}

?>