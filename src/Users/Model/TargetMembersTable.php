<?php
namespace Users\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Users\Tools\MyUtils;

class TargetMembersTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * save user to users table, can't update
     *
     * @param TargetMembers $targetMembers            
     */
    public function saveTargetMembers(TargetMembers $targetMembers)
    {
        // prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($targetMembers);
        $this->tableGateway->insert($data);
    }


    /**
     * Get all TargetMembers
     *
     * @return ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     *
     * @param string $id            
     * @throws \Exception
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, \Zend\Db\ResultSet\mixed, unknown>
     */
    public function getTargetMembersById($id)
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
}
