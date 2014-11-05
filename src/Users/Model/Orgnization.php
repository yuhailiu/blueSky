<?php
namespace Users\Model;

class Orgnization
{
    public $id;
    public $org_name;
    public $org_des;
    public $org_tel;
    public $org_address;
    public $org_website;
    public $org_logo;
    public $org_logo_thumbnail;
    public $org_CT;
    public $org_LM;
    public $org_creater_email;
    
    
  
	function exchangeArray($data)
	{
		$this->id		= (isset($data['id'])) ? $data['id'] : null;
		$this->org_name		= (isset($data['org_name'])) ? $data['org_name'] : null;
		$this->org_des		= (isset($data['org_des'])) ? $data['org_des'] : null;
		$this->org_tel		= (isset($data['org_tel'])) ? $data['org_tel'] : null;
		$this->org_address		= (isset($data['org_address'])) ? $data['org_address'] : null;
		$this->org_website		= (isset($data['org_website'])) ? $data['org_website'] : null;
		$this->org_logo		= (isset($data['org_logo'])) ? $data['org_logo'] : null;
		$this->org_logo_thumbnail		= (isset($data['org_logo_thumbnail'])) ? $data['org_logo_thumbnail'] : null;
		$this->org_CT		= (isset($data['org_CT'])) ? $data['org_CT'] : null;
		$this->org_LM		= (isset($data['org_LM'])) ? $data['org_LM'] : null;
		$this->org_creater_email		= (isset($data['org_creater_email'])) ? $data['org_creater_email'] : null;
				
	}

	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
