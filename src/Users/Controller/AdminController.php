<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{

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

    public function indexAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthAdmin.php';
        
        // get the total no of current users
        try {
            $total = $this->getTotalNo();
        } catch (\Exception $e) {
            $message = "can\t get the total no";
            $total = 0;
        }
        
        $this->layout('layout/frame');
        
        $view = new ViewModel(array(
            'total' => $total['COUNT(*)']
        ));
        return $view;
    }

    /**
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function getTotalNo()
    {
        $sql = "SELECT COUNT(*) FROM users";
        $adapter = $this->getAdapter();
        $total = $adapter->query($sql)->execute();
        return $total->current();
    }

    public function getUserByEmailAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthAdmin.php';
        
        $email = $_GET['email'];
        
        //get userinfo and user by email
        try {
        	$userInfo = $this->getUserInfoByEmail($email);
        } catch (\Exception $e) {
            $message = "failed to get the user of ". $email;
        }
        
        $this->layout('layout/frame');
        
        $view = new ViewModel(array(
        	'user' => $userInfo,
            'message' => $message,
        ));
        return $view;
    }
    
    /**
     * 
     * @param unknown $email
     * @return mixed
     */
    protected function getUserInfoByEmail($email)
    {
        $sql = "select * from userInfo, users
            WHERE users.email = userInfo.email
            and users.email = '$email'";
        
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->current();
    }
    
    public function updateUserByEmailAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthAdmin.php';
        
        $email = $_GET['email'];
        $action = $_GET['action'];
        
        //close the users by id
        try {
        	$this->updateUserByEmail($email, $action);
        } catch (\Exception $e) {
            $message = "can't update the user whose email is ".$email;
            throw new \Exception($message);
        }
        
        //redirect to last page
        return $this->redirect()->toRoute('admin');
    }
    
    /**
     * 
     * @param unknown $email
     * @param unknown $action
     */
    protected function updateUserByEmail($email, $action)
    {
        //set isClose value according action
        if($action == 'close'){
            $isClose = 1;
        }else if ($action == 'open') {
        	$isClose = 0;
        }
        
        $sql = "UPDATE users set isClose = '$isClose' 
            where email = '$email'";
        
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }
}

















