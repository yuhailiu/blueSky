<?php
namespace Users\Controller;

use Users\Controller\CommController;

class CheckNewestVersionController extends CommController
{

    public function indexAction()
    {
        // get platform
        $platForm = $_POST["platForm"];
        if (strlen($platForm) > 2) {
            if ($platForm == 'android') {
                $version = "1.0.4";
            }else{
                $version = "no such platForm";
            }
        } else {
            $version = "what do you want?";
        }
        
        // return json result
        return $this->returnJson(array(
            "version" => $version
        ));
    }
}

?>