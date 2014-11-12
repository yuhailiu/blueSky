<?php
namespace Users\Controller;

use Users\Controller\WebServiceTargetController;
use Users\Model\Comment;
use Users\Tools\MyUtils;
use Users\Model\PushInfo;
use Users\Model\Notification;

class WebServiceCommentController extends WebServiceTargetController
{

    protected $createrId;

    protected $comment;

    public function indexAction()
    {
        return $this->returnJson(array(
            "flag" => "webServiceComment"
        ));
    }

    public function createCommentAction()
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
        $comment = $_POST["comment"];
        $target_id = $_POST["target_id"];
        
        try {
            $create_time = mktime();
            $this->createComment($comment, $target_id, $create_time);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        return $this->returnJson(array(
            "flag" => "successCreateComment",
            "create_time" => $create_time
        ));
    }

    protected function createComment($comment, $target_id, $create_time)
    {
        $user = $this->user;
        $obj = new Comment();
        $obj->comment = $comment;
        if (strlen($comment) < 1) {
            throw new \Exception("noComment");
        }
        $obj->target_id = $target_id;
        if ($target_id > 10) {
            try {
                $target = $this->getTargetById($target_id);
                $this->target = $target;
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            $obj->create_user = $user->id;
            $obj->create_time = $create_time;
            try {
                $this->saveComment($obj);
            } catch (\Exception $e) {
                throw new \Exception("failedCreateComment");
            }
            // save comment to pushStack
            try {
                $this->saveCommentToPushStack();
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        } else {
            throw new \Exception("targetIdErro");
        }
    }

    protected function saveComment(Comment $comment)
    {
        $sql = "INSERT `comment` (`comment`, create_time, create_user, file_name, target_id)
        VALUES('$comment->comment', '$comment->create_time', '$comment->create_user',
        '$comment->file_name', '$comment->target_id')";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $comment->id = $rows->getGeneratedValue();
        $this->comment = $comment;
    }

    protected function saveCommentToPushStack()
    {
        $target = $this->target;
        $comment = $this->comment;
        $user = $this->user;
        $pushInfo = new PushInfo();
        $message = '您的目标“' . $target->target_name . '” 有一个进度更新“' . $comment->comment . '”。';
        $pushInfo->message = $message;
        $pushInfo->comment_id = $comment->id;
        if ($target->target_creater == $user->id) {
            // if user is the target creater push it to all the helpers
            try {
                $this->saveCommentPushForCreater($pushInfo);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        } else {
            // if user is one of the helpers of target, push it to creater
            try {
                $this->saveComentPushForReceiver($pushInfo);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
    }

    protected function saveCommentPushForCreater(PushInfo $pushInfo)
    {
        // get addedMembers
        $addedMembers = $this->getAddedMembers();
        // get agree deviceTokens
        $deviceTokens = array();
        foreach ($addedMembers as $member) {
            $deviceToken = array(
                "deviceToken" => $member["deviceToken"],
                "notificationNumber" => $member["notificationNumber"]
            );
            array_push($deviceTokens, $deviceToken);
        }
        $pushInfo->deviceTokens = $deviceTokens;
        // save it by deviceToken
        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        MyUtils::savePushInfo($pushInfo, $pushStackTable);
    }

    protected function saveComentPushForReceiver(PushInfo $pushInfo)
    {
        // get creater's deviceToken
        $target = $this->target;
        $id = (int) $target->target_creater;
        $user = $this->getUserById($id);
        $user->notificationNumber ++;
        $notification = new Notification();
        $notification->notificationNumber = $user->notificationNumber;
        $notification->userId = $user->id;
        $this->newUpdateUserNotificationNumber($notification);
        $deviceToken = array(
            "deviceToken" => $user->deviceToken,
            "notificationNumber" => $user->notificationNumber
        );
        $pushInfo->deviceTokens = array();
        array_push($pushInfo->deviceTokens, $deviceToken);
        // save it by deviceToken
        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        MyUtils::savePushInfo($pushInfo, $pushStackTable);
    }

    protected function getAddedMembers()
    {
        // get agree members id
        $agreeMembers = $this->getAgreeMembersByTargetId();
        // add 1 at every notificationNumber
        $addedMembers = $this->addOneOnNotificationNumber($agreeMembers);
        // update user table with members
        $this->updateMembersNotificationNumber($addedMembers);
        
        return $addedMembers;
    }

    protected function updateMembersNotificationNumber($members)
    {
        foreach ($members as $member) {
            $notification = new Notification();
            $notification->notificationNumber = $member["notificationNumber"];
            $notification->userId = $member["id"];
            $this->newUpdateUserNotificationNumber($notification);
        }
    }

    protected function addOneOnNotificationNumber($members)
    {
        $addedMembers = array();
        foreach ($members as $member) {
            if (strlen($member["deviceToken"]) > 5) {
                $deviceToken = array();
                $deviceToken["id"] = $member["id"];
                $deviceToken["deviceToken"] = $member["deviceToken"];
                $deviceToken["notificationNumber"] = $member["notificationNumber"] + 1;
                array_push($addedMembers, $deviceToken);
            }
        }
        return $addedMembers;
    }

    protected function getAgreeMembersByTargetId()
    {
        $target = $this->target;
        $sql = "SELECT deviceToken, notificationNumber, users.id from targetMembers, users
            where target_id = '$target->target_id' and member_status = 'agree'
            and users.id = members_id";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $array = array();
        foreach ($rows as $row) {
            array_push($array, $row);
        }
        return $array;
    }

    public function getCommentsAction()
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
        $target_id = $_POST["target_id"];
        $lastGetTime = $_POST["lastGetTime"];
        try {
            $comments = $this->getCommentsByTargetId($target_id, $lastGetTime);
        } catch (\Exception $e) {
            throw new \Exception($e);
            $flag = "failedGetComments";
        }
        return $this->returnJson(array(
            "flag" => "successGetComments",
            "comments" => $comments
        ));
    }

    protected function getCommentsByTargetId($target_id, $lastGetTime)
    {
        if ($target_id < 10) {
            throw new \Exception("targetIdError");
        }
        if ($this->isCreaterOfTarget($target_id)) {
            // get comments by target creater
            $comments = $this->getCommentsByTargetCreater($target_id, $lastGetTime);
        } else {
            // get comments by target receiver
            $comments = $this->getCommentsByTargetReceiver($target_id, $lastGetTime);
        }
        $array = array();
        foreach ($comments as $comment) {
            $temp = array();
            $temp['file_name'] = $comment['file_name'];
            $temp['chat_comment'] = $comment['comment'];
            $temp['target_id'] = $comment['target_id'];
            $temp['create_time'] = $comment['create_time'];
            $temp['create_user'] = $comment['create_user'];
            array_push($array, $temp);
        }
        return $array;
        // return $comments;
    }

    protected function isCreaterOfTarget($target_id)
    {
        $user = $this->user;
        $target = $this->getTargetById($target_id);
        if ($user->id == $target->target_creater) {
            return true;
        } else {
            $this->createrId = $target->target_creater;
            return false;
        }
    }

    protected function getCommentsByTargetCreater($target_id, $lastGetTime)
    {
        if (strlen($lastGetTime) > 5) {
            $sql = "SELECT comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' and create_time > '$lastGetTime'
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        } else {
            $sql = "SELECT comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' 
            and comment.create_user = users.id
            ORDER BY create_time DESC";
        }
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        
        $results = array();
        foreach ($rows as $row) {
            array_push($results, $row);
        }
        return $results;
    }

    protected function getCommentsByTargetReceiver($target_id, $lastGetTime)
    {
        $user = $this->user;
        if (strlen($lastGetTime) > 5) {
            $sql = "SELECT comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' and create_time > $lastGetTime
            and (create_user = '$user->id' or create_user = '$this->createrId')
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        } else {
            $sql = "SELECT comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' 
            and (create_user = '$user->id' or create_user = '$this->createrId')
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        }
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        
        $results = array();
        foreach ($rows as $row) {
            array_push($results, $row);
        }
        return $results;
    }
}

?>