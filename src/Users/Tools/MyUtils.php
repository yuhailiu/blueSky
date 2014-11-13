<?php
namespace Users\Tools;

use Zend\Validator\Regex;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Zend\Db\Adapter\Adapter;
use Users\Model\PushInfo;
use Users\Model\PushStack;

class MyUtils
{

    public function __construct()
    {}

    public static function inspector()
    {
        date_default_timezone_set('UTC');
    }

    public static function inspector1()
    {}

    public static function getFullTargetById($id, $adapter)
    {
        $sql = "SELECT DISTINCT target.target_end_time, target.target_name, target.target_id,
    	targetMembers.member_status as target_status, target.target_content,
    	users.phoneNumber as target_creater, target.eventIdentifier, target.target_create_time,
    	target_lastModify_time as last_update_time,targetMembers.members_id as receiver
    	from target , targetMembers, users
    	WHERE target.target_id = '$id'
    	and target.target_id = targetMembers.target_id
    	and target.target_creater = users.id";
        
        // $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $row = $rows->current();
        if ($rows->count() == 1) {
            // one member
            $memberId = $row["receiver"];
            $sql = "SELECT users.phoneNumber from users where users.id = '$memberId'";
            $memberPhones = $adapter->query($sql)->execute();
            $memberPhone = $memberPhones->current();
            $row["receiver"] = $memberPhone["phoneNumber"];
        } else {
            // 2 members or more
            $row["receiver"] = '007';
        }
        
        return $row;
    }

    /**
     *
     * @param PushInfo $pushInfo            
     * @param unknown $pushStackTable            
     */
    public static function savePushInfo(PushInfo $pushInfo, $pushStackTable)
    {
        $deviceTokens = $pushInfo->deviceTokens;
        // $pushStackTable = $this->getServiceLocator()->get('PushStackTable');
        foreach ($deviceTokens as $deviceToken) {
            $pushStack = new PushStack();
            $pushStack->deviceToken = $deviceToken["deviceToken"];
            $pushStack->message = $pushInfo->message;
            $pushStack->notificationNumber = $deviceToken["notificationNumber"];
            $pushStack->status = "waiting";
            $pushStack->target_id = $pushInfo->target;
            $pushStack->comment_id = $pushInfo->comment_id;
            $pushStack->userStatus = $pushInfo->userStatus;
            if (strlen($pushStack->deviceToken) > 5) {
                $pushStackTable->savePushStack($pushStack);
            }
        }
    }

    /**
     * delete file by file name
     *
     * @param string $fileName            
     * @return boolean
     */
    public static function deleteFile($fileName)
    {
        if (file_exists($fileName)) {
            $result = unlink($fileName);
            return $result;
        }
    }

