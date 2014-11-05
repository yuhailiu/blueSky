<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{

    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/index_label.php';
        
        parent::__construct('registerForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'last_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'last_name',
                'placeholder' => $labels[lastNamePrompt]
            ),
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'first_name',
                'placeholder' => $labels[firstNamePrompt],
                'autofocus' => 'autofocus'
            ),
        ));
        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'email',
                'placeholder' => $labels[email]."-".$labels[emailPrompt],
            ),
        ));
        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'password',
                'placeholder' => $labels[passwordPrompt]
            ),
        ));
        
        $this->add(array(
            'name' => 'confirm_password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-element',
                'placeholder' => $labels[confirmPassword]
            ),
            'options' => array(
                'label' => $labels[confirmPassword]
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => $labels[register],
                'id' => 'submit-button'
            )
            
        ));
    }
}
