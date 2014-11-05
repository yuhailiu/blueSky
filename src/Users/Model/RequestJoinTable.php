<?php
namespace Users\Model;

use Zend\Text\Table\Row;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Validator\EmailAddress;
use Users\Tools\MyUtils;

class RequestJoinTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveRequestJoin(RequestJoin $requestJoin)
    {
        $data = array(
            'requester' => $requestJoin->requester,
            'request_org_id' => $requestJoin->request_org_id,
            'create_time' => $requestJoin->create_time,
            'status' => $requestJoin->status,
            'addition_info' => $requestJoin->addition_info
        );
        
        $this->tableGateway->insert($data);
        
//         $id = (int) $requestJoin->id;
//         if ($id == 0) {
//             $this->tableGateway->insert($data);
//         } else {
//             if ($this->getRequestJoinById($id)) {
                
//                 $this->tableGateway->update($data, array(
//                     'id' => $id
//                 ));
//             } else {
//                 throw new \Exception('Orgnization ID does not exist');
//             }
//         }
    }

    /**
     * get RequestJoin by reponder's email
     *
     * @param string $email            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getPendingRequestJoinByEmail($email)
    {
        // build the sql statement
        $sql = "SELECT request_join.addition_info, request_join.create_time, request_join.requester, userInfo.first_name
                from request_join 
                INNER JOIN userInfo
                ON request_join.requester = userInfo.email
                WHERE `status` = '0' and `request_org_id` = 
                (SELECT id from `orgnization` WHERE `org_creater_email` = '$email') 
                ORDER BY create_time LIMIT 0, 50";
        // get adapter and rowSet
        $adapter = MyUtils::getBD_adapte();
        $rowSet = $adapter->query($sql)->execute();
        
        // turn the buffer
        $rowSet->buffer();
        
        if ($rowSet->count() == 0) {
            throw new \Exception("no request was found");
        } else {
            // return the rowset
            return $rowSet;
        }
    }

    /**
     * Get all RequestJoin info
     *
     * @return ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * get RequestJoin by $email and $orgId
     *
     * @param string $email            
     * @param int $orgId            
     * @throws \Exception
     * @return RequestJoin
     */
    public function getRequestJoinByEmailOrgId($email, $orgId)
    {
        $orgId = (int) $orgId;
        $validater = new EmailAddress();
        if ($validater->isValid($email)) {
            $sql = "select * from request_join where requester='$email' and request_org_id = '$orgId'";
            $adapter = MyUtils::getBD_adapte();
            $rowset = $adapter->query($sql)->execute();
            $row = $rowset->current();
            if (! $row) {
                throw new \Exception("can't find the request");
            } else {
                $requestJoin = new RequestJoin();
                $requestJoin = MyUtils::exchangeDataToObject($row, $requestJoin);
                return $requestJoin;
            }
        } else {
            throw new \Exception("invalidate email address");
        }
    }

    /**
     * Get RequestJoin by id
     *
     * @param string $id            
     * @throws \Exception
     * @return Row
     */
    public function getRequestJoinById($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array(
            'id' => $id
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    /**
     * Get RequestJoin by requester
     *
     * @param string $requester            
     * @throws \Exception
     * @return Rowset
     */
    public function getRequestJoinByRequester($requester)
    {
        $validater = new EmailAddress();
        if ($validater->isValid($requester)) {
            $rowset = $this->tableGateway->select(array(
                'requester' => $requester
            ));
            return $rowset;
        } else {
            return false;
        }
    }

    /**
     * Get RequestJoin by $orgId
     *
     * @param int $orgId            
     * @throws \Exception
     * @return Rowset
     */
    public function getRequestJoinByOrgId($orgId)
    {
        $orgId = (int) $orgId;
        $rowset = $this->tableGateway->select(array(
            'request_org_id' => $orgId
        ));
        return $rowset;
    }

    /**
     * Delete RequestJoin by OrgId
     *
     * @param int $id            
     */
    public function deleteRequestJoinById($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(array(
            'id' => $id
        ));
    }
}
