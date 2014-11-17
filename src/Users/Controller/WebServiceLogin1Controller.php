<?php
/**
 * add a new function updatePersonalComment
 *
 *
 * @author liuyuhai
 *
 */
namespace Users\Controller;

use Users\Controller\WebServiceLoginController;
use Users\Tools\MyUtils;

class WebServiceLogin1Controller extends WebServiceLoginController
{

    public function updatePersonalCommentAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $sessionCode = $_POST["sessionCode"];
        $phoneNumber = $_POST["phoneNumber"];
        $flag = 'updatePersonalCommentAction';
        try {
            $this->getUserBySessionCode($sessionCode, $phoneNumber);
        } catch (\Exception $e) {
            $flag = "invalidUser";
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        $personalComment = $_POST["personalComment"];
        $currentTime = mktime();
        try {
            $this->updatePersonalComment($personalComment, $currentTime);
            $flag = 'successUpdateComment';
        } catch (\Exception $e) {
            $flag = "failedUpdateComment";
//         	   throw new \Exception($e);
        }
        
        $result = array(
            "flag" => $flag,
            "currentTime" => $currentTime,
        );
        return $this->returnJson($result);
    }

    protected function updatePersonalComment($personalComment, $currentTime)
    {
        $user = $this->user;
        if (strlen($personalComment) > 0) {
            // update the comment
            $adapter = $this->getAdapter();
            $sql = "update users set personalComment = '$personalComment', 
                lastSuccesTime = '$currentTime'
                where id = '$user->id'";
            $adapter->query($sql)->execute();
        } else {
            throw new \Exception("noComment");
        }
    }
}

?>