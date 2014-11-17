<?php
namespace Users\Controller;

use Users\Tools\MyUtils;
use Users\Model\User;
use Users\Model\Target;
use Zend\Stdlib\ArrayUtils;
use Users\Model\TargetMembers;
use Users\Model\PushInfo;
use Users\Model\PushStack;

class WebServiceTargetController extends CommController
{

    protected $user;

    protected $target;

    protected $deviceTokens;

    protected function index()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $result = array(
            "flag" => "WebServiceTagetController"
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

    /**
     *
     * @param string $phoneNumber            
     * @return User
     */
    protected function getUserById($id)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $row = $userTable->getUserById($id);
        return $row;
    }

    /**
     *
     * @param User $user            
     */
    protected function updateUser(User $user)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $userTable->updateUserById($user);
    }

    /**
     *
     * @param unknown $target_id            
     * @return Target:
     */
    protected function getTargetById($target_id)
    {
        $sql = "select * from target
    	where target_id = '$target_id'";
        $adapter = $this->getAdapter();
        
        $row = $adapter->query($sql)->execute();
        $data = $row->current();
        $target = new Target();
        foreach ($target as $key => $value) {
            $target->$key = (isset($data[$key])) ? $data[$key] : null;
        }
        // $target = MyUtils::exchangeDataToObject($row, $target);
        return $target;
    }

    /**
     *
     * @param unknown $target_id            
     * @return Ambigous <object, Object>
     */
    protected function isUpdateableTargetById($target_id)
    {
        $sql = "select * from target
            where target_id = '$target_id' 
            and target_status <> 'deleteByCreater'
            and target_status <> 'deleteByCreaterAgain'";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        if ($rows->count() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * agree, deleteByCreater and deleteByCreaterAgain target return false
     *
     * @param unknown $target_id            
     * @return boolean
     */
    protected function isUpdateableTargetById1($target_id)
    {
        $user = $this->user;
        $sql = "SELECT * from targetMembers, target
    	where target.target_id = '$target_id' and members_id = '$user->id'
    	and (member_status = 'agree'
    	or target.target_status = 'deleteByCreater' or target.target_status = 'deleteByCreaterAgain')
    	and target.target_id = targetMembers.target_id";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        if ($rows->count() > 0) {
            return false;
        } else {
            return true;
        }
    }

    protected function getHelpersByOwnerId($ownerId, $status)
    {
        if ($status) {
            $sql = "select * from relationship WHERE
                owner = '$ownerId' and `status` = '$status'";
        } else {
            $sql = "select * from relationship WHERE
                owner = '$ownerId'";
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

    public function createTargetWithMembersAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "createTargets";
        $this->target = new Target();
        $this->target->target_end_time = $_POST["target_end_time"];
        $timeLimit = (string) mktime(- 1);
        $this->target->target_name = $_POST["target_name"];
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        // comm check user
        // require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        // if ($flag != "validUser") {
        // return $this->returnJson(array(
        // "flag" => $flag
        // ));
        // }
        
        $membersString = $_POST["members"] ? $_POST["members"] : "";
        if (strlen($membersString) > 0) {
            $members = array();
            $members = MyUtils::changeStringtoArray($membersString);
            $this->target->receiver = "members";
            $this->target->target_creater = $this->user->id;
            $this->target->target_content = $_POST["target_content"];
            $this->target->eventIdentifier = $_POST["eventIdentifier"];
            $this->target->target_create_time = mktime();
            // add memeber to target
            $target_id = $_POST["target_id"];
            $target_id = str_replace(" ", "", $target_id);
            if ($target_id < 10) {
                // check target name
                if (strlen($this->target->target_name) < 1) {
                    $flag = "targetEmptyError";
                    return $this->returnJson(array(
                        "flag" => $flag
                    ));
                }
                // check target end time
                if ($this->target->target_end_time < $timeLimit) {
                    $flag = "invalidTargetEndTime";
                    return $this->returnJson(array(
                        "flag" => $flag
                    ));
                }
                // add members at this target
                try {
                    $result = $this->createTarget2();
                    $flag = $result['flag'];
                    $target_id = $result["targetId"];
                    $target_lastModify_time = $result["target_lastModify_time"];
                    if ($target_id < 10) {
                        $flag = "failedCreateTarget";
                    } else {
                        $flag = "success";
                    }
                } catch (\Exception $e) {
                    $flag = "failedCreateTarget";
                }
            }
            if ($flag != "failedCreateTarget") {
                try {
                    $this->target->target_id = $target_id ? $target_id : 0;
                    if ($this->target->target_id > 10) {
                        // check the user is the creater of the target
                        if ($this->isUserOfTargetCreater($target_id, $this->user->id)) {
                            $target = $this->getTargetById($target_id);
                            $this->target = $target;
                            $this->createTargetWithMembers($members, $this->target->target_id);
                            // push create target message to user
                            $message = '您有一个新目标 "' . $this->target->target_name . '".';
                            // try {
                            // $pushTarget = $this->getFullTargetById($this->target->target_id);
                            // } catch (\Exception $e) {
                            // $pushTarget = null;
                            // }
                            $this->pushMessage($message, $this->target->target_id);
                            $flag = "successCreateMembers";
                        } else {
                            $flag = "noPermitionAddMembers";
                        }
                    } else {
                        throw new \Exception("addMemberFailed");
                    }
                } catch (\Exception $e) {
                    $flag = "addMemberFailed";
                    throw new \Exception($e);
                }
            }
        } else {
            $flag = "noMemberError";
        }
        
        $result = array(
            "flag" => $flag ? $flag : "",
            "target_id" => $target_id,
            "target_create_time" => $this->target->target_create_time
        );
        return $this->returnJson($result);
    }

    /**
     * check target creater
     *
     * @param int $target_id            
     * @param int $user_id            
     * @return boolean
     */
    protected function isUserOfTargetCreater($target_id, $user_id)
    {
        $result = false;
        try {
            $target = $this->getTargetById($target_id);
        } catch (\Exception $e) {
            $result = false;
        }
        if ($target->target_creater == $user_id) {
            $result = true;
        }
        return $result;
    }

    /**
     *
     * @param unknown $message            
     * @return string
     */
    protected function pushMessage($message, $target_id)
    {
        if (count($this->deviceTokens) > 0) {
            $pushInfo = new PushInfo();
            $pushInfo->deviceTokens = $this->deviceTokens;
            $pushInfo->message = $message;
            $pushInfo->target = $target_id;
            // $pushInfo->target = $target_id;
            $pushInfo->uploadPath = $this->getFileCertificationLocation();
            $pushInfo->userStatus = "";
            // save the pushInfo to stack
            try {
                $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
                MyUtils::savePushInfo($pushInfo, $pushStackTable);
                // $this->savePushToPushStack($pushInfo);
            } catch (\Exception $e) {
                $flag = "failedSavePush";
                throw new \Exception($e);
            }
            // $resulte = MyUtils::newReadyGoPushNotification($pushInfo);
        } else {
            $flag = "noToken";
        }
        return $flag;
    }

    /**
     *
     * @param PushInfo $pushInfo            
     */
    protected function savePushToPushStack(PushInfo $pushInfo)
    {
        $deviceTokens = $pushInfo->deviceTokens;
        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        foreach ($deviceTokens as $deviceToken) {
            $pushStack = new PushStack();
            $pushStack->deviceToken = $deviceToken["deviceToken"];
            $pushStack->message = $pushInfo->message;
            $pushStack->notificationNumber = $deviceToken["notificationNumber"];
            $pushStack->status = "waiting";
            $pushStack->target_id = $pushInfo->target["target_id"];
            $pushStack->userStatus = $pushInfo->userStatus;
            
            $pushStackTable->savePushStack($pushStack);
        }
    }

    /**
     *
     * @param unknown $members            
     * @param unknown $target_id            
     */
    protected function createTargetWithMembers($members, $target_id)
    {
        
        // prepare deviceTokens
        $this->deviceTokens = array();
        foreach ($members as $member) {
            if (MyUtils::isValidateTel($member)) {
                try {
                    $user = $this->getUserByPhoneNumber($member, "");
                } catch (\Exception $e) {
                    $user = null;
                }
                if ($user->id > 0) {
                    // insert the user id to target
                    $targetMember = new TargetMembers();
                    $targetMember->target_id = $target_id;
                    $targetMember->members_id = $user->id;
                    $targetMember->member_status = 'create';
                    $targetMember->last_update_time = mktime();
                    try {
                        $this->saveTargetMembers($targetMember);
                    } catch (\Exception $e) {
                        throw new \Exception($e);
                    }
                    // save user deviceToken to deviceTokens
                    $user->notificationNumber ++;
                    try {
                        $this->updateUser($user);
                    } catch (\Exception $e) {}
                    
                    if (strlen($user->deviceToken) > 5) {
                        $deviceToken = array(
                            "deviceToken" => $user->deviceToken,
                            "notificationNumber" => $user->notificationNumber
                        );
                        array_push($this->deviceTokens, $deviceToken);
                    }
                }
            }
        }
    }

    /**
     *
     * @throws \Exception
     * @return multitype:string unknown
     */
    protected function createTarget2()
    {
        $target = $this->target;
        // get receiver id by phoneNumber
        $adapter = $this->getAdapter();
        if ($target->receiver != "members") {
            
            $sql = "SELECT users.id from users
            where users.phoneNumber = '$target->receiver'";
            $rows = $adapter->query($sql)->execute();
            $receiverId = $rows->current()[id];
        } else {
            $receiverId = 1;
            $target_status = "mutipleHelpers";
        }
        
        if ($receiverId > 0) {
            if (! $this->hasBlockedByRevceiver($receiverId, $adapter)) {
                $target_status = "create";
                $flag = "successCreateTarget";
            } else {
                $target_status = "block";
                $flag = "blockByRevceiver";
            }
            
            $target_lastModify_time = mktime();
            if (strlen($target->target_content) > 0) {
                $sql = "INSERT target (target.target_content, target.target_creater, target.target_end_time,
            	target.target_name, target.target_status, target.receiver, target.eventIdentifier, target.target_create_time,
            target.target_lastModify_time)
            	VALUES( '$target->target_content','$target->target_creater', '$target->target_end_time',
            	'$target->target_name', '$target_status', '$receiverId', '$target->eventIdentifier', '$target->target_create_time',
            '$target_lastModify_time')";
            } else {
                $sql = "INSERT target (target.target_creater, target.target_end_time, target.target_name,
            	target.target_status, target.receiver, target.eventIdentifier, target.target_create_time,
            target.target_lastModify_time)
            	VALUES('$target->target_creater', '$target->target_end_time', '$target->target_name',
            	'$target_status', '$receiverId', '$target->eventIdentifier', '$target->target_create_time',
            '$target_lastModify_time')";
            }
            
            $rows = $adapter->query($sql)->execute();
            $targetId = $rows->getGeneratedValue();
        } else {
            $flag = "noSuchUser";
        }
        
        $result = array(
            "flag" => $flag,
            "targetId" => $targetId,
            "target_lastModify_time" => $target_lastModify_time
        );
        
        return $result;
    }

    protected function hasBlockedByRevceiver($receiverId, $adapter)
    {
        $user = $this->user;
        $sql = "SELECT * from relationship
            WHERE `owner`='$receiverId' and helper = '$user->phoneNumber'  and `status` = '3'";
        $rows = $adapter->query($sql)->execute();
        if ($rows->count() > 0) {
            print "true";
            return true;
        } else {
            return false;
        }
    }

    public function deleteTargetAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "successDeleteTarget";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $target_id = $_POST["target_id"];
        $target_status = $_POST["target_status"];
        
        if (strlen($target_id) > 0) {
            if ($target_status == "deleteByCreater" || $target_status == "deleteByCreaterAgain") {
                try {
                    $this->deleteTargetById($target_id, $target_status);
                } catch (\Exception $e) {
                    $flag = "failedDeleteTarget";
                }
            } else {
                $flag = "invalidStatus";
            }
        } else {
            $flag = "invalidTargetId";
        }
        
        $result = array(
            "flag" => $flag,
            "status" => $target_status
        );
        return $this->returnJson($result);
    }

    protected function deleteTargetById($target_id, $target_status)
    {
        // can the target be delete?
        if ($this->isDeleteableTarget($target_id)) {
            $creater = $this->user->id;
            // and target_status <> 'agree' target_memeber status
            $sql = "UPDATE target set target_status = '$target_status'
            where target_id ='$target_id'
            and target_creater = '$creater' ";
            $adapter = $this->getAdapter();
            $rows = $adapter->query($sql)->execute();
            $result = $rows->getAffectedRows();
            if ($result != 1) {
                throw new \Exception("failedDeleteTarget");
            }
        }
    }

    protected function isDeleteableTarget($target_id)
    {
        $targetMembers = $this->getTargetStatusById($target_id);
        foreach ($targetMembers as $targetMember) {
            if ($targetMember[member_status] == 'agree') {
                return false;
            }
        }
        return true;
    }

    public function getTargetIdAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $target_id = $_POST["target_id"];
        if ($target_id > 10) {
            if ($this->isMemberOfTarget($target_id)) {
                try {
                    $adapter = $this->getAdapter();
                    $target = MyUtils::getFullTargetById($target_id, $adapter);
                    $flag = "successGetTarget";
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
            } else {
                $flag = "noPermission";
            }
        } else {
            $flag = "targetIdError";
            $target = null;
        }
        
        return $this->returnJson(array(
            "flag" => $flag,
            "target" => $target
        ));
    }

    protected function isMemberOfTarget($target_id)
    {
        $user = $this->user;
        try {
            $sql = "SELECT *
            from targetMembers
            where target_id = '$target_id' and members_id = '$user->id'";
            $adapter = $this->getAdapter();
            $rows = $adapter->query($sql)->execute();
        } catch (\Exception $e) {
            return false;
        }
        
        if ($rows->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

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
                $targets = $this->getTargetStatusById($target_id);
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

    protected function getTargetsByCreaterWithPhoneNumber($target_status, $target_id)
    {
        $user = $this->user;
        date_default_timezone_set('Asia/Shanghai');
        $lastDay = (string) mktime(- 24);
        
        if (strlen($target_status) > 0) {
            $sql = "SELECT target.target_end_time, target.target_name, target.target_id, target.target_creater, target.target_status, target.target_content, 
                users.phoneNumber as receiver, target.eventIdentifier from target, users 
                WHERE
                target_creater = '$user->id'
                and target_status = '$target_status'
                and receiver = users.id and target_end_time > '$lastDay'
                ORDER BY target_end_time DESC";
        } else 
            if (strlen($target_id) > 0) {
                $sql = "SELECT target.target_end_time, target.target_name, target.target_id, target.target_creater, target.target_status, target.target_content, 
                users.phoneNumber as receiver, target.eventIdentifier from target, users 
                WHERE
                target_id = '$target_id' and target_creater = '$user->id'
                and receiver = users.id and target_end_time > '$lastDay'
                ORDER BY target_end_time DESC";
            } else {
                $sql = "SELECT DISTINCT target.target_end_time, target.target_name, target.target_id, target.target_creater, targetMembers.member_status as target_status, 
                        target.target_content, users.phoneNumber as receiver, target.eventIdentifier, target.target_create_time, target_lastModify_time as last_update_time 
                        from target, users, targetMembers 
                        WHERE 
                        target.target_id = targetMembers.target_id 
                        and (target.target_status = 'create' or target.target_status = 'reject' or target.target_status = 'agree' or target.target_status = 'pause' or target.target_status = 'block') 
                        and target_creater = '$user->id' 
                        and receiver = users.id 
                        and target_end_time > '$lastDay' 
                        ORDER BY target_end_time DESC";
            }
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        
        $results = array();
        foreach ($rows as $row) {
            $row["target_creater"] = $user->phoneNumber;
            array_push($results, $row);
        }
        
        // update receiver by members
        $results = $this->updateTargetReceiverByMembers($results);
        
        return $results;
    }

    protected function removeDuplicateTargets($targets)
    {
        $array = array();
        foreach ($targets as $target) {
            $flag = "NO";
            if (count($array) == 0) {
                $flag = "YES";
            }
            foreach ($array as $item) {
                if ($target['target_id'] != $item['target_id']) {
                    $flag = "YES";
                } else {
                    $flag = "NO";
                    break;
                }
            }
            if ($flag == "YES") {
                array_push($array, $target);
            }
        }
        return $array;
    }
    
    // protected function getFullTargetById($id)
    // {
    // $sql = "SELECT DISTINCT target.target_end_time, target.target_name, target.target_id, target.receiver,
    // targetMembers.member_status as target_status, target.target_content,
    // users.phoneNumber as target_creater, target.eventIdentifier, target.target_create_time,
    // target_lastModify_time as last_update_time
    // from target , targetMembers, users
    // WHERE target.target_id = '$id'
    // and target.target_id = targetMembers.target_id
    // and target.target_creater = users.id";
    
    // $adapter = $this->getAdapter();
    // $rows = $adapter->query($sql)->execute();
    // $row = $rows->current();
    // return $row;
    // }
    protected function getTargetsByReceiverWithPhoneNumber($target_status, $target_id)
    {
        $user = $this->user;
        date_default_timezone_set('Asia/Shanghai');
        $lastDay = (string) mktime(- 24);
        
        if (strlen($target_status) > 0) {
            $sql = "SELECT target.target_end_time, target.target_name, target.target_id, target.receiver, target.target_status, target.target_content, 
                users.phoneNumber as target_creater, target.eventIdentifier from target, users 
                WHERE
                receiver = '$user->id'
                and target_status = '$target_status'
                and target_creater = users.id and target_end_time > '$lastDay'
                ORDER BY target_end_time DESC";
        } else 
            if (strlen($target_id) > 0) {
                $sql = "SELECT target.target_end_time, target.target_name, target.target_id, target.receiver, target.target_status, target.target_content, 
                users.phoneNumber as target_creater, target.eventIdentifier from target, users 
                WHERE
                target_id = '$target_id' and receiver = '$user->id'
                and target_creater = users.id and target_end_time > '$lastDay'
                ORDER BY target_end_time DESC";
            } else {
                $sql = "SELECT DISTINCT target.target_end_time, target.target_name, target.target_id, target.receiver,
                targetMembers.member_status as target_status, target.target_content,
                users.phoneNumber as target_creater, target.eventIdentifier, target.target_create_time,
                target_lastModify_time as last_update_time
                from target , targetMembers, users
                WHERE targetMembers.members_id = '$user->id'
                and (targetMembers.member_status = 'create'
                or targetMembers.member_status = 'agree' or targetMembers.member_status = 'pause'
                or targetMembers.member_status = 'block')
                and  (target.target_status = 'create'
                or target.target_status = 'agree' or target.target_status = 'pause'
                or target.target_status = 'block')
                and target.target_id = targetMembers.target_id
                and target.target_creater = users.id
                and target_end_time > '$lastDay'
                ORDER BY target_end_time DESC";
            }
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        $results = array();
        // keep it for 1.0
        foreach ($rows as $row) {
            $row["receiver"] = "007";
            array_push($results, $row);
        }
        $results = $this->updateTargetReceiverByMembers($results);
        return $results;
    }

    protected function updateTargetReceiverByMembers($targets)
    {
        $adapter = $this->getAdapter();
        $results = array();
        foreach ($targets as $item) {
            // $sql = "SELECT * from targetMembers where target_id = '$item[target_id]'";
            $sql = "SELECT member_status, users.phoneNumber 
                from targetMembers,users where target_id = '$item[target_id]' and users.id = members_id";
            $rows = $adapter->query($sql)->execute();
            if ($rows->count() == 1) {
                $memberTarget = $rows->current();
                $item['receiver'] = $memberTarget['phoneNumber'];
            }
            // if the creater is the self,jump
            array_push($results, $item);
            
            // if ($item['target_creater'] != $this->user->phoneNumber) {
            // array_push($results, $item);
            // }
        }
        return $results;
    }

    public function updateStatusByTargetIdAction()
    {
        $flag = "updateStatusByTargetId";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $target_status = $_POST["target_status"];
        $target_id = $_POST["target_id"];
        $last_update_time = mktime();
        if (MyUtils::isRightStatus($target_status) and $target_id > 0) {
            // check target current status
            if ($this->isUpdateableTargetById1($target_id)) {
                try {
                    $updateResult = $this->newUpdateStatusByTargetId($target_status, $target_id, $last_update_time);
                    if ($updateResult > 0) {
                        $flag = "successUpdateTargetStatus";
                    } else {
                        $flag = "noRecordBeUpdated";
                    }
                } catch (\Exception $e) {
                    $flag = "failedUpdateTargetStatus";
                }
            } else {
                $flag = "failedUpdateTargetStatus";
            }
        } else {
            $flag = "statusOrIdError";
        }
        
        $result = array(
            "flag" => $flag,
            "last_update_time" => $last_update_time
        );
        return $this->returnJson($result);
    }

    protected function updateStatusByTargetId($target_status, $target_id)
    {
        $user = $this->user;
        $sql = "UPDATE target SET target_status = '$target_status'
    	where target_id = '$target_id' and
    	(target_creater ='$user->id' or receiver = '$user->id')";
        
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        return $rows->getAffectedRows();
    }

    /**
     *
     * @param unknown $target_id            
     */
    protected function updateLastModifyTimeByTargetId($target_id, $target_lastModify_time)
    {
        $user = $this->user;
        $sql = "UPDATE target SET target_lastModify_time = '$target_lastModify_time'
            	where target_id = '$target_id'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }

    protected function newUpdateStatusByTargetId($target_status, $target_id, $last_update_time)
    {
        $result = $this->updateTargetMemberStatus($target_id, $target_status, $last_update_time);
        if ($result == 1) {
            $this->updateLastModifyTimeByTargetId($target_id, $last_update_time);
            $this->updateUserNotificationNumber($target_id, $target_status);
        }
        return $result;
    }

    /**
     *
     * @param unknown $target_id            
     * @param unknown $target_status            
     */
    protected function updateUserNotificationNumber($target_id, $target_status)
    {
        // get Target
        $target = $this->getTargetById($target_id);
        $user = $this->user;
        if ($target->target_creater == $user->id) {
            // update it by creater
        } else {
            // update it by receiver
            // update creater notificaitonNumber
            $creater = $this->getUserById($target->target_creater);
            $creater->notificationNumber ++;
            $this->updateUser($creater);
            // push a message to creater
            $this->deviceTokens = array();
            if (strlen($creater->deviceToken) > 5) {
                $deviceToken = array(
                    "deviceToken" => $creater->deviceToken,
                    "notificationNumber" => $creater->notificationNumber
                );
                array_push($this->deviceTokens, $deviceToken);
            }
            switch ($target_status) {
                case "agree":
                    $message = "目标‘" . "$target->target_name" . "’有一个同意回复。";
                    break;
                case "reject":
                    $message = "目标‘" . "$target->target_name" . "’有一个拒绝回复。";
                    break;
                case "pause":
                    $message = "目标‘" . "$target->target_name" . "’有一个暂定回复。";
                    break;
                default:
                    $message = "您的目标有一个回复。";
                    break;
            }
            // $target = $this->getFullTargetById($target_id);
            $this->pushMessage($message, $target_id);
        }
    }

    /**
     *
     * @param unknown $target_id            
     * @param unknown $target_status            
     * @param unknown $last_update_time            
     * @return affectedRows
     */
    protected function updateTargetMemberStatus($target_id, $target_status, $last_update_time)
    {
        $user = $this->user;
        $sql = "UPDATE targetMembers SET member_status = '$target_status',
            last_update_time = '$last_update_time'
            where target_id = '$target_id' and members_id = '$user->id'";
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }

    /**
     *
     * @param unknown $target_id            
     * @return multitype: member_status,phoneNumber
     */
    protected function getTargetStatusById($target_id)
    {
        // $user = $this->user;
        $sql = "SELECT member_status, users.phoneNumber as phoneNumber, 
        last_update_time
        from targetMembers, users
            where target_id = '$target_id' and members_id = users.id";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $targetMembers = array();
        foreach ($rows as $row) {
            array_push($targetMembers, $row);
        }
        return $targetMembers;
    }

    protected function getTargetStatusByIdForReceiver($target_id)
    {
        $user = $this->user;
        $sql = "SELECT member_status, users.phoneNumber as phoneNumber, 
        last_update_time
        from targetMembers, users
            where target_id = '$target_id'  and members_id = '$user->id' 
            and members_id = users.id";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $targetMembers = array();
        foreach ($rows as $row) {
            array_push($targetMembers, $row);
        }
        return $targetMembers;
    }

    /**
     *
     * @return boolean
     */
    protected function getFileCertificationLocation()
    {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        if ($config instanceof \Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! empty($config['module_config']['certifications'])) {
            return $config['module_config']['certifications'];
        } else {
            return FALSE;
        }
    }

    /**
     * create a new targetMembers
     *
     * @param TargetMembers $targetMembers            
     */
    protected function saveTargetMembers(TargetMembers $targetMember)
    {
        $targetMembersTable = $this->getServiceLocator()->get('TargetMembersTable');
        $targetMembersTable->saveTargetMembers($targetMember);
    }
}

?>