<?php
namespace Users\Controller;

use Users\Tools\MyUtils;
use Users\Model\User;
use Zend\Stdlib\ArrayUtils;
use Users\Model\PushInfo;

class WebServiceLoginController extends CommController
{

    protected $sesionCode;

    protected $hasLoginByOtherDevice;

    protected $user;

    public function indexAction()
    {
        $result = array(
            "flag" => "Hello",
            "sessionCode" => "whatADay",
            "result" => $result
        );
        return $this->returnJson($result);
    }

    protected function getUserBySessionCode($sessionCode)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserBySessionCode($sessionCode);
        return $user;
    }

    public function checkNewsAction()
    {
        $phoneNumber = $_POST["phoneNumber"];
        $phoneNumber = MyUtils::clearNumber($phoneNumber);
        $deviceCode = $_POST["deviceCode"];
        $deviceCode = str_replace(" ", "", $deviceCode);
        $areaCode = $_POST["areaCode"];
        $areaCode = MyUtils::clearNumber($areaCode);
        try {
            $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
        } catch (\Exception $e) {
            $flag = "noSuchUser";
            MyUtils::writelog("can not get user by phone=" . $phoneNumber);
        }
        if ($flag != "noSuchUser") {
            if (strlen($deviceCode) > 10) {
                if ($deviceCode == $user->deviceCode) {
                    $flag = "validUser";
                } else {
                    $flag = "invalidUser";
                }
            } else {
                $flag = "deviceCodeError";
            }
        }
        $result = array(
            "flag" => $flag,
            "deviceToken" => $user->deviceToken
        );
        return $this->returnJson($result);
    }

    public function newProcessAction()
    {
        $flag = "success";
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
        }
        
        return $this->returnJson(array(
            "flag" => $flag
        ));
    }

    public function processAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        
        $flag = "process";
        $sessionCode = "sessionCode";
        
        $phoneNumber = $_POST["phoneNumber"];
        $phoneNumber = MyUtils::clearNumber($phoneNumber);
        $password = $_POST["password"];
        $password = str_replace(" ", "", $password);
        $areaCode = $_POST["areaCode"];
        $areaCode = MyUtils::clearNumber($areaCode);
        $deviceCode = $_POST["deviceCode"];
        $deviceCode = str_replace(" ", "", $deviceCode);
        if (strlen($phoneNumber) < 5 || strlen($password) < 1) {
            $flag = "phoneNumbeOrpasswordError";
        } else {
            if (strlen($deviceCode) > 0) {
                try {
                    $flag = $this->checkUserStatus($phoneNumber, $areaCode, $password, $deviceCode);
                } catch (\Exception $e) {
                    $flag = "checkStatusError";
                }
            } else {
                $flag = "noDeviceCode";
            }
            
            $action = $_POST["action"];
            if ($action == "updateDeviceCode" && $flag = "loginByOtherDevice") {
                // update deviceCode
                $this->user->deviceCode = $deviceCode;
                try {
                    $this->updateUser($this->user);
                    $flag = "updateSuccess";
                } catch (\Exception $e) {
                    $flag = "updateDeviceCodeError";
                }
                // push userStatus
                try {
                    $this->pushUserStatus();
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
            }
        }
        $sessionCode = $this->sesionCode;
        $result = array(
            "flag" => $flag,
            "sessionCode" => $sessionCode ? $sessionCode : "noSessionCode",
            "deviceCode" => $this->user->deviceCode
        );
        return $this->returnJson($result);
    }

    protected function pushUserStatus()
    {
        $user = $this->user;
        if (strlen($user->deviceToken) > 5) {
            $deviceTokens = array();
            $deviceToken = array(
                "deviceToken" => $user->deviceToken
            // "notificationNumber" => $user->notificationNumber
                        );
            array_push($deviceTokens, $deviceToken);
            $pushInfo = new PushInfo();
            $pushInfo->deviceTokens = $deviceTokens;
            $pushInfo->message = "警告：您的用户已经被其他设备登录。如果不是您本人的操作，请尽快修改密码重新登录。";
            $pushInfo->uploadPath = $this->getFileCertificationLocation();
            $pushInfo->userStatus = "loginByOtherDevice";
            try {
                $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
                MyUtils::savePushInfo($pushInfo, $pushStackTable);
            } catch (\Exception $e) {
                $flag = "failedSavePushInfo";
                throw new \Exception($e);
            }
        }
    }

    protected function checkUserStatus($phoneNumber, $areaCode, $password, $deviceCode)
    {
        try {
            try {
                $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
            } catch (\Exception $e) {
                $flag = "notFind";
                return $flag;
            }
            
            if ($user->failedTimes > 5) {
                throw new \Exception("failedTimeOver");
            }
            $this->user = $user;
            
            if (strlen($user->sessionCode) > 20) {
                // $this->hasLoginByOtherDevice = true;
                $this->sesionCode = $user->sessionCode;
                // $this->user = $user;
            } else {
                // if there isn't a seessioncode, create a sessioncode, and save the code to the user
                // $this->hasLoginByOtherDevice = false;
                session_start();
                $this->sesionCode = session_id() . $areaCode . $phoneNumber;
                $user->sessionCode = $this->sesionCode;
                try {
                    $this->updateUser($user);
                } catch (\Exception $e) {
                    $flag = false;
                }
            }
        } catch (\Exception $e) {
            MyUtils::writelog("can not get user by phone=" . $phoneNumber);
            throw $e;
        }
        
        if ($user->password == md5($password)) {
            // reset user failedTimes
            $user->failedTimes = 0;
            try {
                $this->updateUser($user);
            } catch (\Exception $e) {
                throw $e;
            }
            // valid user
            if ($user->deviceCode == $deviceCode) {
                // same device
                $flag = "success";
            } else {
                // other device
                $flag = "loginByOtherDevice";
            }
        } else {
            // update the failed times
            $result = $this->updateFailedTimes();
            if ($result == "successUpdateUser") {
                $flag = "failed";
            } else {
                $flag = $result;
            }
        }
        return $flag;
    }

    protected function updateFailedTimes()
    {
        $user = $this->user;
        $user->failedTimes ++;
        try {
            $this->updateUser($user);
            $flag = "successUpdateUser";
        } catch (\Exception $e) {
            $flag = "failedUpdateUser";
        }
        return $flag;
    }

    protected function isValidateUser($password, $phoneNumber, $areaCode)
    {
        $flag = true;
        $user = new User();
        try {
            $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
        } catch (\Exception $e) {
            MyUtils::writelog("can not get user by phone=" . $phoneNumber);
            $flag = false;
        }
        if ($user->password == md5($password)) {
            // if there is a sessioncode, warning the user
            if (strlen($user->sessionCode) > 20) {
                $this->hasLoginByOtherDevice = true;
                $this->sesionCode = $user->sessionCode;
                $this->user = $user;
            } else {
                // if there isn't a seessioncode, create a sessioncode, and save the code to the user
                $this->hasLoginByOtherDevice = false;
                session_start();
                $this->sesionCode = session_id() . $areaCode . $phoneNumber;
                $user->sessionCode = $this->sesionCode;
                try {
                    $this->updateUser($user);
                } catch (\Exception $e) {
                    $flag = false;
                }
            }
        } else {
            $flag = false;
        }
        return $flag;
    }

    /**
     * void
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
     * @param string $phoneNumber            
     * @return User
     */
    protected function getUserByPhoneNumber($phoneNumber, $areaCode)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserByPhoneNumber($phoneNumber, $areaCode);
        return $user;
    }

    public function resetpasswordAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "failed";
        $phoneNumber = $_POST['phoneNumber'];
        $phoneNumber = MyUtils::clearNumber($phoneNumber);
        $areaCode = $_POST["areaCode"];
        $areaCode = MyUtils::clearNumber($areaCode);
        
        if (MyUtils::isValidateTel($phoneNumber) && MyUtils::isValidateAreaCode($areaCode)) {
            $user = new User();
            try {
                $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
            } catch (\Exception $e) {
                $flag = "noFindUser";
                MyUtils::writelog("can not get user by phone=" . $phoneNumber);
            }
            $flag = $this->resetPassword($phoneNumber, $areaCode);
            if ($flag == "success") {
                if ($this->sendResetMessage($this->user->password)) {
                    try {
                        $this->recordTextMessage($phoneNumber);
                        $flag = "success";
                    } catch (\Exception $e) {
                        $flag = "recordError";
                    }
                } else {
                    $flag = "failedSendTextMessage";
                }
            }
        } else {
            $flag = "wrongPhoneNumber";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    protected function resetPassword($phoneNumber, $areaCode)
    {
        if ($areaCode == "86") {
            $user = new User();
            try {
                $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
            } catch (\Exception $e) {
                $flag = "noFindUser";
            }
            if ($user->id > 0) {
                $this->user = $user;
                // if last time is over 5min
                date_default_timezone_set('Asia/Shanghai');
                $currentTime = (string) mktime();
                $lastTimeOver5min = $user->lastSuccesTime + 300;
                if ($currentTime > $lastTimeOver5min) {
                    $newPassword = rand(100000, 999999);
                    try {
                        $this->changePassword($newPassword);
                        $this->user->password = $newPassword;
                        $flag = "success";
                    } catch (\Exception $e) {
                        $flag = "failedUpdatePassword";
                    }
                } else {
                    $flag = "canntResetIn5min";
                }
            } else {
                $flag = "noSuchUser";
            }
        } else {
            $flag = "noChina";
        }
        return $flag;
    }

    protected function recordTextMessage($phoneNumber)
    {
        $adapter = $this->getAdapter();
        $sql = "INSERT textMessage SET textMessage.phoneNumber = '$phoneNumber'";
        $rows = $adapter->query($sql)->execute();
        return $rows->getAffectedRows();
    }

    protected function sendResetMessage($newPassword)
    {
        $user = $this->user;
        $content = "您的重置的密码为：$newPassword" . "。 请不要分享给他人。";
        $content = urlencode($content);
        $url = "http://utf8.sms.webchinese.cn/?Uid=ReadyGo&Key=ee448fe2dd92fa4ad8a4&smsMob=" . $user->phoneNumber . "&smsText=$content";
        $result = $this->getTextMessage($url);
        if ($result == "1") {
            return true;
        } else {
            return false;
        }
    }

    protected function getTextMessage($url)
    {
        if (function_exists('file_get_contents')) {
            $file_contents = file_get_contents($url);
        } else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        return $file_contents;
    }

    public function changePasswordAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "changePassword";
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $newPassword = $_POST['newPassword'];
        if (MyUtils::isValidatePassword($newPassword)) {
            try {
                $this->changePassword($newPassword);
                $flag = "successChangePassword";
            } catch (\Exception $e) {
                $flag = "failedChangePassword";
            }
        } else {
            $flag = "invalidNewPassword";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    protected function changePassword($newPassword)
    {
        $user = $this->user;
        $newPassword = md5($newPassword);
        date_default_timezone_set('Asia/Shanghai');
        $currentTime = (string) mktime();
        $sql = "update users SET `password` = '$newPassword', users.lastSuccesTime = '$currentTime',
            users.failedTimes = '0'
            WHERE users.id = '$user->id'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
    }

    protected function getFileUploadLocation()
    {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        if ($config instanceof \Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! empty($config['module_config']['image_upload_location'])) {
            return $config['module_config']['image_upload_location'];
        } else {
            return FALSE;
        }
    }

    public function imgeuploadAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "imageUpLoad";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $request = $this->getRequest();
        $uploadFile = $this->params()->fromFiles('imageUpload');
        
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

    protected function deleteOldImageFiles()
    {
        $user = $this->user;
        $uploadPath = $this->getFileUploadLocation();
        $fileName = $uploadPath . "/" . "$user->fileName";
        // delete original file
        $result = MyUtils::deleteFile($fileName);
        if (strlen($user->thumbnail) > 2) {
            // delete tn file
            $tnFileName = $uploadPath . "/" . "$user->thumbnail";
            $result1 = MyUtils::deleteFile($tnFileName);
        }
    }

    protected function uploadUserInfo($uploadFile, $adapter)
    {
        $path = $this->getFileUploadLocation();
        // get user by email
        $user = $this->user;
        // creater the filename
        $sysName = "img" . $user->id;
        
        $adapter->addFilter('Rename', $sysName, $uploadFile['name']);
        
        // Save the update photo
        if ($adapter->receive($uploadFile["name"])) {
            // genarate the thumbfile and give the name to userInfo
            $user->thumbnail = $this->generateThumbnail($sysName);
            
            // update the DB if it's a new file
            if ($sysName != $user->fileName) {
                $user->filename = $sysName;
                $userTable = $this->getServiceLocator()->get('UserTable');
                try {
                    $userTable->updateUserById($user);
                } catch (\Exception $e) {
                    MyUtils::writelog("can't write userinfo to DB from upload controller" . $e);
                    throw new \Exception($e);
                }
            }
        } else {
            throw new \Exception("can't write the file to filesystem");
        }
    }

    /**
     *
     * @param file $uploadFile            
     * @param
     *            file adapter $adapter
     * @throws \Exception
     */
    protected function NewUploadUserInfo($uploadFile, $adapter)
    {
        $path = $this->getFileUploadLocation();
        // get user by email
        $user = $this->user;
        // creater the filename
        $sysName = MyUtils::getRandChar(20);
        
        $adapter->addFilter('Rename', $sysName, $uploadFile['name']);
        
        // Save the update photo
        if ($adapter->receive($uploadFile["name"])) {
            // genarate the thumbfile and give the name to userInfo
            $user->thumbnail = $this->generateThumbnail($sysName);
            
            // update the DB if it's a new file
            if ($sysName != $user->fileName) {
                $user->filename = $sysName;
                $userTable = $this->getServiceLocator()->get('UserTable');
                try {
                    $userTable->updateUserById($user);
                } catch (\Exception $e) {
                    MyUtils::writelog("can't write userinfo to DB from upload controller" . $e);
                    throw new \Exception($e);
                }
            }
        } else {
            throw new \Exception("can't write the file to filesystem");
        }
    }

    /**
     * generate thumbfile from image
     *
     * @param string $imageFileName            
     * @return string thumbfile name
     */
    protected function generateThumbnail($imageFileName)
    {
        $thumbnailFileName = 'tn_' . $imageFileName;
        $path = $this->getFileUploadLocation();
        $sourceImageFileName = $path . '/' . $imageFileName;
        $imageThumb = $this->getServiceLocator()->get('WebinoImageThumb');
        $thumb = $imageThumb->create($sourceImageFileName, $options = array());
        $thumb->resize(30, 30);
        $thumb->save($path . '/' . $thumbnailFileName);
        
        return $thumbnailFileName;
    }

    public function getimgebyuserAction()
    {
        $flag = "getimgebyuserAction";
        $sessionCode = $_GET["sessionCode"] ? $_GET["sessionCode"] : $_POST["sessionCode"];
        $sessionCode = str_replace(" ", "", $sessionCode);
        if (strlen($sessionCode) > 10) {
            try {
                $this->user = $this->getUserBySessionCode($sessionCode);
                if ($this->user->id > 10) {
                    $photoUser = $this->user;
                    $flag = "validUser";
                }
            } catch (\Exception $e) {
                $flag = "noFindUserBySessionCode";
                $result = array(
                    "flag" => $flag
                );
                return $this->returnJson($result);
            }
        } else {
            $flag = "invalidSession";
            $result = array(
                "flag" => $flag
            );
            return $this->returnJson($result);
        }
        if ($flag == "validUser") {
            $phoneNumber = $_GET["phoneNumber"] ? $_GET["phoneNumber"] : $_POST["phoneNumber"];
            $phoneNumber = MyUtils::clearNumber($phoneNumber);
            if (strlen($phoneNumber) > 5) {
                // check the photoUser is the helper of user
                // if ($this->isHelperOfUser($this->user->id, $phoneNumber)) {
                try {
                    $photoUser = $this->getUserByPhoneNumberOnly($phoneNumber);
                } catch (\Exception $e) {
                    $flag = "noSuchUser";
                    $result = array(
                        "flag" => $flag
                    );
                    return $this->returnJson($result);
                }
                // }
            }
            
            // Fetch Configuration from Module Config
            $uploadPath = $this->getFileUploadLocation();
            $thumb = $_GET["thumb"];
            if ($thumb == "YES") {
                $filename = $uploadPath . "/" . $photoUser->thumbnail;
            } else {
                $filename = $uploadPath . "/" . $photoUser->fileName;
            }
            // get the file
            $file = file_get_contents($filename);
            
            if ($file) {
                return $this->returnFileResponse($file, "img");
            } else {
                $flag = "noImageOfThePhoneNumber";
            }
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    public function newGetImgeByUserAction()
    {
        $flag = "getimgebyuserAction";
        $sessionCode = $_GET["sessionCode"] ? $_GET["sessionCode"] : $_POST["sessionCode"];
        $sessionCode = str_replace(" ", "", $sessionCode);
        if (strlen($sessionCode) > 10) {
            try {
                $this->user = $this->getUserBySessionCode($sessionCode);
                if ($this->user->id > 10) {
                    $photoUser = $this->user;
                    $flag = "validUser";
                }
            } catch (\Exception $e) {
                $flag = "noFindUserBySessionCode";
                $result = array(
                    "flag" => $flag
                );
                return $this->returnJson($result);
            }
        } else {
            $flag = "invalidSession";
            $result = array(
                "flag" => $flag
            );
            return $this->returnJson($result);
        }
        if ($flag == "validUser") {
            $filename = $_GET["fileName"];
            if ($_GET["thumb"] == "YES") {
                $filename = "tn_" . $filename;
            }
            $uploadPath = $this->getFileUploadLocation();
            $filenameWithPath = $uploadPath . "/" . $filename;
            // get the file
            $file = file_get_contents($filenameWithPath);
            
            if ($file) {
                return $this->returnFileResponse($file, $filename);
            } else {
                $flag = "noImageOfThePhoneNumber";
            }
        }
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    protected function isHelperOfUser($user_id, $helperPhoneNumber)
    {
        try {
            $adapter = $this->getAdapter();
            $sql = "SELECT * from relationship
                WHERE `owner` = '$user_id' and helper = '$helperPhoneNumber' and status = '1'";
            $rows = $adapter->query($sql)->execute();
            $rowsCount = $rows->count();
        } catch (\Exception $e) {
            $rowsCount = 0;
        }
        if ($rowsCount > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * put the file to reponse
     *
     * @param File $file            
     * @return response
     * @author yuhai liu
     */
    protected function returnFileResponse($file, $fileName)
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => "attachment;filename=$fileName"
        ));
        $response->setContent($file);
        return $response;
    }

    /**
     * update device token
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function updateUserDeviceTokenAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "updateUserDeviceToken";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $deviceToken = $_POST["deviceToken"] ? str_replace(" ", "", $_POST["deviceToken"]) : "";
        if (strlen($deviceToken) > 4) {
            $version = $_POST["version"];
            try {
                //$flag = $this->updateUserDeviceToken($deviceToken);
                $flag = $this->updateUserDeviceTokenAndVersion($deviceToken, $version);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        } else {
            $flag = "noDeviceToken";
        }
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
    }

    /**
     *
     * @param String $deviceToken            
     * @return string $flag
     */
    protected function updateUserDeviceToken($deviceToken)
    {
        // if has the deviceToken, delete it
        try {
            $this->clearDevicToken($deviceToken);
        } catch (\Exception $e) {
            throw new \Exception("failedClearDeviceToken");
        }
        
        $this->user->deviceToken = $deviceToken;
        try {
            $this->updateUser($this->user);
            $flag = "successUpdateDeviceToken";
        } catch (\Exception $e) {
            $flag = "failedUpdateDeviceToken";
        }
        return $flag;
    }
    /**
     * 
     * @param unknown $deviceToken
     * @throws \Exception
     * @return string
     */
    protected function updateUserDeviceTokenAndVersion($deviceToken, $version)
    {
        // if has the deviceToken, delete it
        try {
            $this->clearDevicToken($deviceToken);
        } catch (\Exception $e) {
            throw new \Exception("failedClearDeviceToken");
        }
        
        $this->user->deviceToken = $deviceToken;
        $this->user->version = $version;
        try {
            $this->updateUser($this->user);
            $flag = "successUpdateDeviceToken";
        } catch (\Exception $e) {
            $flag = "failedUpdateDeviceToken";
        }
        return $flag;
    }

    protected function clearDevicToken($deviceToken)
    {
        $sql = "UPDATE users set deviceToken = '0' WHERE deviceToken = '$deviceToken'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows;
    }

    public function updateUserNotificationNumberAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "updateUserNotificationNumber";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        $notificationNumber = str_replace(" ", "", $_POST["notificationNumber"]);
        if (strlen($notificationNumber) > 0) {
            $this->user->notificationNumber = $notificationNumber;
            try {
                $this->updateUser($this->user);
                $flag = "successNotification";
            } catch (\Exception $e) {
                $flag = "failedUpdateNotification";
            }
        } else {
            $flag = "noNotificationNumber";
        }
        
        $result = array(
            "flag" => $flag
        );
        return $this->returnJson($result);
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

    public function getHelperFileNameAction()
    {
        MyUtils::inspector();
        MyUtils::inspector1();
        $flag = "getHelperFileNameAction";
        
        try {
            $this->authrizeUser($_POST["password"], $_POST["phoneNumber"], $_POST["areaCode"]);
        } catch (\Exception $e) {
            $flag = $e->getMessage();
            return $this->returnJson(array(
                "flag" => $flag
            ));
        }
        
        // get helper
        $helperPhoneNumber = $_POST["helperPhoneNumber"];
        $helperPhoneNumber = MyUtils::clearNumber($helperPhoneNumber);
        if (strlen($helperPhoneNumber) < 5) {
            $helper = $this->user;
            $flag = "successfulGetHelper";
        } else {
            if ($this->isHelperOfUser($this->user->id, $helperPhoneNumber)) {
                try {
                    $helper = $this->getUserByPhoneNumberOnly($helperPhoneNumber);
                    $flag = "successfulGetHelper";
                } catch (\Exception $e) {
                    $flag = "failedGetHelper";
                }
            } else {
                $flag = "noPermition";
            }
        }
        // return helper fileName
        return $this->returnJson(array(
            "flag" => $flag,
            "fileName" => $helper->fileName
        ));
    }
}

?>