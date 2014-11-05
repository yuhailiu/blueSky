<?php
namespace Users\Form;

use Zend\InputFilter\InputFilter;

class RegisterFilter extends InputFilter
{

    public function __construct()
    {
        $this->add(array(
            'name' => 'email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'domain' => true
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'last_name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                    'name' => 'Zend\Filter\StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 18
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                    'name' => 'Zend\Filter\StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 18
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                    'name' => 'Zend\Filter\StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 6,
                        'man' => 22
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'confirm_password',
            'filters' => array(
            		array(
            				'name' => 'StripTags',
            				'name' => 'Zend\Filter\StringTrim'
            		)
            ),
            'required' => true
        ));
    }
}
