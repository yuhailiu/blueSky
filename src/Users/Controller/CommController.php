<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Users\Model\User;
use Users\Tools\MyUtils;
use Users\Model\Notification;
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

    protected function newUpdateUserNotificationNumber(Notification $notification)
    {
        $sql = "UPDATE users SET notificationNumber = '$notification->notificationNumber'
            where id = '$notification->userId'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
    }

    protected function getUserByPhoneNumberOnly($phoneNumber)
    {
        if (strlen($phoneNumber) > 4) {
            $userTable = $this->getServiceLocator()->get('UserTable');
            $row = $userTable->getUserByPhoneNumberOnly($phoneNumber);
            return $row;
        } else {
            throw new \Exception("phoneNumberError");
        }
    }

    protected function getCommentById($id)
    {
        $sql = "SELECT `comment`.id , comment, `comment`.target_id, `comment`.create_time, users.phoneNumber as create_user from `comment` ,users
            WHERE `comment`.id = '$id' and `comment`.create_user = users.id";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows->current();
    }

    /**
     * get user object
     *
     * @param string $id            
     * @return User
     */
    protected function getUserById($id)
    {
        $sql = "SELECT * from users where id = '$id'";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $data = $rows->current();
        $object = new User();
        $user = MyUtils::exchangeDataToObject($data, $object);
        return $user;
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
                if (! $this->isValidUserBySessionCode($phoneNumber, $sessionCode)) {
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

    protected function parseAddressBookPhoneNumber($addressBookPhoneNumber)
    {
        if (strlen($addressBookPhoneNumber) > 5) {
            $members = MyUtils::changeStringtoArray($addressBookPhoneNumber);
        } else {
            throw new \Exception("addressBookPhoneNumberError");
        }
        return $members;
    }
}
