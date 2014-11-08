<?php
namespace Users\Controller;

use Users\Controller\CommController;
use Users\Tools\MyUtils;
use Users\Model\User;
use Users\Model\Transaction;
use Users\Model\CostCenter;

class WebServiceTransactionController extends CommController
{

    protected $user;

    protected $transaction;

    protected function indexAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $result = array(
            "flag" => "WebServiceTransactionController"
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
        if (strlen($areaCode) > 0) {
            $user = $userTable->getUserByPhoneNumber($phoneNumber, $areaCode);
        } else {
            $user = $userTable->NewGetUserByPhoneNumber($phoneNumber);
        }
        return $user;
    }

    public function getTransactionsByUserAction()
    {
        $flag = "getTransactions";
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        
        $costCenterId = $_POST["costCenterId"];
        
        try {
            $transactions = $this->getTransactionsByUser($costCenterId);
            $flag = "successGetTransactions";
        } catch (\Exception $e) {
            $flag = "failedGetTransactions";
        }
        
        $result = array(
            "flag" => $flag,
            "transactions" => $transactions,
            "costCenterId" => $costCenterId
        );
        return $this->returnJson($result);
    }

    protected function getTransactionsByUser($costCenterId)
    {
        $sql = "SELECT cost_record.id, cost_record.record_time, cost_record.note, cost_record.`transaction`,  
            cost_record.transaction_time, cost_record.costCenterId, users.phoneNumber as creater
            from cost_record, users where costCenterId = '$costCenterId' and cost_record.creater = users.id
            ORDER BY cost_record.transaction_time";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $objs = array();
        foreach ($rows as $row) {
            array_push($objs, $row);
        }
        
        return $objs;
    }

    public function createTransactionAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "createTransaction";
        
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        
        if ($flag == "invalidUser") {
        	return $this->returnJson(array(
        		"flag" => $flag,
        	));
        }
        $this->transaction = new Transaction();
        $this->transaction->transaction = $_POST["transaction"];
        $this->transaction->note = $_POST["note"];
        $this->transaction->record_time = $_POST["record_time"];
        $this->transaction->transaction_time = $_POST["transaction_time"];
        $this->transaction->creater = $this->user->id;
        $this->transaction->costCenterId = $_POST["costCenterId"];
        
        try {
            $sql = $this->createTransaction();
            $flag = "successCreateTransaction";
        } catch (\Exception $e) {
            $flag = "failedCreateTransactions";
        }
        
