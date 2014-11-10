<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Users\Model\User;
use Users\Tools\MyUtils;
// use Users\Exception\MyException;
class CommController extends AbstractActionController
{

    protected $authservice;

    protected $user;

    protected function isAuthrizedUser($areaCode, $phoneNumber, $password)
    {
        $user = new User();
        try {
            // $user = $this->getUserByPhoneNumber($phoneNumber, $areaCode);
            $user = $this->getUserByPhoneNumberOnly($phoneNumber, $areaCode);
        } catch (\Exception $e) {
            return false;
        }
        if ($user->password == md5($password) && $user->failedTimes < 6) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }

    protected function getUserByPhoneNumberOnly($phoneNumber)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $row = $userTable->getUserByPhoneNumberOnly($phoneNumber);
        return $row;
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

    public function indexAction()
    {
        header("Location:/m/service.html");
        exit();
        // // authrize user
        // require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // return $this->returnJson(array(
        // 'webpage' => 'commIndex'
        // ));
    }

    protected function isValidUserBySessionCode($phoneNumber, $sessionCode)
    {
        $user = $this->getUserByPhoneNumberOnly($phoneNumber);
        if ($user->sessionCode == $sessionCode) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }

    protected function getUserBySessionCode($sessionCode, $phoneNumber)
    {
        // check phoneNumber
        $phoneNumber = MyUtils::clearNumber($phoneNumber);
        if (MyUtils::isValidateTel($phoneNumber)) {
            // check sessionCode
            $sessionCode = str_replace(" ", "", $sessionCode);
            if (strlen($sessionCode) > 10) {
                if (!$this->isValidUserBySessionCode($phoneNumber, $sessionCode)) {
                    throw new \Exception("invalidUser");
                }
            } else {
                throw new \Exception("sessionCodeError");
            }
        } else {
            throw new \Exception("phoneNumberError");
        }
    }

    /**
     * check data type, user status
     *
     * @param string $password            
     * @param string $phoneNumber            
     * @param string $areaCode            
     * @throws \Exception
     */
    protected function authrizeUser($password, $phoneNumber, $areaCode)
    {
        $password = $password ? str_replace(" ", "", $password) : "";
        if (MyUtils::isValidatePassword($password)) {
            $phoneNumber = MyUtils::clearNumber($phoneNumber);
            if (MyUtils::isValidateTel($phoneNumber)) {
                $areaCode = MyUtils::clearNumber($areaCode);
                if (! $this->isAuthrizedUser($areaCode, $phoneNumber, $password)) {
                    throw new \Exception("invalidUser");
                }
            } else {
                throw new \Exception("phoneNumberError");
            }
        } else {
            throw new \Exception("invalidPassword");
        }
    }
}
