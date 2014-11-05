<?php
/*
 * filename : module/Users/src/Users/Form/UserSetFilter.php create by Yuhai at 2014-1-21
 */
namespace Users\Form;

use Zend\InputFilter\InputFilter;

class UserSetFilter extends InputFilter
{

    public function __construct()
    {
        $this->add(array(
            'name' => 'last_name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags'
                ),
                array('name' => 'Zend\Filter\StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 18
                    ),
                )
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags'
                ),
                array('name' => 'Zend\Filter\StringTrim'),
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
        
        // not required fields
        $this->add(array(
            'name' => 'telephone1',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StripTags'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 20
                    )
                ),
            )
            
        ));
        $this->add(array(
            'name' => 'telephone2',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StripTags'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 20
                    )
                )
            )
        ));
        $this->add(array(
            'name' => 'title',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StripTags'
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
        		'name' => 'sex',
        		'required' => false,
        		'filters' => array(
        				array(
        						'name' => 'Int'
        				)
        		),
//         		'validators' => array(
//         				array(
//         						'name' => 'StringLength',
//         						'options' => array(
//         								'encoding' => 'UTF-8',
//         								'max' => 1
//         						)
//         				)
//         		)
        ));
        
    }
}
