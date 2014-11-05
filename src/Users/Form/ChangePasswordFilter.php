<?php
namespace Users\Form;

use Zend\InputFilter\InputFilter;

class ChangePasswordFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'       => 'password',
            'required'   => true,
            'filters' => array(
            		array(
            				'name' => 'StripTags',
            				'name' => 'Zend\Filter\StringTrim'
            		)
            ),
        ));
        $this->add(array(
            'name'       => 'old_password',
            'required'   => true,
            'filters' => array(
            		array(
            				'name' => 'StripTags',
            				'name' => 'Zend\Filter\StringTrim'
            		)
            ),
        ));
        $this->add(array(
            'name'       => 'confirm_password',
            'required'   => true,
            'filters' => array(
            		array(
            				'name' => 'StripTags',
            				'name' => 'Zend\Filter\StringTrim'
            		)
            ),
        ));
    }
}
