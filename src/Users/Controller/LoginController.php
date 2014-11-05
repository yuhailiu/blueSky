<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Json\Json;
use Users\Model\User;

class LoginController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    protected function getAuthService()
    {
        return $this->authservice = $this->getServiceLocator()->get('AuthService');
    }

    protected function getAdapter()
    {
        if (! $this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
    }

    protected function logoutAction()
    {
        $this->getAuthService()->clearIdentity();
        
        return $this->redirect()->toRoute('users/login');
    }

    public function indexAction()
    {
        //clear the authservice
        $this->getAuthService()->clearIdentity();
        //get form return it.
        $form = $this->getServiceLocator()->get('LoginForm');
        $viewModel = new ViewModel(array(
            'form' => $form
        ));
        return $viewModel;
    }

    /**
     * fail login
     *
     * @param int $failedTimes            
     * @return Response Json(flag = false, failedTimes)
     */
    protected function returnIndexError($failedTimes, $isClose)
    {
        $result = array(
            'flag' => false,
            'failedTimes' => $failedTimes,
            'isClose' => $isClose
        );
        
        return $this->returnJson($result);
    }

    /**
     * success login
     *
     * @return Reponse Json(flag = true, failedTimes = 0)
     */
    protected function returnLoginSuccess()
    {
        $result = array(
            'flag' => true,
            'failedTimes' => 0
        );
        return $this->returnJson($result);
    }

    /**
     * check password is match email in usertable
     *
     * @param string $password            
     * @param string $email            
     * @return array (boolean flag, int failedTimes)
     */
    protected function isValidateUser($password, $email)
    {
        // check it in the DB
        // get the user
        $userTable = $this->getServiceLocator()->get('UserTable');
        $failedTimes = 0;
        try {
            $user = $userTable->getUserByEmail($email);
        } catch (\Exception $e) {
            // write a error log
            MyUtils::writelog("error at isValidateUser--" . $e);
            return array(
                'flag' => false,
                'failedTimes' => $user->failedTimes
            );
        }
        // if it's a close account throw exception
        if ($user->isClose == 1) {
            return array(
                'flag' => false,
                'failedTimes' => $user->failedTimes,
                'isClose' => 1,
            );
        }
        
        // if user->failedTimes > 10 then return false,
        // others failed time add 1,then go ahead
        $failedTimes = $user->failedTimes;
        // get success login times
        $successTimes = $user->successTimes;
        
        // get the FAILED_TIMES
        require 'module/Users/src/Users/Tools/appConfig.php';
        if ($failedTimes > FAILED_TIMES) {
            return array(
                'flag' => false,
                'failedTimes' => $failedTimes
            );
        } else {
            if ($user->password == md5($password)) {
                // write it in AuthService the session has been open here
                $this->getAuthService()
                    ->getStorage()
                    ->write($email);
                
                // save email to session
                $_SESSION['email'] = $user->email;
                $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
                $userInfo = $userInfoTable->getUserInfoByEmail($email);
                $_SESSION['username'] = $userInfo->first_name;
                // save language set to session
                $_SESSION['language'] = $userInfo->language;
                
                // update failedTimes in DB with 0
                // success time add 1
                $successTimes ++;
                // update user
                $user->successTimes = $successTimes;
                $user->failedTimes = 0;
                try {
                    $this->updateUserFSTimes($user);
                } catch (\Exception $e) {
                    $message = 'failed to update user';
                }
                
                return array(
                    'flag' => true,
                    'failedTimes' => 0,
                    'message' => $message
                );
            } else {
                
                // update failedTimes in DB with add 1
                $user->failedTimes = $failedTimes + 1;
                try {
                    $this->updateUserFailTimes($user);
                } catch (\Exception $e) {
                    MyUtils::writelog($user->email . " failed to update the failed times");
                }
                // $userTable->updateFailedTimesByEmail($email, $failedTimes + 1);
                
                return array(
                    'flag' => false,
                    'failedTimes' => $failedTimes + 1
                );
            }
        }
    }

    /**
     *
     * @param User $user            
     */
    protected function updateUserFSTimes(User $user)
    {
        $sql = "UPDATE users set failedTimes = '$user->failedTimes', successTimes = '$user->successTimes'
            where email = '$user->email'";
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    /**
     *
     * @param User $user            
     */
    protected function updateUserFailTimes(User $user)
    {
        $sql = "UPDATE users set failedTimes = '$user->failedTimes', lastSuccesTime = '$user->lastSuccesTime'
            where email = '$user->email'";
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    /**
     * get password and email from page
     *
     * @return login page with error if false
     * @return confirm page with user id
     */
    protected function processAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/login');
        }
        $post = $this->request->getPost();
        $form = $this->getServiceLocator()->get('LoginForm');
        
        // validate the passoword
        if (! MyUtils::isValidatePassword($post->password)) {
            return $this->returnIndexError(0);
        }
        
        // validate login form
        $form->setData($post);
        if ($form->isValid()) {
            
            // Is validate users
            $result = $this->isValidateUser($post->password, $post->email);
            
            // flag is true return true, others return false and times
            if ($result['flag']) {
                
                // return to success
                return $this->returnLoginSuccess();
            } else {
                
                // return failed with times and isClose flag
                return $this->returnIndexError($result['failedTimes'], $result['isClose']);
            }
        } else {
            return $this->returnIndexError(0);
        }
    }

    /**
     * get the user id from AuthService
     *
     * @return ViewModel with user_info and org
     */
    protected function confirmAction()
    {
        // get the user email, if false return to users/login
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $userEmail = $email;
        
        // get tabs id from route
        $tabs = $this->params()->fromRoute('tabs');
        
        $userTable = $this->getServiceLocator()->get('UserTable');
        // if can not get the user redirect to login page
        try {
            $user = $userTable->getUserByEmail($userEmail);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('users/login');
        }
        
        // if can not get the user info, plase null to it
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        try {
            $userInfo = $userInfoTable->getUserInfoByEmail($userEmail);
        } catch (\Exception $e) {
            $userInfo = null;
        }
        
        // if can't get org place null to it
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        try {
            $org = $orgTable->getOrgnizationByCreaterEmail($email);
        } catch (\Exception $e) {
            $org = null;
        }
        
        // get the pending request
        $requestJoin = $this->getServiceLocator()->get('RequestJoinTable');
        try {
            $rowSet = $requestJoin->getPendingRequestJoinByEmail($email);
            $pendingNo = $rowSet->count();
        } catch (\Exception $e) {
            $pendingNo = 0;
        }
        
        // store the pendingNo to session
        $_SESSION['pendingNo'] = $pendingNo;
        $_SESSION['username'] = $userInfo->first_name;
        $_SESSION['org_id'] = $org->id;
        $_SESSION['org_name'] = $org->org_name;
        
        // get the relative forms
        $orgSearchForm = $this->getServiceLocator()->get('OrgSearchForm');
        $joinOrgForm = $this->getServiceLocator()->get('JoinOrgForm');
        
        $this->layout('layout/myaccount');
        $viewModel = new ViewModel(array(
            'user' => $user,
            'userInfo' => $userInfo,
            'org' => $org,
            'orgSearchForm' => $orgSearchForm,
            'joinOrgForm' => $joinOrgForm,
            'tabs' => $tabs
        ));
        return $viewModel;
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
                $result = "true";
            } catch (\Exception $e) {
                
                $result = "false";
            }
        } else {
            // email is invalid; return false the reasons
            $result = "false";
        }
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
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
}
