<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Users\Tools\MyUtils;
use Zend\Json\Json;
use Zend\Validator\EmailAddress;
use Users\Model\UserInfo;

class HelperController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    protected $adapter;


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
        }
        return $this->adapter;
    }

    protected function dealInvitationAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get inviter and action
        $emailValidation = new EmailAddress();
        if ($emailValidation->isValid($_GET['inviter'])) {
            if ($_GET['action'] == 'accept') {
                // accept the invitation
                try {
                    $this->acceptInvitation($email, $_GET['inviter']);
                    $flag = true;
                    $message = "accept the invitation";
                } catch (\Exception $e) {
                    $flag = false;
                    $message = 'can not accept the inviter';
                }
            } elseif ($_GET['action'] == 'reject') {
                // reject the invitation
                try {
                    $this->rejectInvitation($email, $_GET['inviter']);
                    $flag = true;
                    $message = "reject the invitation";
                } catch (\Exception $e) {
                    $flag = false;
                    $message = 'can not reject the inviter';
                }
            } else {
                $flag = false;
                $message = 'illeage action';
            }
        } else {
            // illeagal email
            $flag = false;
            $message = "illeagal email";
        }
        return $this->returnJson(array(
            'flag' => $flag,
            'message' => $message
        ));
    }

    /**
     *
     * @param unknown $email            
     * @param unknown $inviter            
     * @return boolean
     */
    protected function acceptInvitation($email, $inviter)
    {
        $sql = "update relationship set status = '1'
            where owner = '$inviter' and helper = '$email'";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        return true;
    }

    /**
     *
     * @param unknown $email            
     * @param unknown $inviter            
     * @return boolean
     */
    protected function rejectInvitation($email, $inviter)
    {
        $sql = "DELETE FROM relationship 
            WHERE owner = '$inviter' and helper = '$email'";
        
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        return true;
    }

    protected function getInvitationsAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        // get invitations by email
        try {
            $invitations = $this->getInvitationsByEmail($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'message' => $email . '--cannot get invitations',
                'flag' => false
            ));
        }
        // return the invitations
        
        return $this->returnJson(array(
            'flag' => true,
            'invitations' => $invitations
        ));
    }

    /**
     *
     * @param unknown $email            
     * @return multitype:
     */
    protected function getInvitationsByEmail($email)
    {
        $sql = "SELECT * from userInfo
            where email in
            (select owner from relationship
            where helper = '$email' and status = 2
            ORDER BY create_time)";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // push the result to a helpers array
        $invitations = array();
        foreach ($rows as $row) {
            // get message of the invitation
            try {
                $message = $this->getMessageOfInvitation($row['email'], $email);
            } catch (\Exception $e) {
                MyUtils::writelog("can not get message of " . $row['email'] . "--" . $email);
                throw new \Exception("can not get message" . $e);
            }
            // add the message to $row
            $row['message'] = $message['message'];
            // push the $row to array
            array_push($invitations, $row);
        }
        
        return $invitations;
    }

    /**
     *
     * @param unknown $owner            
     * @param unknown $helper            
     */
    protected function getMessageOfInvitation($owner, $helper)
    {
        $sql = "SELECT message from buildRelationshipMessage
            where owner = '$owner' and helper = '$helper'";
        $adapter = $this->getAdapter();
        
        $row = $adapter->query($sql)->execute();
        
        return $row->current();
    }

    protected function getApplyingHelpersAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        // get helper by email
        try {
            $helpers = $this->getApplyingHelpersByEmail($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'message' => 'cant get helpers',
                'flag' => false
            ));
        }
        // return the helper
        
        return $this->returnJson(array(
            'flag' => true,
            'helpers' => $helpers
        ));
    }

    /**
     *
     * @param unknown $email            
     * @return multitype:
     */
    protected function getApplyingHelpersByEmail($email)
    {
        // get applying email
        $sql = "select helper from relationship 
            where owner = '$email' and STATUS = '2' ORDER BY create_time";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        
        // prepare helpers array
        $helpers = array();
        // get userInfo by email
        if ($rows->count()) {
            foreach ($rows as $row) {
                $userInfo = $this->getHelperByEmail($row['helper']);
                array_push($helpers, $userInfo);
            }
        }
        return $helpers;
    }

    protected function addHelperAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $message = $_GET['message'];
        // check the helper and message
        if (! $this->isLeagalHelper($email, $_GET['email']) || ! MyUtils::isValidateAddress($message) || ! MyUtils::isValidateName($_GET['first_name'])) {
            // return false for illeagal helper application
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'illeagal helper'
            ));
        }
        
        // add the helper and email relationship
        try {
            $this->addHelper($email, $_GET['email'], $message);
        } catch (\Exception $e) {
            MyUtils::writelog($email . 'can not add helper--' . $_GET['email']);
            // return false
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cannot add the helper'
            ));
        }
        // set the default message for null message
        if (! $message) {
            $message = $_GET['first_name'] . " invite you as his/her helper.<br>" . $_GET['first_name'] . " 邀请你成为他或她的帮手。<br>";
        }
        
        // get helper infor
        $helper = $this->getHelperByEmail($_GET['email']);
        
        // send the message to the $email
        if (! $this->sendInvitationMessage($_GET['email'], $message)) {
            return $this->returnJson(array(
                'flag' => true,
                'message' => "fail to send the invitation message",
                'helper' => $helper
            ));
        }
        
        // return true
        return $this->returnJson(array(
            'flag' => true,
            'helper' => $helper
        ));
    }

    protected function getHelperByEmail($email)
    {
        // get userInfo
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        try {
            $userInfo = $userInfoTable->getUserInfoByEmail($email);
        } catch (\Exception $e) {
            // if not, new a userInfo
            $userInfo = new UserInfo();
            $userInfo->email = $email;
            $userInfo->filename = 'defaultphoto.jpg';
            $userInfo->thumbnail = 'defaultphoto.jpg';
        }
        // return the userInfo
        $userInfo = MyUtils::exchangeObjectToData($userInfo);
        return $userInfo;
    }

    protected function sendInvitationMessage($email, $message)
    {
        require_once 'vendor/PHPMailer-master/PHPMailerAutoload.php';
        require_once 'module/Users/src/Users/Tools/emailConfig.php';
        require_once 'module/Users/view/users/utils/user_label.php';
        // debug switch
        if ($emailConfig[MySwitch] == 'off') {
            return true;
        }
        $mail = new \PHPMailer(true);
        $mail->CharSet = 'utf-8';
        try {
            $to = $email;
            if (! \PHPMailer::validateAddress($to)) {
                throw new \phpmailerAppException("Email address " . $to . " is invalid -- aborting!");
            }
            $mail->isSMTP();
            $mail->SMTPDebug = $emailConfig[SMTPDebug];
            $mail->Host = $emailConfig[Host];
            $mail->Port = $emailConfig[Port];
            $mail->SMTPSecure = $emailConfig[SMTPSecure];
            $mail->SMTPAuth = $emailConfig[SMTPAuth];
            $mail->Username = $emailConfig[Username];
            $mail->Password = $emailConfig[Password];
            $mail->From = $emailConfig[From];
            $mail->addAddress($to);
            $mail->WordWrap = $emailConfig[WordWrap];
            
            // set content and subject
            $mail->FromName = $user_labels['fromName'];
            $mail->Subject = $user_labels['invitationSubject'];
            
            // create message with html
            $body = $message . $user_labels[weblink];
            $mail->msgHTML($body);
            $mail->AltBody = 'This is a plain-text message body';
            
            try {
                
                $mail->send();
            } catch (\Exception $e) {
                throw new \Exception('Unable to send to: ' . $to . ': ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return true;
    }

    /**
     *
     * @param string $owner            
     * @param string $helper            
     * @param string $message            
     */
    protected function addHelper($owner, $helper, $message)
    {
        // add the helper
        $sql = "insert into relationship (owner, helper, status) values ('$owner', '$helper', '2')";
        
        $adapter = $this->getAdapter();
        
        $adapter->query($sql)->execute();
        
        // add the message
        $sql = "insert into buildRelationshipMessage 
            (helper, owner, message)
            values('$helper', '$owner', '$message')";
        
        // $adapter = $this->getAdapter();
        
        $adapter->query($sql)->execute();
    }

    protected function isLeagalHelper($email, $apply)
    {
        // check the helper
        $emailValidation = new EmailAddress();
        if ($emailValidation->isValid($apply)) {
            // validate helper
            // can't build relation ship with self
            if ($apply != $email) {
                // has they connected or are they connecting
                try {
                    $result = ! $this->hasConnectedOrIsConnecting($email, $apply);
                } catch (\Exception $e) {
                    MyUtils::writelog($email . " failed to check the relationship with " . $apply);
                    $result = false;
                }
            } else {
                // can't build relation ship with self
                $result = false;
            }
        } else {
            // fail the email validation
            $result = false;
        }
        // return result
        return $result;
    }

    protected function isLeagalEmailAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $apply = $_GET['email'];
        
        $result = $this->isLeagalHelper($email, $apply);
        // change boolean result to string
        if ($result) {
            $result = 'true';
        } else {
            $result = 'false';
        }
        
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
    }

    /**
     *
     * @param string $email,
     *            owner
     * @param string $apply,
     *            applyer
     * @return boolean
     */
    protected function hasConnectedOrIsConnecting($email, $apply)
    {
        $sql = "SELECT * from relationship
                where (helper = '$email' and owner = '$apply')
                or (owner = '$email' and helper = '$apply')";
        
        $adapter = $this->getAdapter();
        
        $row = $adapter->query($sql)->execute();
        if ($row->count()) {
            // at least get a record
            return true;
        } else {
            // no record
            return false;
        }
    }

    protected function deleteHelperByEmailAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // check the helper
        $emailValidation = new EmailAddress();
        if ($emailValidation->isValid($_GET['helper'])) {
            // validate helper
            $helper = $_GET['helper'];
        } else {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate helper'
            ));
        }
        // delete the relationship
        try {
            $this->deleteHelperByEmail($email, $helper);
        } catch (\Exception $e) {
            MyUtils::writelog($email . " can not delete " . $helper);
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'can not delete the helper'
            ));
        }
        // return true
        return $this->returnJson(array(
            'flag' => true
        ));
    }

    /**
     *
     * @param string $email,
     *            owner
     * @param string $helper            
     */
    protected function deleteHelperByEmail($email, $helper)
    {
        $sql = "DELETE from relationship 
            where (helper = '$email' and owner = '$helper')
            OR (OWNER = '$email' and helper = '$helper')";
        
        $adapter = $this->getAdapter();
        
        $adapter->query($sql)->execute();
    }

    protected function getHelpersByOwnerAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // Owner's email or email get from web
        $emailValidation = new EmailAddress();
        $email = $emailValidation->isValid($_GET[email]) ? $_GET[email] : $email;
        
        // get helper by email
        try {
            $helpers = $this->getHelpersByEmail($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'message' => 'cant get helpers',
                'flag' => false
            ));
        }
        
        // pagenate the result
        // return the helper
        
        return $this->returnJson(array(
            'flag' => true,
            'helpers' => $helpers
        ));
    }

    /**
     *
     * @param string $email            
     * @return helpers array:
     */
    protected function getHelpersByEmail($email)
    {
        $sql = "select * from userInfo
            where email in 
            (SELECT helper from relationship
            where owner = '$email' and status = '1') 
            OR
            email in (SELECT owner from relationship
            where helper = '$email' and status = '1')
            ORDER BY first_name";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // push the result to a helpers array
        $helpers = array();
        foreach ($rows as $row) {
            array_push($helpers, $row);
        }
        
        return $helpers;
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
}
