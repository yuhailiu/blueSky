<?php
/*
 * filename : module/Users/src/Users/Form/OrgSearchForm.php create by Yuhai at 2014-2-24
 */
namespace Users\Form;

use Zend\Form\Form;

class OrgSearchForm extends Form
{

    public function __construct($name = null)
    {
        // get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('orgSearchForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        
        $this->add(array(
            'name' => 'org_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'org_name',
                'placeholder' => $user_labels[orgNamePrompt]
            ),
            'options' => array(
                'label' => '*' . $user_labels[orgName] . ':'
            )
        ));
                
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => $user_labels[search],
                'id' => 'org_submit_button',
                'style' => 'margin-bottom: 0px; left: 40px; bottom: 6px;'
            )
        )
        );
    }
}
