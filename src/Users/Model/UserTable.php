<?php
namespace Users\Model;

use Zend\Text\Table\Row;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Users\Tools\MyUtils;

class UserTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * save user to users table, can't update
     *
     * @param User $user            
     */
    public function saveUser(User $user)
    {
        // prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($user);
        $this->tableGateway->insert($data);
    }
    
    /**
     * 
     * @param User $user
     */
    public function updateUser(User $user)
    {
        // prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($user);
        $this->tableGateway->update($data, array('email' => $user->email));
    }
    
    /**
     * 
     * @param User $user
     */
    public function updateUserById(User $user)
    {
        // prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($user);
        $this->tableGateway->update($data, array('id' => $user->id));
    }

    /**
     * Get all users
     *
     * @return ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * Get User account by Email
     *
     * @param string $userEmail            
     * @throws \Exception
     * @return Row
     */
    public function getUserByEmail($email)
    {
        $rowset = $this->tableGateway->select(array(
            'email' => $email
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $email");
        }
        return $row;
    }
    
    /**
     * 
     * @param unknown $id
     * @throws \Exception
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, \Zend\Db\ResultSet\mixed, unknown>
     */
    public function getUserById($id)
    {
        $rowset = $this->tableGateway->select(array(
            'id' => $id
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function getUsersByDeviceToken($deviceToken)
    {
        $rowset = $this->tableGateway->select(array(
            'deviceToken' => $deviceToken
        ));
        if (! $rowset) {
            throw new \Exception("Could not find row $deviceToken");
        }
        return $rowset;
    }
    
    public function getUserBySessionCode($sessionCode)
    {
        $rowset = $this->tableGateway->select(array(
            'sessionCode' => $sessionCode
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $sessionCode");
        }
        return $row;
    }
    
    public function getUserByPhoneNumberOnly($phoneNumber)
    {
        $rowset = $this->tableGateway->select(array(
            'phoneNumber' => $phoneNumber
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $phoneNumber");
        }
        return $row;
    }
    
    /**
     * Get User by phoneNumber
     * 
     * @param string $phoneNumber
     * @return user
     */
    public function  getUserByPhoneNumber($phoneNumber,$areaCode)
    {
        $rowset = $this->tableGateway->select(array(
        		'phoneNumber' => $phoneNumber,
                'areaCode' => $areaCode,
        ));
        $row = $rowset->current();
        if (! $row) {
        	throw new \Exception("Could not find row $phoneNumber");
        }
        return $row;
    }
    
    /**
     * 
     * @param unknown $phoneNumber
     * @throws \Exception
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, \Zend\Db\ResultSet\mixed, unknown>
     */
    public function  newGetUserByPhoneNumber($phoneNumber)
    {
        $rowset = $this->tableGateway->select(array(
        		'phoneNumber' => $phoneNumber,
        ));
        $row = $rowset->current();
        if (! $row) {
        	throw new \Exception("Could not find row $phoneNumber");
        }
        return $row;
    }

    /**
     * Delete User account by $email
     *
     * @param string $email            
     */
    public function deleteUser($email)
    {
        $this->tableGateway->delete(array(
            'email' => $email
        ));
    }
    

    /**
     * change the password
     *
     * @param string $email            
     * @param string $password            
     */
    public function updatePasswordByEmail($email, $password)
    {
        $password = md5($password);
        $this->tableGateway->update(array(
            'password' => $password
        ), array(
            'email' => $email
        ));
    }


    /**
     * increase 1 every failed login, if the times is over 10, throw exception
     *
     * @param int $id            
     * @param int $failedTimes            
     * @throws \Exception
     */
    public function updateFailedTimesByEmail($email, $failedTimes)
    {
        try {
            $this->tableGateway->update(array(
                'failedTimes' => $failedTimes
            ), array(
                'email' => $email
            ));
        } catch (\Exception $e) {
            throw new \Exception("failed to update failedTimes");
        }
    }
}
