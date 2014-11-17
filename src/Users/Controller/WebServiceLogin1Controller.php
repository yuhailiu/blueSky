<?php
namespace Users\src\Users\Controller;

use Users\Controller\WebServiceLoginController;
use Users\Tools\MyUtils;

class WebServiceLogin1Controller extends WebServiceLoginController
{

    public function updatePersonalCommentAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "updatePersonalComment";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                "flag" => "invalidUser"
            ));
        }
        
        $result = array(
        		"flag" => $flag,
        );
        return $this->returnJson($result);
    }
}

?>