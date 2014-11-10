<?php
namespace Users\Controller;

use Users\Controller\WebServiceTargetController;
use Users\Model\Comment;
use Users\Tools\MyUtils;

class WebServiceCommentController extends WebServiceTargetController
{

    protected $createrId;

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
            $obj->create_user = $user->id;
            $obj->create_time = $create_time;
            $commentTable = $this->getServiceLocator()->get('CommentTable');
            try {
                $commentTable->saveComment($obj);
            } catch (\Exception $e) {
                throw new \Exception("failedCreateComment");
            }
        } else {
            throw new \Exception("targetIdErro");
        }
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
            $sql = "SELECT `comment`, fileName, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' and create_time > '$lastGetTime'
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        } else {
            $sql = "SELECT `comment`, fileName, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' 
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

    protected function getCommentsByTargetReceiver($target_id, $lastGetTime)
    {
        $user = $this->user;
        if (strlen($lastGetTime) > 5) {
            $sql = "SELECT `comment`, fileName, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
            where target_id = '$target_id' and create_time > $lastGetTime
            and (create_user = '$user->id' or create_user = '$this->createrId')
            and `comment`.create_user = users.id
            ORDER BY create_time DESC";
        } else {
            $sql = "SELECT `comment`, fileName, target_id, create_time, users.phoneNumber as create_user from `comment` ,users
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