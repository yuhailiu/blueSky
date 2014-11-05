<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ChangePasswordForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('ChangePassword');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        
	   $this->add(array(
            'name' => 'old_password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'class' => 'form-element',
                'autofocus' => 'autofocus',
                'placeholder' => $user_labels[passwordPrompt]
            ),
            'options' => array(
                'label' => $user_labels[oldPassword],
            ),
        )); 
	   $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'class' => 'form-element',
                'id' => 'password',
                'placeholder' => $user_labels[passwordPrompt]
            ),
            'options' => array(
                'label' => $user_labels[newPassword],
            ),
        )); 
	   $this->add(array(
            'name' => 'confirm_password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'class' => 'form-element',
                'placeholder' => $user_labels[passwordPrompt]
            ),
            'options' => array(
                'label' => $user_labels[confirmNewPassword],
            ),
        )); 


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'class' => 'form-element',
                'value' => $user_labels[update],
                'id' => 'submit-button',
                'style' => 'width: 100px',
                'class' => 'rc-button rc-button-submit'
            ),
        )); 
    }
}
