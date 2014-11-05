<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Model\User;
use Zend\Validator\Regex;
use Users\Tools\MyUtils;
use Zend\Json\Json;
use Users\Model\UserInfo;

class SettingController extends AbstractActionController
{

    protected $authservice;

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

    public function indexAction()
    {
        // $this->layout('layout/myaccount');
        // check is it a league user, false return to login page
        $email = $this->getAuthService()
            ->getStorage()
            ->read();
        // check empty and verify
        if (! $email) {
            return $this->redirect()->toRoute('users/login');
        }
        
        // get user and user info by email
        $userTable = $this->getServiceLocator()->get('UserTable');
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        try {
            $user = $userTable->getUserByEmail($email);
            $userInfo = $userInfoTable->getUserInfoByEmail($email);
        } catch (\Exception $e) {
            MyUtils::writelog("can't get user or info in setting controller" . $e);
            return $this->redirect()->toRoute('users/setting');
        }
        
        // get image upload form
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        // get user info set form
        $userSetForm = $this->getServiceLocator()->get('UserSetForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'userInfo' => $userInfo,
            'form' => $form,
            'userSetForm' => $userSetForm
        ));
        return $viewModel;
    }

    protected function getOwnerInfoAction()
    {
        // authorize the request
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get userinfo by email
        $userInfo = $this->getUserInfoByEmail($email);
        
        // return json response
        return $this->returnJson($userInfo);
    }

    protected function getUserInfoByEmail($email)
    {
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        $userInfo = $userInfoTable->getUserInfoByEmail($email);
        return $userInfo;
    }

    protected function changePasswordAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserByEmail($email);
        $form = $this->getServiceLocator()->get('ChangePasswordForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
        return $viewModel;
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
     * change the password when the user can provide the old password
     *
     * @return confirm page if success or back when false
     */
    protected function processPasswordAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $post = $this->request->getPost();
        
        // Is validate password?
        $util = new MyUtils();
        if (! $util->isValidatePassword($post->password)) {
            return $this->showError("the new password isn't validate");
        }
        
        // Is new password same as the confirm password?
        if ($post->password != $post->confirm_password) {
            return $this->showError("the passwords are not same");
        }
        
        // Is validate form?
        $form = $this->getServiceLocator()->get('ChangePasswordForm');
        $form->setData($post);
        if (! $form->isValid()) {
            $result = array(
                'flag' => false,
                'message' => "the form is invalid"
            );
            return $this->returnJson($result);
        } else {
            
            // get the relative table and form
            $userTable = $this->getServiceLocator()->get('UserTable');
            $user = $userTable->getUserByEmail($email);
            
            // compare the old password, if false return to error
            $old_password = md5($post->old_password);
            if ($user->password != $old_password) {
                $result = array(
                    'flag' => false,
                    'message' => "the password is invalid"
                );
                return $this->returnJson($result);
                
                // return $this->showError('old password isn\'t right');
            } else {
                
                try {
                    // Update the password by id
                    $userTable->updatePasswordByEmail($email, $post->password);
                    $result = array(
                        'flag' => true,
                        'message' => "the password has been updated"
                    );
                    return $this->returnJson($result);
                    // return $this->redirect()->toRoute('users/setting', array(
                    // 'action' => 'passwordConfirm'
                    // ));
                } catch (\Exception $e) {
                    MyUtils::writelog("can't write usertable in settingController", $e);
                    return $this->showError('error when update DB');
                }
            }
        }
    }

    protected function showError($message)
    {
        $model = new ViewModel(array(
            'error' => true,
            'message' => $message
        // 'user' => $user
                ));
        $model->setTemplate('users/utils/error');
        return $model;
    }

    protected function updateProfileAction()
    {
        // Authrize the request
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // validate the data of web input
        $userInfo = new UserInfo();
        $userInfo->email = $email;
        $userInfo->first_name = $_GET['first_name'];
        $userInfo->last_name = $_GET['last_name'];
        $userInfo->language = $_GET['language'];
        $userInfo->self_descript = $_GET['self_descript'];
        if (! $this->isValidateUserInfo($userInfo)) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'illeagal userinfo'
            ));
        }
        // update the userinfo table
        try {
            $this->updateUserInfo($userInfo);
            //update language set in session
            $_SESSION['language'] = $userInfo->language;
            $_SESSION['username'] = $userInfo->first_name;
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'can not update the profile'
            ));
        }
        
        // return result
        return $this->returnJson(array(
            'flag' => true
        ));
    }
    /**
     * 
     * @param UserInfo $userInfo
     */
    protected function updateUserInfo(UserInfo $userInfo)
    {
        $sql ="UPDATE userInfo set first_name = '$userInfo->first_name', 
            last_name = '$userInfo->last_name', language = '$userInfo->language', 
            self_descript = '$userInfo->self_descript'
            where email = '$userInfo->email'";
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    /**
     *
     * @param UserInfo $userInfo            
     * @return boolean
     */
    protected function isValidateUserInfo(UserInfo $userInfo)
    {
        // init flag
        $flag = true;
        if (MyUtils::isValidateName($userInfo->first_name) || MyUtils::isValidateName($userInfo->last_name)) {
            // set language array
            $language_array = array(
                'en',
                'zh'
            );
            if (in_array($userInfo->language, $language_array)) {
                if (MyUtils::isValidateAddress($userInfo->self_descript)) {
                    $flag = true;
                } else {
                    // invalidate self_descript
                    $flag = false;
                    $message = "invalidate self_descript";
                }
            } else {
                // invalidate language
                $flag = false;
                $message = "invalidate language setting";
            }
        } else {
            // invalidate name
            $flag = false;
            $message = "invalidate name";
        }
        // write a log for error
        if (! $flag) {
            MyUtils::writelog($userInfo->email . "--" . $message);
        }
        return $flag;
    }


    protected function validateTel($tel)
    {
        $flag = true;
        
        if ($tel) {
            $validator = new Regex(array(
                'pattern' => '/(^(\d{3,4}-)?\d{7,8})$|(1[0-9][0-9]{9})$|(^(\d{3,4}-)?\d{7,8}-)/'
            ));
            $flag = $validator->isValid($tel);
        }
        
        return $flag;
    }

    /**
     * if has new property in data update the user relative otherwise keep user's original
     *
     * @param User $user            
     * @param Array $data            
     * @return User
     */
    protected function exchangeArray($user, $data)
    {
        $user->id = (isset($data['id'])) ? $data['id'] : $user->id;
        $user->first_name = (isset($data['first_name'])) ? $data['first_name'] : $user->first_name;
        $user->last_name = (isset($data['last_name'])) ? $data['last_name'] : $user->last_name;
        $user->email = (isset($data['email'])) ? $data['email'] : $user->email;
        $user->password = (isset($data['password'])) ? $data['password'] : $user->password;
        $user->filename = (isset($data['filename'])) ? $data['filename'] : $user->filename;
        $user->thumbnail = (isset($data['thumbnail'])) ? $data['thumbnail'] : $user->thumbnail;
        $user->create_time = (isset($data['create_time'])) ? $data['create_time'] : $user->create_time;
        $user->last_modify = (isset($data['last_modify'])) ? $data['last_modify'] : $user->last_modify;
        $user->sex = (isset($data['sex'])) ? $data['sex'] : $user->sex;
        $user->telephone1 = (isset($data['telephone1'])) ? $data['telephone1'] : $user->telephone1;
        $user->telephone2 = (isset($data['telephone2'])) ? $data['telephone2'] : $user->telephone2;
        $user->address = (isset($data['address'])) ? $data['address'] : $user->address;
        $user->title = (isset($data['title'])) ? $data['title'] : $user->title;
        
        return $user;
    }

    /**
     * get user by the authorized email
     *
     * @return User
     */
    protected function getUser($email)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserByEmail($email);
        return $user;
    }

    /**
     * If setting is successful, show confirm message and
     * close the window in 5 seconds
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function passwordConfirmAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $user = $this->getUser($email);
        $viewModel = new ViewModel(array(
            'user' => $user
        ));
        return $viewModel;
    }
}

