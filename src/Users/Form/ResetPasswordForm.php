<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ResetPasswordForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/index_label.php';
        
        parent::__construct('ResetPasswordForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'password',
                'class' => 'form-element',
                'placeholder' => $labels[passwordPrompt],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $labels[password],
            ),
        )); 
        
        $this->add(array(
            'name' => 'confirmPassword',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'confirmPassword',
                'class' => 'form-element',
                'placeholder' => $labels[confirmPassword],
            ),
            'options' => array(
                'label' => $labels[confirmPassword],
            ),
        )); 
        
        $this->add(array(
        		'name' => 'submit',
        		'attributes' => array(
        				'type'  => 'submit',
        				'value' => $labels[resetPassword],
        				'id' => 'resetPassword-submit',
        				'class' => 'rc-button rc-button-submit'
        		),
        ));
        
        
    }
}