    /**
     * generate a string of length
     *
     * @param int $length            
     * @return String
     */
    public static function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        
        for ($i = 0; $i < $length; $i ++) {
            $str .= $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * if only a-z or A-Z or chinese words in 3-18 return true
     *
     * @param String $name            
     * @return boolean
     * @author liuyuhai 2014-1-26
     */
    public static function isValidateName($name)
    {
        $validator = new Regex(array(
            'pattern' => '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9]|[\s]|[-]){1,140}$/u'
        ));
        $flag = $validator->isValid($name);
        return $flag;
    }

    /**
     * validate address, allow space and comm
     *
     * @param String $address            
     * @return boolean
     */
    public static function isValidateAddress($address)
    {
        $validator = new Regex(array(
            'pattern' => '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9]|[,]|[%]|[％]|[$]|[¥]|[\']|[，]|[.]|[。]|[:]|[：]|[；]|[?]|[？]|[\s]){0,140}$/u'
        ));
        $flag = $validator->isValid($address);
        return $flag;
    }

    /**
     *
     * @param unknown $status            
     * @return boolean
     */
    public static function isValidateStatus($status)
    {
        $status = (int) $status;
        if (0 <= $status && $status < 4) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param unknown $content            
     * @return boolean
     */
    public static function isValidateContent($content)
    {
        $validator = new Regex(array(
            'pattern' => '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9]|[,]|[，]|[.]|[。]|[:]|[：]|[；]|[?]|[？]|[\s]){0,140}$/u'
        ));
        $flag = $validator->isValid($content);
        return $flag;
    }

    /**
     * 1-22 charactors
     *
     * @param string $password            
     * @return boolean
     */
    public static function isValidatePassword($password)
    {
        $validator = new Regex(array(
            'pattern' => '/^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{1,22}$/u'
        ));
        $flag = $validator->isValid($password);
        return $flag;
    }

    public static function changeStringtoArray($strings)
    {
        $strings = str_replace(" ", "", $strings);
        $array = array();
        foreach (explode(',', $strings) as $string) {
            array_push($array, $string);
        }
        return $array;
    }

    /**
     *
     * @param string $message            
     * @throws \Exception
     */
    public static function writelog($message)
    {
        try {
            $writer = new Stream('data/log/logfile');
            if (! $writer) {
                throw new \Exception('Failed to create writer');
            }
            $logger = new Logger();
            $logger->addWriter($writer);
            date_default_timezone_set('Asia/Shanghai');
            $logger->info($message);
        } catch (\Exception $e) {
            throw new \Exception("can't write the log", $e);
        }
    }

    /**
     * throw exception
     *
     * @return mysqli
     */
    public static function getDB_connection()
    {
        $config = require 'config/autoload/mysqli_config.php';
        $conn = new \mysqli($config['hostname'], $config['username'], $config['password'], $config['dbname']) or die("Error coonecting to Mysql server");
        $conn->query("set names utf8");
        return $conn;
    }

    public static function getBD_adapte()
    {
        $config = require 'config/autoload/mysqli_config.php';
        $adapter = new Adapter(array(
            'driver' => 'Mysqli',
            'database' => $config['dbname'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
        $adapter->query("set names utf8")->execute();
        return $adapter;
    }

    /**
     * return false if the telephone no doesn't match '/(^(\d{3,4}-)?\d{7,8})$|(1[0-9][0-9]{9})$|(^(\d{3,4}-)?\d{7,8}-)/'
     *
     * @param string $tel            
     * @return boolean
     */
    public static function isValidateTel($tel)
    {
        if ($tel) {
            $validator = new Regex(array(
                'pattern' => '/^[0-9]{5,20}$/'
            ));
            $flag = $validator->isValid($tel);
        }
        
        return $flag;
    }

    /**
     *
     * @param unknown $areaCode            
     * @return boolean
     */
    public static function isValidateAreaCode($areaCode)
    {
        $flag = true;
        
        if (strlen($areaCode) > 1 && strlen($areaCode) < 6) {
            $flag = true;
        } else {
            $flag = false;
        }
        
        return $flag;
    }

    /**
     * return false if the website doesn't match /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/
     *
     * @param string $website            
     * @return boolean
     */
    public static function isValidateWebsite($website)
    {
        $validator = new Regex(array(
            'pattern' => '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/'
        ));
        return $validator->isValid($website);
    }

    /**
     * exchange the data to object and return the new object
     *
     * @param array $data            
     * @param Object $object            
     * @return Object
     */
    public static function exchangeDataToObject($data, $object)
    {
        foreach ($object as $key => $value) {
            $object->$key = (isset($data[$key])) ? $data[$key] : null;
        }
        return $object;
    }

    /**
     * exchange an object to an array
     *
     * @param Object $object            
     * @return array $data
     */
    public static function exchangeObjectToData($object)
    {
        foreach ($object as $key => $value) {
            $data[$key] = $value;
        }
        
        return $data;
    }

    public static function isRightStatus($status)
    {
        $statuses = array(
            'create',
            'reject',
            'agree',
            'deleteByCreater',
            'deleteByCreaterAgain',
            'pause',
            'block'
        );
        return in_array($status, $statuses);
    }

    public static function clearNumber($number)
    {
        $number = trim($number);
        $number = preg_replace("/\\D/", "", $number);
        return $number;
    }

    public static function deletePhoneNumber86($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);
        $pattern = '/^((\+86)|(86)|(0086))/';
        $phoneNumber = preg_replace($pattern, '', $phoneNumber);
        if (strlen($phoneNumber) < 5) {
            $phoneNumber = null;
        }
        return $phoneNumber;
    }
    
    // /**
    // * push message to iPhones
    // *
    // * @param array $deviceTokens
    // * @param string $message
    // * @throws \Exception
    // * @return string messageNotDelivered messageSuccessfullyDelivered
    // */
    // public static function readyGoPushNotification($deviceTokens, $message, $uploadPath, $target)
    // {
    // $flag = "pushNotification";
    // $passphrase = '123456';
    
    // // Put your alert message here:
    // $ctx = stream_context_create();
    // $filename = $uploadPath . "/" . 'ck-readyGo.pem';
    // stream_context_set_option($ctx, 'ssl', 'local_cert', $filename);
    // stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    
    // // Open a connection to the APNS server
    // $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
    
    // if (! $fp) {
    // throw new \Exception("Failed to connect");
    // }
    // foreach ($deviceTokens as $deviceTokenWithNumber) {
    // // Create the payload body
    // $body['aps'] = array(
    // 'alert' => $message,
    // 'sound' => 'default',
    // 'badge' => $deviceTokenWithNumber['notificationNumber'],
    // 'category' => 'incomingCall',
    // 'target' => $target
    // );
    // // Encode the payload as JSON
    // $payload = json_encode($body);
    
    // // Build the binary notification
    // $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokenWithNumber['deviceToken']) . pack('n', strlen($payload)) . $payload;
    
    // // Send it to the server
    // $result = fwrite($fp, $msg, strlen($msg));
    
    // if (! $result)
    // // echo 'Message not delivered' . PHP_EOL;
    // $flag = "messageNotDelivered";
    // else
    // // echo 'Message successfully delivered' . PHP_EOL;
    // $flag = "messageSuccessfullyDelivered";
    // }
    
    // // Close the connection to the server
    // fclose($fp);
    // return $flag;
    // }
    
    /**
     * push message to iPhones
     *
     * @param array $deviceTokens            
     * @param string $message            
     * @throws \Exception
     * @return string messageNotDelivered messageSuccessfullyDelivered
     */
    public static function newReadyGoPushNotification(PushInfo $pushInfo)
    {
        $flag = "pushNotification";
        // print_r("pushNotification = ".$flag);
        // $passphrase = '123456';
        $passphrase = 'rdhaisheng';
        
        // Put your alert message here:
        $ctx = stream_context_create();
        // $filename = $pushInfo->uploadPath . "/" . 'readyGoDevelop.pem';
        $filename = $pushInfo->uploadPath . "/" . 'productpush.pem';
        stream_context_set_option($ctx, 'ssl', 'local_cert', $filename);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        
        // Open a connection to the APNS server
        // $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        // print_r("fp = ".$fp);
        if (! $fp) {
            throw new \Exception("Failed to connect");
        }
        foreach ($pushInfo->deviceTokens as $deviceTokenWithNumber) {
            // Create the payload body
            $body['aps'] = array(
                'alert' => $pushInfo->message,
                'sound' => 'default',
                'badge' => $deviceTokenWithNumber['notificationNumber'],
                'category' => 'incomingCall',
                // 'target' => $pushInfo->target,
                'userStatus' => $pushInfo->userStatus
            );
            // Encode the payload as JSON
            $payload = json_encode($body);
            // print_r("payload from old".$payload);
            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokenWithNumber['deviceToken']) . pack('n', strlen($payload)) . $payload;
            
            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
            
            print_r($result);
            
            if (! $result)
                // echo 'Message not delivered' . PHP_EOL;
                $flag = "messageNotDelivered";
            else
                // echo 'Message successfully delivered' . PHP_EOL;
                $flag = "messageSuccessfullyDelivered";
        }
        
        // Close the connection to the server
        fclose($fp);
        return $flag;
    }

    public static function stop($action, $adapter, $user)
    {
        // set swith table to off
        $sql = "INSERT INTO switch (switch, user_id, action) VALUES ('off', '$user->id','$action')";
        $row = $adapter->query($sql)->execute();
    }

    public static function start($action, $adapter, $user)
    {
        // set swith table to off
        $sql = "INSERT INTO switch (switch, user_id, action) VALUES ('on', '$user->id', '$action')";
        $row = $adapter->query($sql)->execute();
    }
}

?>