<?php
namespace Users\Model;

use Zend\Text\Table\Row;
use Zend\Db\TableGateway\TableGateway;
use Users\Tools\MyUtils;

class PushStackTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     *
     * @param PushStack $pushStack            
     */
    public function savePushStack(PushStack $pushStack)
    {
        // prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($pushStack);
        $this->tableGateway->insert($data);
    }

    /**
     *
     * @param PushStack $pushStack            
     */
    public function updatePushStack(PushStack $pushStack)
    {
        // prepare the data for update or insert
        $data = MyUtils::exchangeObjectToData($pushStack);
        $this->tableGateway->update($data, array(
            'id' => $pushStack->id
        ));
    }

    /**
     * Get User account by Email
     *
     * @param string $userEmail            
     * @throws \Exception
     * @return Row
     */
    public function getPushStackByStatus($status)
    {
        $resultSet = $this->tableGateway->select(array(
            'status' => $status
        ));
        return $resultSet;
    }
    
    public function getApplePushByStatus($status)
    {
        $pushs = $this->getPushStackByStatus($status);
        $array = array();
        foreach ($pushs as $push)
        {
            if (strlen($push->deviceToken) > 15) {
            	   array_push($array, $push);
            }
        }
        return $array;
    }
    
    public function getAndriodPushByStatus($status)
    {
        $pushs = $this->getPushStackByStatus($status);
        $array = array();
        foreach ($pushs as $push)
        {
            if (strlen($push->deviceToken) < 15) {
            	   array_push($array, $push);
            }
        }
        return $array;
    }
}
