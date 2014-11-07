<?php
namespace Users\Controller;

use Zend\Stdlib\ArrayUtils;
use Users\Model\User;
use Users\Tools\MyUtils;
use Users\Model\PushStack;
use Users\Tools\AndriodPush;

class PushManagementController extends CommController
{

    protected $user;

    public function indexAction()
    {
        $flag = "pushManagement";
        
        return $this->returnJson(array(
            "flag" => $flag
        ));
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

    public function startAction()
    {
        // verify user
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        if ($flag != "validUser") {
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        $user = $this->user;
        if ($user->phoneNumber == '1974071900') {
            // turn on switch
            try {
                $this->start();
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            // start push
            ignore_user_abort(); // 关掉浏览器，PHP脚本也可以继续执行.
            set_time_limit(0); // 通过set_time_limit(0)可以让程序无限制的执行下去
            $interval = 1; // 每隔1s运行
            $platForm = $_POST["platForm"];
            
            if ($platForm == "apple") {
                $flag = $this->repeatApplePushInfo($interval);
            } elseif ($platForm == "andriod") {
                // repeate andriod
                $flag = $this->repeatAndriodPushInfo($interval);
            } else {
                $flag = "which platform do you want?";
            }
        } else {
            $flag = "you are kiding";
        }
        
        // finish push
        return $this->returnJson(array(
            "flag" => $flag
        ));
    }

    public function stopAction()
    {
        require 'module/Users/src/Users/Tools/WebServiceAuthUser.php';
        if ($flag != "validUser") {
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        $user = $this->user;
        if ($user->phoneNumber == '1974071900') {
            try {
                $this->stop();
                $flag = "stopPushService";
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        } else {
            $flag = "you are kiding";
        }
        
        // finish push
        return $this->returnJson(array(
            "flag" => $flag
        ));
    }

    protected function stop()
    {
        $user = $this->user;
        // set swith table to off
        $sql = "INSERT INTO switch (switch, user_id) VALUES ('off', '$user->id')";
        $adapter = $this->getAdapter();
        $row = $adapter->query($sql)->execute();
    }

    protected function start()
    {
        $user = $this->user;
        // set swith table to off
        $sql = "INSERT INTO switch (switch, user_id) VALUES ('on', '$user->id')";
        $adapter = $this->getAdapter();
        $row = $adapter->query($sql)->execute();
    }

    protected function getFP()
    {
        $passphrase = 'rd123';
        $uploadPath = $this->getFileCertificationLocation();
        $ctx = stream_context_create();
        // $filename = $uploadPath . "/" . 'readyGoDevelop.pem';
        // $filename = $uploadPath . "/" . 'readyGoDistribution.pem';
        // $filename = $uploadPath . "/" . 'productpush.pem';
        $filename = $uploadPath . "/" . 'productCertBlueSky.pem';
        stream_context_set_option($ctx, 'ssl', 'local_cert', $filename);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        
        // Open a connection to the APNS server
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        // $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        
        if (! $fp) {
            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        }
        return $fp;
    }

    protected function isSwithOn()
    {
        $adapter = $this->getAdapter();
        $sql = "SELECT * FROM switch where id IN(SELECT MAX(id) FROM switch)";
        $rows = $adapter->query($sql)->execute();
        $switch = $rows->current()["switch"];
        if ($switch == 'on') {
            $flag = true;
        } else {
            $flag = false;
        }
        return $flag;
    }

    protected function repeatApplePushInfo($interval)
    {
        $flag = "startRepeatPush";
        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        $adapter = $this->getAdapter();
        $i = 0;
        do {
            // get push info
            $pushedInfos = array();
            try {
                
                try {
                    if (! $pushStackTable) {
                        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
                    }
                    $pushInfos = $this->getApplePush($pushStackTable);
                } catch (\Exception $e) {
                    $flag = "failedGetPushinfo";
                }
                // push info
                $pushedInfos = array();
                if (count($pushInfos) > 0) {
                    // open fp
                    $fp = $this->getFP();
                    
                    // if (! $fp) {
                    // throw new \Exception("noFP");
                    // }
                    foreach ($pushInfos as $pushInfo) {
                        // get target by id
                        if ($pushInfo->target_id > 10) {
                            // get target by id
                            try {
                                // $target = $this->getTargetById($pushInfo->target_id);
                                if (! $adapter) {
                                    $adapter = $this->getAdapter();
                                }
                                $target = MyUtils::getFullTargetById($pushInfo->target_id, $adapter);
                            } catch (\Exception $e) {
                                $target = null;
                            }
                            $body = $this->packPushInfoWithTarget($pushInfo, $target);
                        } else {
                            $body = $this->packPushInfoWithOutTarget($pushInfo);
                        }
                        $body[aps][badge] = (int) $body[aps][badge];
                        // Encode the payload as JSON
                        $payload = json_encode($body);
                        if (strlen($pushInfo->deviceToken) > 10) {
                            // Build the binary notification
                            $msg = chr(0) . pack('n', 32) . pack('H*', $pushInfo->deviceToken) . pack('n', strlen($payload)) . $payload;
                            
                            // Send it to the server
                            $result = fwrite($fp, $msg, strlen($msg));
                            
                            if (! $result) {
                                // echo 'Message not delivered' . PHP_EOL;
                                $flag = "messageNotDelivered";
                            } else {
                                // echo 'Message successfully delivered' . PHP_EOL;
                                $flag = "messageSuccessfullyDelivered";
                                array_push($pushedInfos, $pushInfo);
                            }
                        }
                    }
                    // close fp
                    if ($fp)
                        fclose($fp);
                        // update pushStack
                    try {
                        if (! $pushStackTable) {
                            $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
                        }
                        $this->updatePushInfo($pushedInfos, $pushStackTable);
                        $flag = "successfulUpdate";
                    } catch (\Exception $e) {
                        $flag = "failedUpdatePushInfos";
                    }
                }
            } catch (\Exception $e) {
                $i ++;
                if ($i > 10) {
                    $i = 0;
                    // send a text message to yuhai
                    if ($fp)
                        fclose($fp);
                    sleep(10);
                }
            }
            sleep($interval);
        } while ($this->isSwithOn());
        $flag = "finishPush";
        return $flag;
    }

    protected function repeatAndriodPushInfo($interval)
    {
        $flag = "startRepeatPush";
        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        do {
            // get push info
            $pushedInfos = array();
            try {
                if (! $pushStackTable) {
                    $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
                }
                $pushInfos = $this->getAndriodPush($pushStackTable);
            } catch (\Exception $e) {
                $flag = "failedGetPushinfo";
            }
            // push info
            if (count($pushInfos) > 0) {
                // push android info
                foreach ($pushInfos as $pushInfo) {
                    
                    try {
                        $this->pushAndroidInfo($pushInfo);
                    } catch (\Exception $e) {
                        
                        throw new \Exception($e);
                    }
                }
                try {
                    if (! $pushStackTable) {
                        $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
                    }
                    $this->updatePushInfo($pushInfos, $pushStackTable);
                    $flag = "successfulUpdate";
                } catch (\Exception $e) {
                    $flag = "failedUpdatePushInfos";
                }
            }
            // wait for a while
            sleep($interval);
        } while ($this->isSwithOn());
        return $flag;
    }


    protected function pushAndroidInfo($pushStack)
    {
        if ($pushStack->target_id > 10) {
            $adapter = $this->getAdapter();
            $target = MyUtils::getFullTargetById($pushStack->target_id, $adapter);
        }
        
        $andriodPush = new AndriodPush();
        $andriodPush->easyPush($target, $pushStack);
    }

    /**
     *
     * @param unknown $pushStack            
     * @param unknown $target            
     * @return multitype:string unknown NULL
     */
    protected function packPushInfoWithTarget(PushStack $pushStack, $target)
    {
        // print_r("badge = ".$pushStack->message);
        $body['aps'] = array(
            'alert' => $pushStack->message,
            'sound' => 'default',
            'badge' => $pushStack->notificationNumber,
            'category' => 'incomingCall',
            'target' => $target,
            'userStatus' => $pushStack->userStatus
        );
        // print_r("badge =".$pushStack->notificationNumber);
        return $body;
    }

    /**
     *
     * @param unknown $pushStack            
     * @return multitype:string NULL
     */
    protected function packPushInfoWithOutTarget($pushStack)
    {
        $body['aps'] = array(
            'alert' => $pushStack->message,
            'sound' => 'default',
            'badge' => $pushStack->notificationNumber,
            'category' => 'incomingCall',
            'userStatus' => $pushStack->userStatus
        );
        return $body;
    }

    /**
     *
     * @param unknown $pushStacks            
     */
    protected function updatePushInfo($pushStacks, $pushStackTable)
    {
        // $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        foreach ($pushStacks as $pushStack) {
            $pushStack->status = "delivered";
            $pushStackTable->updatePushStack($pushStack);
        }
        $pushStackTable = null;
    }

    /**
     * get push while the status = waiting
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function getApplePush($pushStackTable)
    {
        $rows = $pushStackTable->getApplePushByStatus("waiting");
        $array = array();
        foreach ($rows as $row) {
            array_push($array, $row);
        }
        $pushStackTable = null;
        return $array;
    }

    protected function getAndriodPush($pushStackTable)
    {
        $rows = $pushStackTable->getAndriodPushByStatus("waiting");
        $array = array();
        foreach ($rows as $row) {
            array_push($array, $row);
        }
        $pushStackTable = null;
        return $array;
    }

    protected function getAdapter()
    {
        if (! $this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
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
     *
     * @param unknown $target_id            
     * @return unknown
     */
    protected function getTargetById($target_id)
    {
        $sql = "select * from target
    	       where target_id = '$target_id'";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        $row = $rows->current();
        return $row;
    }
}