        $result = array(
            "flag" => $flag,
            "transactions" => $this->transaction,
            "sql" => $sql
        );
        return $this->returnJson($result);
    }

    protected function createTransaction()
    {
        $transaction = $this->transaction;
        $sql = "INSERT cost_record (cost_record.creater, cost_record.note, cost_record.record_time, 
        cost_record.transaction, cost_record.transaction_time,cost_record.costCenterId)
        VALUES('$transaction->creater', '$transaction->note', '$transaction->record_time', 
        '$transaction->transaction', '$transaction->transaction_time', '$transaction->costCenterId' )";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }

    public function createCostCenterAction()
    {
        $flag = "createCostCenter";
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        
        $costCenter = new CostCenter();
        $costCenter->centerName = $_POST["centerName"];
        $costCenter->budget = $_POST["budget"];
        $costCenter->creater = $this->user->id;
        $costCenter->customerCenterCode = $_POST["customerCode"];
        $costCenter->createTime = $_POST["create_time"];
        $membersString = $_POST["members"];
        
        $members = array();
        foreach (explode(',', $membersString) as $member) {
            array_push($members, $member);
        }
        
        try {
            $createResult = $this->createCostCenter($costCenter, $members);
            $flag = "successCreateCostCenter";
        } catch (\Exception $e) {
            $flag = "failedCreateCostCenter";
        }
        
        $result = array(
            "flag" => $flag,
            "costCenter" => $costCenter,
            "costCenterId" => $createResult
        );
        return $this->returnJson($result);
    }

    protected function createCostCenter(CostCenter $costCenter, $members)
    {
        $sql = "INSERT costCenter (costCenter.budget, costCenter.centerName, costCenter.createTime, costCenter.creater, costCenter.customerCenterCode)
            VALUES('$costCenter->budget', '$costCenter->centerName', '$costCenter->createTime', '$costCenter->creater', '$costCenter->customerCenterCode')";
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $costCenterId = $rows->getGeneratedValue();
        if ($costCenterId > 0) {
            foreach ($members as $member) {
                try {
                    $user = $this->getUserByPhoneNumber($member, "");
                } catch (\Exception $e) {
                    $user = null;
                }
                if ($user->id > 0) {
                    $sql = "INSERT costCenterMembers (costCenterMembers.costCenterId, costCenterMembers.memberId)
                        VALUES ('$costCenterId', '$user->id')";
                    $rows = $adapter->query($sql)->execute();
                }
            }
        }
        return $costCenterId;
    }

    public function getCostCentersAction()
    {
        $flag = "getCostCenter";
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        
        try {
            $costCenters = $this->getCostCentersByUser();
            $flag = "successGetCostCenters";
        } catch (\Exception $e) {
            $flag = "failedGetCostCenters";
        }
        
        $result = array(
            "flag" => $flag,
            "costCenters" => $costCenters
        );
        return $this->returnJson($result);
    }

    protected function getCostCentersByUser()
    {
        $user = $this->user;
        $sql = "SELECT costCenter.budget, costCenter.centerName, costCenter.createTime, costCenter.customerCenterCode, costCenter.id
                ,users.phoneNumber as creater from costCenter , users WHERE (creater = '$user->id'
                or costCenter.id IN (
                SELECT costCenterId from costCenterMembers WHERE costCenterMembers.memberId = '$user->id'
                ) )
                AND costCenter.creater = users.id
                ORDER BY createTime";
        
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        $objs = array();
        foreach ($rows as $row) {
            $costCenterId = $row["id"];
            $sql = "SELECT users.phoneNumber as memberId from costCenterMembers
                    JOIN users on costCenterMembers.memberId = users.id
                    WHERE costCenterId = '$costCenterId'";
            $members = $adapter->query($sql)->execute();
            $costCenterMembers = "";
            foreach ($members as $member){
                $costCenterMembers = $costCenterMembers.$member["memberId"].",";
            }
            $row["members"] = $costCenterMembers;
            array_push($objs, $row);
        }
        
        return $objs;
    }

    public function addMembersAtCostCenterAction()
    {
        $flag = "addMembersAtCostCenter";
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        
        $costCenterId = $_POST["costCenterId"];
        
        $membersString = $_POST["members"];
        $members = array();
        foreach (explode(',', $membersString) as $member) {
            array_push($members, $member);
        }
        
        if ($costCenterId > 0) {
            try {
                $affectRowsNumber = $this->addMembersAtCostCenter($costCenterId, $members);
                $flag = "successAddMembers";
            } catch (\Exception $e) {
                $flag = "failedAddMembers";
            }
        }else {
            $flag = "costCenterErro";
        }
        
        $result = array(
            "flag" => $flag,
            "affectRowsNumber" => $affectRowsNumber,
        );
        return $this->returnJson($result);
    }

    protected function addMembersAtCostCenter($costCenterId, $members)
    {
        $adapter = $this->getAdapter();
        $affectRows = 0;
        if ($costCenterId > 0) {
            foreach ($members as $member) {
                try {
                    $user = $this->getUserByPhoneNumber($member, "");
                } catch (\Exception $e) {
                    $user = null;
                }
                // save user to cost member
                if ($user->id > 0) {
                    $sql = "INSERT costCenterMembers (costCenterMembers.costCenterId, costCenterMembers.memberId)
    				VALUES ('$costCenterId', '$user->id')";
                    $rows = $adapter->query($sql)->execute();
                }
                $affectRows++;
            }
        }
        return $affectRows;
    }
}

?>