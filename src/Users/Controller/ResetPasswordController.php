<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Zend\Json\Json;
use Zend\Validator\EmailAddress;
use Users\Form\ConfirmEmailForm;

class ResetPasswordController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    protected function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    /**
     * get email and captcha
     * reset the password
     * flag 1 success 0 failed
     *
     * @return Json $result
     */
    protected function resetPasswordAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/resetPassword');
        }
        // init flag and message
        $flag = 1;
        $message = "success";
        $validate = new EmailAddress();
        $result = array();
        
        // get data from page and verify
        $post = $this->request->getPost();
        if (! $validate->isValid($post->email)) {
            $flag = 0;
            $message = "email format is invalidate";
        } elseif ($post->password != $post->confirmPassword) {
            $flag = 0;
            $message = "two passwords are not same";
        } elseif (! MyUtils::isValidatePassword($post->password)) {
            $flag = 0;
            $message = "password format is invalidate";
        }
        
        if ($flag == 0) {
            $result = array(
                'flag' => $flag,
                'message' => $message
            );
            
            return $this->returnJson($result);
        }
        
        // get reset the password where captcha and email
        $captcha = (int) $post->captcha;
        $userTable = $this->getServiceLocator()->get('UserTable');
        
        try {
            $user = $userTable->getUserByEmail($post->email);
            if ($captcha != $user->captcha) {
                throw new \Exception("captchas are not match");
            }
            //update user with new password
            //$userTable->updatePasswordByEmail($post->email, $post->password);
            $user->password = md5($post->password);
            $user->failedTimes = 0;
            $userTable->updateUser($user);
            
            $message = "password has been reset.";
        } catch (\Exception $e) {
            $flag = 0;
            $message = $e;
        }
        
        // return json $result
        $result = array(
            'flag' => $flag,
            'message' => $message
        );
        return $this->returnJson($result);
    }

    /**
     *
     * @param string $emailAddress            
     * @param string $captcha
     *            return exception
     */
    protected function sendCaptchaMail($userInfo, $captcha)
    {
        // set the user's defaulte languae in session
        $_SESSION['language'] = $userInfo->language;
        require_once 'vendor/PHPMailer-master/PHPMailerAutoload.php';
        require_once 'module/Users/src/Users/Tools/emailConfig.php';
        require_once 'module/Users/view/users/utils/user_label.php';
        if ($emailConfig[MySwitch] == 'off') {
            return true;
        }
        $mail = new \PHPMailer(true);
        $mail->CharSet = 'utf-8';
        
        try {
            $to = $userInfo->email;
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
            $mail->FromName = $user_labels['fromName'];
            $mail->From = $emailConfig[From];
            $mail->addAddress($to);
            $mail->Subject = $user_labels['passwordSubject'];
            $mail->WordWrap = $emailConfig[WordWrap];
            // create message with html
            $body = $user_labels[dear] . $userInfo->first_name . $user_labels[passwordBody] . "<br><strong>$captcha</strong>" . $user_labels[weblink];
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
    }

    /**
     *
     * @param unknown $email            
     * @return unknown
     */
    protected function getUserInfoByEmail($email)
    {
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        $userInfo = $userInfoTable->getUserInfoByEmail($email);
        return $userInfo;
    }

    /**
     *
     * @param string $email            
     * @param int $captcha            
     *
     * @return throw excption
     */
    protected function saveCaptcha($email, $captcha)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        try {
            $userTable->updateCaptchaByEmail($email, $captcha);
        } catch (\Exception $e) {
            //throw new \SqlException(e);
        }
    }

    /**
     * get email address from web page
     * send the captcha password to the email box
     *
     * @return throw exception if false
     */
    protected function sendMailAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/resetPassword');
        }
        $post = $this->request->getPost();
        $flag = 1;
        // get the userinfo by email
        try {
            $userInfo = $this->getUserInfoByEmail($post->email);
        } catch (\Exception $e) {
            $message = "cant find the user of " . $post->email;
            $flag = 0;
        }
        if ($userInfo) {
            // create captcha and send it to user
            $captcha = rand(100000, 999999);
            try {
                // send captcha mail
                $this->sendCaptchaMail($userInfo, $captcha);
                
                // save $captcha to the user
                $this->saveCaptcha($post->email, $captcha);
                $message = "send the email";
            } catch (\Exception $e) {
                $message = "can\'t send the captcha";
                $flag = 0;
            }
        }
        
        $result = array(
            'message' => $message,
            'flag' => $flag
        );
        return $this->returnJson($result);
    }

    /**
     * change an array to Json and return response
     *
     * @param array $array            
     * @return \Zend\Stdlib\ResponseInterface
     */
    protected function returnJson($result)
    {
        $json = Json::encode($result);
        $response = $this->getEvent()->getResponse();
        $response->setContent($json);
        
        return $response;
    }

    /**
     * get captcha and email from web
     * check it with user table
     *
     * @return json data
     */
    protected function verifyCaptchaAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/resetPassword');
        }
        $post = $this->request->getPost();
        $captcha = (int) $post->captcha;
        $email = $post->email;
        
        // validate email
        $validator = new EmailAddress();
        if ($validator->isValid($email)) {
            // throw new \Exception("user email" . $email . "captcha--" . $captcha);
            
            // get the user by email
            $userTable = $this->getServiceLocator()->get('UserTable');
            try {
                $user = $userTable->getUserByEmail($email);
            } catch (\Exception $e) {
                $result = array(
                    'flag' => 0,
                    'message' => 'sql error'
                );
                return $this->returnJson($result);
            }
            
            // verify the captcha
            // 0 erro, 1 success
            if ($user->captcha == $captcha) {
                $result = array(
                    'flag' => 1,
                    'message' => 'the captcha is metched'
                );
            } else {
                $result = array(
                    'flag' => 0,
                    'message' => 'the captch is not metched'
                );
            }
        } else {
            $result = array(
                'flag' => 0,
                'message' => 'email is error'
            );
        }
        
        return $this->returnJson($result);
    }

    /**
     * reset password by email
     *
     * @see ConfirmEmailForm and RandomPasswordForm
     */
    protected  function indexAction()
    {
        $form = $this->getServiceLocator()->get('ConfirmEmailForm');
        $confirmCaptchaForm = $this->getServiceLocator()->get('ConfirmCaptchaForm');
        $resetPasswordForm = $this->getServiceLocator()->get('ResetPasswordForm');
        $viewModel = new ViewModel(array(
            'form' => $form,
            'confirmCaptchaForm' => $confirmCaptchaForm,
            'resetPasswordForm' => $resetPasswordForm
        ));
        return $viewModel;
    }
}

