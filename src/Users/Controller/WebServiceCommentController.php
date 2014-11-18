<?php
namespace Users\Controller;

use Users\Controller\WebServiceTargetController;
use Users\Model\Comment;
use Users\Tools\MyUtils;
use Users\Model\PushInfo;
use Users\Model\Notification;
use Users\Model\Target;
use Zend\Stdlib\ArrayUtils;

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
            date_default_timezone_set('PRC');
            $create_time = mktime();
            $this->createComment($comment, $target_id, $create_time);
            $flag = "successCreateComment";
        } catch (\Exception $e) {
            // throw new \Exception($e);
            $flag = "failedCreateComment";
        }
        return $this->returnJson(array(
            "flag" => $flag,
            "create_time" => $create_time,
            "comment_id" => $this->comment->id
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
            // is commentable target
            if (! $this->isCommentableTarget($target)) {
                throw new \Exception("uncommentableTarget");
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

    protected function isCommentableTarget(Target $target)
    {
        $user = $this->user;
        try {
            if ($target->target_end_time < mktime()) {
                throw new \Exception("overtime");
            }
            if ($target->target_creater != $user->id && ! $this->isMemberOfTarget($target->target_id)) {
                throw new \Exception("noRight");
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
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
        if (strlen($comment->comment) > 0) {
            $message = '您的目标“' . $target->target_name . '” 有一个进度更新“' . $comment->comment . '”。';
        }else if(strlen($comment->file_name) > 19){
            $message = '您的目标“' . $target->target_name . '” 有一个进度更新文件。';
        }else{
            throw new \Exception("messageError");
        }
        
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
        return $comments;
    }
    
    // protected function isCreaterOfTarget($target_id)
    // {
    // $user = $this->user;
    // $target = $this->getTargetById($target_id);
    // if ($user->id == $target->target_creater) {
    // return true;
    // } else {
    // $this->createrId = $target->target_creater;
    // return false;
    // }
    // }
    protected function getCommentsByTargetCreater($target_id, $lastGetTime)
    {
        if (strlen($lastGetTime) > 5) {
            $sql = "SELECT comment.id, comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' and create_time > '$lastGetTime'
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        } else {
            $sql = "SELECT comment.id, comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
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
            $sql = "SELECT comment.id, comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' and create_time > $lastGetTime
            and (create_user = '$user->id' or create_user = '$this->createrId')
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        } else {
            $sql = "SELECT comment.id, comment.comment, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
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

    public function fileUploadAction()
    {
        $flag = "fileUploadAction";
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
        
        $request = $this->getRequest();
        $uploadFile = $this->params()->fromFiles('comment_file');
        $target_id = $_POST["target_id"];
        
        $uploadPath = $this->getFileUploadLocation();
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setDestination($uploadPath);
        
        $adapter->addValidator('Extension', false, 'jpg, png, bmp, gif, jpeg');
        
        // Limit the size of all files to be uploaded to maximum 10MB and mimimum 20 bytes
        $adapter->addValidator('FilesSize', false, array(
            'min' => 1,
            'max' => '10MB'
        ));
        
        if ($adapter->isValid() && $adapter->isValid()) {
            // delete the old files
            
            if (strlen($this->user->fileName) > 2) {
                try {
                    $this->deleteOldImageFiles();
                    $result = true;
                } catch (\Exception $e) {
                    $result = false;
                }
            } else {
                $result = true;
            }
            if ($result) {
                try {
                    $this->NewUploadUserInfo($uploadFile, $adapter);
                    $flag = "successUpLoadFile";
                } catch (\Exception $e) {
                    $flag = "failedUpLoadFile";
                }
            } else {
                $flag = "failedDeleteOldFiles";
            }
        } else {
            $flag = "invalidFile";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    protected function fileUpload($uploadFile, $target_id)
    {
        $uploadPath = $this->getFileUploadLocation();
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setDestination($uploadPath);
        
        
        // Limit the size of all files to be uploaded to maximum 10MB and mimimum 20 bytes
        $adapter->addValidator('FilesSize', false, array(
            'min' => 1,
            'max' => '10MB'
        ));
        
        if ($adapter->isValid()) {
            //save the file and get a name
            $fileName = $this->uploadFile($uploadFile, $adapter, $uploadPath);
            //save the file name to comment table
            $comment = new Comment();
            $comment->create_time = mktime();
            $comment->create_user = $this->user->id;
            $comment->file_name = $fileName;
            $comment->target_id = $target_id;
            $this->saveComment($comment);
            //save comment to comment push
            $this->saveCommentToPushStack();
        } else {
            $flag = "invalidFile";
        }
    }

    protected function getFileUploadLocation()
    {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        if ($config instanceof \Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! empty($config['module_config']['file_comment_upload_location'])) {
            return $config['module_config']['file_comment_upload_location'];
        } else {
            return FALSE;
        }
    }
}

?>