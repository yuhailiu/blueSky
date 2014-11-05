<?php
/*
 * filename : module/Users/src/Users/Form/UserSetForm.php create by Yuhai at 2014-1-21
 */
namespace Users\Form;

use Zend\Form\Form;

class OrgSetForm extends Form
{

    public function __construct($name = null)
    {
        // get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('orgSetForm');
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
            'name' => 'org_website',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'org_website',
                'placeholder' => $user_labels[webSitePrompt],
            ),
            'options' => array(
                'label' => $user_labels[webSite] . ':'
            )
        ));
        $this->add(array(
            'name' => 'org_tel',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'org_tel',
                'placeholder' => $user_labels[telephone1Prompt]
            ),
            'options' => array(
                'label' => $user_labels[telephone] . ':'
            )
        ));
        $this->add(array(
            'name' => 'org_des',
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'form-element',
                'rows' => 8,
                'cols' => 60,
                'id' => 'org_des',
                'placeholder' => $user_labels[orgInfo]
            ),
            'options' => array(
                'label' => $user_labels[orgInfo] . ':'
            )
        ));
        $this->add(array(
            'name' => 'org_address',
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'form-element',
                'rows' => 8,
                'cols' => 60,
                'id' => 'org_address',
                'placeholder' => $user_labels[addressPrompt]
            )
            ,
            'options' => array(
                'label' => $user_labels[address] . ':'
            )
        ));
                
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => $user_labels[update],
                'id' => 'submit-button',
                'style' => 'width: 100px;'
            )
        )
        );
    }
}
