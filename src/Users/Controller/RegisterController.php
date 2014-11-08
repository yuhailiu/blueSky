<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Users\Model\User;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Json\Json;

class RegisterController extends AbstractActionController
{

    protected function getAdapter()
    {
        if (! $this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
    }

    protected function returnResponse($result)
    {
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
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

    /**
     *
     * @return response true or false
     */
    protected function processAction()
    {
        
        // is this post request
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/register');
        }
        // check the captcha code
        session_start();
        $post = $this->request->getPost();
        $compare = strcasecmp($_SESSION['captcha_id'], $post->captcha);
        if (0 != $compare) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'captcha is wrong'
            ));
        }
        
        // the password same and term is accept
        if ($post->terms != 'on' || $post->password != $post->confirm_password) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'terms are off or password is wrong'
            ));
        }
        // is validate date
        if (! $this->isValidate($post)) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'data is invalid.'
            ));
        }
        try {
            $this->createUser($post);
            $this->initUserInfo($post);
            
        } catch (\Exception $e) {
            MyUtils::writelog("error when register user" . $e);
            // return false
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'can not write database.'
            ));
        }
        return $this->returnJson(array(
            'flag' => true,
            'message' => 'user has been created.'
        ));
    }

    /**
     *
     * @param unknown $post            
     * @return boolean
     */
    protected function isValidate($post)
    {
        // validate first_name, last_name, email, password, language
        if (MyUtils::isValidateName($post->first_name) && MyUtils::isValidateName($post->last_name)) {
            if (MyUtils::isValidatePassword($post->password)) {
                // set lang scop english and chinese
                $lang = array(
                    'en',
                    'zh'
                );
                if (in_array($post->language, $lang)) {
                    $emailValidate = new EmailAddress();
                    if ($emailValidate->isValid($post->email)) {
                        $flag = true;
                    } else {
                        // invalid email
                        $flag = false;
                    }
                } else {
                    // invalid language
                    $flag = false;
                }
            } else {
                // invalid password
                $flag = false;
            }
        } else {
            // invalid name
            $flag = false;
        }
        return $flag;
    }

    protected function createUser($data)
    {
        $user = new User();
        $user->setPassword($data['password']);
        
        $sql = "insert into users (email, password) values ('$data->email', '$user->password')";
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    /**
     * init userInfo by the $email, default pic
     *
     * @param string $email            
     * @return boolean
     */
    protected function initUserInfo($data)
    {
        $sql = "insert into userInfo (email, first_name, last_name, language) 
            values ('$data->email', '$data->first_name', '$data->last_name', '$data->language')";
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    protected function checkEmailAction()
    {
        $email = $_GET['email'];
        
        // validate the email address
        $validator = new EmailAddress();
        if ($validator->isValid($email)) {
            $userTable = $this->getServiceLocator()->get('UserTable');
            
            try {
                $userTable->getUserByEmail($email);
                $result = "false";
            } catch (\Exception $e) {
                
                $result = "true";
            }
        } else {
            // email is invalid; return false the reasons
            $result = "true";
        }
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
    }
}
