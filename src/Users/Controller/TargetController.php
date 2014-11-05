<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Users\Tools\MyUtils;
use Zend\Validator\Date;
use Zend\Validator\EmailAddress;
use Users\Model\Target;

class TargetController extends AbstractActionController
{

    protected $authservice;

    protected function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    protected function getAdapter()
    {
        if (! $this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
            $setCharset = "SET NAMES 'utf8mb4'";
            $this->adapter->query($setCharset)->execute();
        }
        return $this->adapter;
    }

    /**
     * change array to Json response
     *
     * @param array $result            
     * @return \Zend\Stdlib\ResponseInterface
     */
    protected function returnJson($result)
    {
        $json = Json::encode($result);
        $response = $this->getEvent()->getResponse();
        $response->setContent($json);
        return $response;
    }

    public function indexAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        return $this->returnJson(array(
            'webpage' => 'index'
        ));
    }

    protected function getCloseTargetsAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get targets
        try {
            $targets = $this->getCloseTargets($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "can not get close targets"
            ));
        }
        // return json result
        return $this->returnJson(array(
            'flag' => true,
            'targets' => $targets
        ));
    }

    /**
     *
     * @param string $email,
     *            current user
     * @return Ambigous <\Users\Controller\sortedTargets, multitype:unknown >
     */
    protected function getCloseTargets($email)
    {
        // get create close targets
        $sql = "SELECT * from target inner join userInfo
            on target.receiver = userInfo.email
            where target_creater = '$email' 
            and target_status in (5, 6) 
            and parent_target_id = '0'
            or parent_target_id in (
            SELECT target_id from target
            where target_creater = '$email' 
            and target_status in (5, 6) 
            and parent_target_id = '0')
            ORDER BY target_lastModify_time DESC";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            // format target end time
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            // format target last modify time
            $unixTime = strtotime($row[target_lastModify_time]);
            $row[target_lastModify_time] = date('Y-m-d', $unixTime);
            $array[$i] = $row;
            $i ++;
        }
        // get shared close targets
        $sql = "SELECT * from target inner join userInfo
            on target.target_creater = userInfo.email
            where receiver = '$email'
            and target_status in (5, 6) and target_creater <> '$email'
            ORDER BY target_lastModify_time DESC";
        $rows = $adapter->query($sql)->execute();
        
        foreach ($rows as $row) {
            // format target end time
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            // format target last modify time
            $unixTime = strtotime($row[target_lastModify_time]);
            $row[target_lastModify_time] = date('Y-m-d', $unixTime);
            $array[$i] = $row;
            $i ++;
        }
        
        // sort the targets by target and sub-target
        $sortedTargets = $this->sortTargetsBySubtarget($array, $email);
        
        return $sortedTargets;
    }

    protected function insertTargetAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        // validate the input data
        $target = new Target();
        $target->target_name = MyUtils::isValidateName($_GET['target_name']) ? $_GET['target_name'] : - 1;
        $target->parent_target_id = (int) $_GET['parent_target_id'];
        $end_date = date('Y-m-d', strtotime($_GET['target_end_time']));
        $date = new Date();
        $target->target_end_time = $date->isValid($end_date) ? $end_date : - 1;
        $target->target_content = MyUtils::isValidateAddress($_GET['target_content']) ? $_GET['target_content'] : - 1;
        $target->target_creater = $email;
        $validateEmail = new EmailAddress();
        $target->receiver = $validateEmail->isValid($_GET['receiver']) ? $_GET['receiver'] : - 1;
        // if the receiver is the owner, status is 7, otherwise is 2
        if ($email == $target->receiver) {
            // receiver is the owner
            $target->target_status = 7;
        } else {
            // receiver is a helper
            $target->target_status = 2;
        }
        // if invalid there is a -1 in the target
        foreach ($target as $item) {
            if ($item == - 1) {
                return $this->returnJson(array(
                    'flag' => false,
                    'message' => 'invalidate data'
                ));
            }
        }
        
        // insert the target
        try {
            // get the auto increamental id
            $id = $this->insertTarget($target);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cant insert the target"
            ));
        }
        // set target id for success create target
        $target->target_id = $id->getGeneratedValue();
        // exchange $target to data
        $target1 = MyUtils::exchangeObjectToData($target);
        // return result
        return $this->returnJson(array(
            'flag' => true,
            'target' => $target1
        ));
    }

    /**
     *
     * @param Target $target            
     */
    protected function insertTarget(Target $target)
    {
        $sql = "insert into target (target_name, target_creater, target_end_time, 
            target_content, target_status, parent_target_id, receiver) 
            values ('$target->target_name', '$target->target_creater', '$target->target_end_time',
            '$target->target_content', '$target->target_status', '$target->parent_target_id', '$target->receiver')";
        // excute
        $adapter = $this->getAdapter();
        $id = $adapter->query($sql)->execute();
        return $id;
    }

    protected function getTargetByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get id
        $target_id = (int) $_GET['target_id'];
        
        // validate the target id
        if (! $target_id) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate target id'
            ));
        }
        
        // get subtargets
        try {
            $target = $this->getTargetById($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant get sub targets'
            ));
        }
        // return subtargets
        return $this->returnJson(array(
            'flag' => true,
            'target' => $target
        ));
    }

    /**
     *
     * @param unknown $target_id            
     * @return multitype:
     */
    protected function getTargetById($target_id)
    {
        $sql = "select * from target
            where target_id = '$target_id'";
        $adapter = $this->getAdapter();
        
        $row = $adapter->query($sql)->execute();
        $row = $row->current();
        return $row;
    }

    protected function getSubTargetsByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get id
        $target_id = (int) $_GET['target_id'];
        
        // validate the target id
        if (! $target_id) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate target id'
            ));
        }
        
        // get subtargets
        try {
            $subTargets = $this->getSubTargetsById($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant get sub targets'
            ));
        }
        // return subtargets
        return $this->returnJson(array(
            'flag' => true,
            'subTargets' => $subTargets
        ));
    }

    protected function getSubTargetsById($id)
    {
        $sql = "select * from target
            where parent_target_id = '$id' ORDER BY target_end_time";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // push the result to a sub_targets array
        $subTargets = array();
        foreach ($rows as $row) {
            // format the time "Y-m-d"
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            
            array_push($subTargets, $row);
        }
        
        return $subTargets;
    }

    protected function updateTargetAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the target by id
        if ((int) $_GET['target_id']) {
            // leagal id
            try {
                $target = $this->getTargetById($_GET['target_id']);
            } catch (\Exception $e) {
                return $this->returnJson(array(
                    'flag' => false,
                    'message' => 'can not get target by id' . $_GET['target_id']
                ));
            }
        } else {
            // illeagal id
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate target id'
            ));
        }
        // exchange the array to target
        $newTarget = new Target();
        $newTarget = MyUtils::exchangeDataToObject($target, $newTarget);
        // put the update date to newTarget
        $end_date = date('Y-m-d', strtotime($_GET['target_end_time']));
        $newTarget->target_end_time = $end_date;
        $newTarget->target_content = $_GET['target_content'];
        $newTarget->target_status = $_GET['target_status'];
        // print_r($newTarget);
        // isValidate target and has the right
        if (! $newTarget->isValidate() || ! $this->hasRight($target, $newTarget, $email)) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate target--' . $newTarget->target_id
            ));
        }
        
        // update target by id
        try {
            $this->updateTarget($newTarget);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant update target:' . $newTarget->target_id
            ));
        }
        // return target
        $target = MyUtils::exchangeObjectToData($newTarget);
        return $this->returnJson(array(
            'flag' => true,
            'target' => $target
        ));
    }

    /**
     *
     * @param string $right,
     *            receiver or creater
     * @param string $who,
     *            current user
     * @param int $status,
     *            new target status
     * @param array $validateArray,
     *            allow status for new target
     * @return boolean
     */
    protected function nextStep($right, $who, $status, $validateArray)
    {
        if ($right == $who) {
            // who has the right
            if (! in_array($status, $validateArray)) {
                // target status is wrong
                MyUtils::writelog($who . " cannot update status--" . $status);
                return false;
            }
        } else {
            MyUtils::writelog($who . " do not have the right");
            return false;
        }
        return true;
    }

    /**
     *
     * @param int $id,
     *            parent_target_id of sub
     * @return true, if find a unclose target
     */
    protected function hasUncloseTargetsById($id)
    {
        $sql = "SELECT * from target
            where parent_target_id = '$id' AND target_status not in (5, 6)";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        if ($rows->count()) {
            // at least one unclose
            return true;
        } else {
            // no unclose target
            return false;
        }
    }

    /**
     *
     * @param int $id,parent_target_id
     *            of sub
     * @return true, if find a failed target
     */
    protected function hasFailedTargetsById($id)
    {
        $sql = "SELECT * from target
            where parent_target_id = '$id' AND target_status = '6'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        if ($rows->count()) {
            // at least one failed
            return true;
        } else {
            // no failed target
            return false;
        }
    }

    /**
     *
     * @param Target $target            
     * @param string $who,
     *            current user's email
     */
    protected function isReadyToClose(Target $target, $who)
    {
        $flag = true;
        if ($target->target_creater == $who) {
            // get unclose targets
            if (! $this->hasUncloseTargetsById($target->target_id)) {
                // all targets closeed
                if ($this->hasFailedTargetsById($target->target_id)) {
                    // has failed targets
                    if ($target->target_status == 5) {
                        // try to achieve target when has failed target
                        $flag = false;
                        MyUtils::writelog($who . " try to achieve a failed target");
                    }
                } else {
                    // no failed targets
                    if ($target->target_status == 6) {
                        // try to fail target when all achieve target
                        $flag = false;
                        MyUtils::writelog($who . " try to fail a achieved target");
                    }
                }
            } else {
                // has unclose target
                MyUtils::writelog(who . " try to close unable unclose target--" . $target->target_id);
                $flag = false;
            }
        } else {
            // who has't right to update it
            MyUtils::writelog($who . " donot have right");
            $flag = false;
        }
        return $flag;
    }

    /**
     *
     * @param Target $target            
     * @param string $who,
     *            email of current user
     */
    protected function hasRight($target, Target $newTarget, $who)
    {
        // set flag true
        $flag = true;
        
        switch ($target['target_status']) {
            case '2': // share target
                      // receiver has right;
                      // modify, agree, receiver reject allow
                $validateArray = array(
                    3,
                    7,
                    4
                );
                $flag1 = $this->nextStep($target['receiver'], $who, $newTarget->target_status, $validateArray);
                // creater has right to delete and modify
                $validateArray = array(
                    1,
                    2
                );
                $flag2 = $this->nextStep($target['target_creater'], $who, $newTarget->target_status, $validateArray);
                // met the one of terms then flag is true
                $flag = $flag1 || $flag2;
                break;
            case '3': // modify target
                      // creater has right;
                      // delete, share or agree
                $validateArray = array(
                    1,
                    2,
                    7
                );
                $flag1 = $this->nextStep($target['target_creater'], $who, $newTarget->target_status, $validateArray);
                // receiver has right to modify keep the status
                $validateArray = array(
                    3
                );
                $flag2 = $this->nextStep($target['receiver'], $who, $newTarget->target_status, $validateArray);
                // met the one of terms then flag is true
                $flag = $flag1 || $flag2;
                break;
            case '4': // creater reject target
                      // creater has right;
                      // delete, share
                $validateArray = array(
                    1,
                    2
                );
                $flag = $this->nextStep($target['target_creater'], $who, $newTarget->target_status, $validateArray);
                break;
            case '5': // achieve target
                      // no one has right;
                $flag = false;
                MyUtils::writelog($who . " no body can change achieved target");
                break;
            case '6': // failed target
                      // no one has right;
                $flag = false;
                MyUtils::writelog($who . " no body can change failed target");
                break;
            case '7': // agree target
                      // receiver has right;
                if ($target['receiver'] == $target['target_creater']) {
                    // check the right of close
                    try {
                        $flag = $this->isReadyToClose($newTarget, $who);
                    } catch (\Exception $e) {
                        $flag = false;
                        MyUtils::writelog($who . " meet error when check right");
                    }
                } else {
                    // apply achieve, apply fail
                    $validateArray = array(
                        8,
                        9
                    );
                    $flag = $this->nextStep($target['receiver'], $who, $newTarget->target_status, $validateArray);
                }
                break;
            case '8': // apply achieve target
                      // creater has right;
                      // achieve, creater reject
                $validateArray = array(
                    5,
                    10
                );
                $flag = $this->nextStep($target['target_creater'], $who, $newTarget->target_status, $validateArray);
                break;
            case '9': // apply fail target
                      // creater has right;
                      // fail, creater reject
                $validateArray = array(
                    6,
                    10
                );
                $flag = $this->nextStep($target['target_creater'], $who, $newTarget->target_status, $validateArray);
                break;
            case '10': // creater reject target
                       // receiver has right;
                       // apply achieve, apply fail
                $validateArray = array(
                    8,
                    9
                );
                $flag = $this->nextStep($target['receiver'], $who, $newTarget->target_status, $validateArray);
                break;
            
            default:
                $flag = false;
                MyUtils::writelog($who . " no such target status");
                break;
        }
        // the final result
        if (! $flag) {
            MyUtils::writelog("the last log comes from target--" . $target['target_id']);
        }
        return $flag;
    }

    /**
     *
     * @return \Users\Model\Target
     */
    protected function mockTarget()
    {
        $target = new Target();
        $target->parent_target_id = 0;
        $target->receiver = 'l.yuhai@foxmail.com';
        $target->target_content = "this is a mocked target";
        $target->target_creater = 'l.yuhai@gmail.com';
        $target->target_end_time = '2014-12-31';
        $target->target_id = 2;
        $target->target_name = 'mock target';
        $target->target_status = 2;
        return $target;
    }

    /**
     *
     * @param unknown $target            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function updateTarget(Target $target)
    {
        $sql = "update target set parent_target_id = '$target->parent_target_id', receiver = '$target->receiver',
            target_content = '$target->target_content', target_creater = '$target->target_creater',
            target_end_time = '$target->target_end_time', target_name = '$target->target_name', target_status = '$target->target_status'
            where  target_id = '$target->target_id'";
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    protected function updateStatusByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        // get id and status
        $target_id = (int) $_GET['target_id'];
        $target_status = (int) $_GET['target_status'];
        
        // get target by id
        try {
            $target = $this->getTargetById($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "can't get the target--" . $target_id
            ));
        }
        // create the new target
        $newTarget = new Target();
        $newTarget = MyUtils::exchangeDataToObject($target, $newTarget);
        $newTarget->target_status = $target_status;
        // check the right and validate
        // print_r($newTarget);
        if (! $newTarget->isValidate() || ! $this->hasRight($target, $newTarget, $email)) {
            // if (! $newTarget->isValidate()) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "invalidate target--" . $target_id
            ));
        }
        
        // update status by id
        try {
            $this->updateStatusById($target_id, $target_status);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "can not update status--" . $target_id
            ));
        }
        // return true and target
        return $this->returnJson(array(
            'flag' => true,
            'target' => $newTarget
        ));
    }

    /**
     * update status by id and make sure the right authorization
     *
     * @param int $target_id            
     * @param int $target_status            
     * @param string $email
     *            creater or receiver
     */
    protected function updateStatusById($target_id, $target_status)
    {
        $sql = "UPDATE target set target_status = '$target_status'
            where target_id = '$target_id'";
        
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }
    
    // insert comment by targetId
    protected function addCommentByTargetIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get target id and comment and who
        $target_id = (int) $_POST['target_id'];
        $comment = $_POST['comment'];
        $who = $email;
        
        // validate the comment
        if (MyUtils::isValidateAddress($comment) && $comment) {
            // insert the comment into table
            try {
                $this->insertCommentByTargetId($comment, $target_id, $who);
            } catch (\Exception $e) {
                return $this->returnJson(array(
                    flag => false
                ));
            }
            // if success return true to web
            return $this->returnJson(array(
                flag => true
            ));
        } else {
            // return false to web
            return $this->returnJson(array(
                flag => false
            ));
        }
    }

    /**
     * insert comment
     *
     * @param string $comment            
     * @param int $target_id            
     * @param int $who            
     */
    protected function insertCommentByTargetId($comment, $target_id, $who)
    {
        // convert $comment before into db
        $comment = addslashes($comment);
        // sql
        $sql = "INSERT into `comment` (target_id , comment , who)
            VALUES ('$target_id', '$comment', '$who') ";
        
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }
    
    // get targets by email which is getten in Session, both receiver and creater
    protected function getCreateTargetsAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get targets by emaill
        try {
            $targets = $this->getCreateTargets($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                false
            ));
        }
        
        // return the Json results
        return $this->returnJson($targets);
    }
    
    // get share targets by email which is getten in Session, both receiver and creater
    protected function getShareTargetsAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get targets by emaill
        try {
            $targets = $this->getShareTargets($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                false
            ));
        }
        
        // return the Json results
        return $this->returnJson($targets);
    }

    /**
     * get creater targets where the creater is the email
     *
     * @param unknown $email            
     * @return array
     */
    protected function getCreateTargets($email)
    {
        $sql = "SELECT DISTINCT * from target inner join userInfo
            on target.receiver = userInfo.email
            where parent_target_id in (select target_id from target
                where target_creater = '$email' and receiver = '$email' and target_status = '7')
            or target_creater = '$email' and target_status in  ('2', '3','4','7','8','9','10')
            ORDER BY target_end_time";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            // format target end time
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            // format target last modify time
            $unixTime = strtotime($row[target_lastModify_time]);
            $row[target_lastModify_time] = date('Y-m-d', $unixTime);
            $array[$i] = $row;
            $i ++;
        }
        
        // sort the targets by target and sub-target
        $sortedTargets = $this->sortTargetsBySubtarget($array, $email);
        
        return $sortedTargets;
    }

    /**
     * get share targets where receiver is the email
     *
     * @param unknown $email            
     * @return array
     */
    protected function getShareTargets($email)
    {
        $sql = "SELECT DISTINCT * from target inner join userInfo
            on target.target_creater = userInfo.email
            where receiver = '$email' and target_status in ('2', '3','7','8','9','10')
                and target_creater != '$email'
            ORDER BY target_end_time";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            // format target end time
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            // format target last modify time
            $unixTime = strtotime($row[target_lastModify_time]);
            $row[target_lastModify_time] = date('Y-m-d', $unixTime);
            $array[$i] = $row;
            $i ++;
        }
        
        // sort the targets by target and sub-target
        $sortedTargets = $this->sortTargetsBySubtarget($array, $email);
        
        return $sortedTargets;
    }

    /**
     * get the $targets from table by endtime, sort them by subtarget
     *
     * @param
     *            $targets
     * @return sortedTargets
     */
    protected function sortTargetsBySubtarget($targets, $email)
    {
        $i = 1;
        $sortedTargets = array();
        foreach ($targets as $target) {
            
            if (! $target['parent_target_id'] and $target['target_creater'] == $email) {
                // if it's a maintarget
                // push it to sortedTargets
                $sortedTargets[$i] = $target;
                $i ++;
                
                // put the relative subTargets to the target
                foreach ($targets as $subTarget) {
                    if ($target['target_id'] == $subTarget['parent_target_id']) {
                        $sortedTargets[$i] = $subTarget;
                        $i ++;
                    }
                }
            } elseif ($target['receiver'] == $email and $target['target_creater'] != $email) {
                // shared targets
                $sortedTargets[$i] = $target;
                $i ++;
            } elseif ($target['parent_target_id'] == 0 and $target['target_creater'] != $email) {
                // shared targets's parent targets
                $sortedTargets[$i] = $target;
                $i ++;
            }
        }
        return $sortedTargets;
    }

    protected function getCommentsOfSubAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $target_id = (int) $_GET['target_id'];
        
        // get comments by target id
        try {
            $comments = $this->getCommentsOfSub($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant get comments'
            ));
        }
        
        // return comments in Json format
        return $this->returnJson($comments);
    }

    /**
     *
     * @param unknown $target_id            
     * @return unknown
     */
    protected function getCommentsOfSub($target_id)
    {
        $sql = "select * from `comment`
            where target_id = '$target_id' or target_id = (
            SELECT parent_target_id from target
            where target_id = '$target_id')
            ORDER BY create_time DESC";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            $array[$i] = $row;
            $i ++;
        }
        return $array;
    }

    protected function getCommentsByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $target_id = (int) $_GET['target_id'];
        
        // get comments by target id
        $comments = $this->getCommentsById($target_id);
        
        // return comments in Json format
        return $this->returnJson($comments);
    }

    /**
     * get the comments from comment by target id
     *
     * @param unknown $target_id            
     * @return unknown
     */
    protected function getCommentsById($target_id)
    {
        $sql = "SELECT * from `comment`
                WHERE target_id = '$target_id'
                ORDER BY create_time DESC";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            $array[$i] = $row;
            $i ++;
        }
        return $array;
    }

}
