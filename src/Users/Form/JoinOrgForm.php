<?php
/*
 * filename : module/Users/src/Users/Form/OrgSearchForm.php create by Yuhai at 2014-2-24
 */
namespace Users\Form;

use Zend\Form\Form;

class JoinOrgForm extends Form
{

    public function __construct($name = null)
    {
        // get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('joinOrgForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        
        $this->add(array(
            'name' => 'additionInfo',
            'attributes' => array(
                'type' => 'textarea',
                'id' => 'additionInfo',
                'rows' => 8,
                'cols' => 80,
                'style' => 'width: 350px',
                'placeholder' => $user_labels[additionInfoPrompt]
            ),
            'options' => array(
                'label' => $user_labels[additionInfo] . ':'
            )
        ));
                
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => $user_labels[submit],
                'id' => 'joinOrg_submit_button',
                'style' => 'margin-bottom: 0px; left: 40px; bottom: 6px;'
            )
        )
        );
    }
}
