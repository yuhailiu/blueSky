<?php
namespace Users\Model;

use Zend\Text\Table\Row;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Users\Tools\MyUtils;

class UserInfoTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveUserInfo(UserInfo $userInfo)
    {
        $data = MyUtils::exchangeObjectToData($userInfo);
        $this->tableGateway->insert($data);
    }

    public function updateUserInfo(UserInfo $userInfo)
    {
        $data = MyUtils::exchangeObjectToData($userInfo);
        $this->tableGateway->update($data, array(
            'email' => $userInfo->email
        ));
    }

    /**
     * Get all userInfos
     *
     * @return ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * Get UserInfo account by UserId
     *
     * @param string $id            
     * @throws \Exception
     * @return Row
     */
    public function getUserInfoByEmail($email)
    {
        $rowset = $this->tableGateway->select(array(
            'email' => $email
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row by $email");
        }
        return $row;
    }

    /**
     * Delete UserInfo by UserId
     *
     * @param string $id            
     */
    public function deleteUser($userId)
    {
        $this->tableGateway->delete(array(
            'user_id' => $userId
        ));
    }
}
