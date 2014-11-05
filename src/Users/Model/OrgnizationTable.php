<?php
namespace Users\Model;

use Zend\Text\Table\Row;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Users\Tools\MyUtils;

class OrgnizationTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveOrgnization(Orgnization $org)
    {
        $data = array(
            'org_name'      => $org->org_name,
            'org_des'       => $org->org_des,
            'org_tel'       => $org->org_tel,            
            'org_address'   => $org->org_address,            
            'org_website'   => $org->org_website,            
            'org_logo'      => $org->org_logo,            
            'org_logo_thumbnail'      => $org->org_logo_thumbnail,            
            'org_CT'        => $org->org_CT,            
            'org_LM'        => null, 
            'org_creater_email'=> $org->org_creater_email, 
                       
        );

        $id = (int)$org->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getOrgnizationById($id)) {
            	
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Orgnization ID does not exist');
            }
        }
    }
    
    /**
     * Get all orgnization
     * @return ResultSet
     */
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }
    
    /**
     * Get orgnization by orgId
     * @param string $id
     * @throws \Exception
     * @return Row
     */    
    public function getOrgnizationById($id)
    {
    	$id  = (int) $id;
    	$rowset = $this->tableGateway->select(array('id' => $id));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $id");
    	}
    	return $row;
    }
    
    /**
     * Get Orgnization by Creater id
     * @param int $id
     * @throws \Exception
     * @return Row
     */
    public function getOrgnizationByCreaterEmail($email)
    {
        $rowset = $this->tableGateway->select(array('org_creater_email' => $email));
        $row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $email");
    	}
    	return $row;    
    }
    
    /**
     * get orgnization by org_name and org_creater_email != email
     * 
     * @param string $orgName
     * @param string $email
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getOrgByNameExcludeEmail($orgName, $email)
    {
        $sql = "SELECT * from orgnization where org_name =
        '$orgName' and org_creater_email != '$email'  ORDER BY org_LM DESC LIMIT 0, 50 ";
        $adapter = MyUtils::getBD_adapte();
        $rowSet = $adapter->query($sql)->execute();
        $rowSet->buffer();
        if ($rowSet->count() == 0) {
        	throw new \Exception("no row with the conditions");
        }else{
            return $rowSet;
        }
    }
    
    /**
     * Delete Orgnization by OrgId
     * @param int $id
     */
    public function deleteOrgById($id)
    {
    	$id = (int)$id;
        $this->tableGateway->delete(array('id' => $id));
    }
        
}
