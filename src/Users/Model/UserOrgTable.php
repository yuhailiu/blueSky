<?php
namespace Users\Model;

use Zend\Db\TableGateway\TableGateway;
use Users\Tools\MyUtils;

class UserOrgTable
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
    public function saveUserOrg(UserOrg $userOrg)
    {
        //prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($userOrg);
        $this->tableGateway->insert($data);
    }
}
