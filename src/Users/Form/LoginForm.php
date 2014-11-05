<?php
namespace Users\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/index_label.php';
        
        parent::__construct('Login');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'email',
				'required' => 'required',
                'id' => 'Email',
                'placeholder' => $labels[email],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $labels[email],
            ),
        )); 
        
	   $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'Passwd',
                'placeholder' => $labels[password]
            ),
            'options' => array(
                'label' => $labels[password],
            ),
        )); 


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => $labels[login],
                'id' => 'submit-button',
                'class' => 'rc-button rc-button-submit'
            ),
        )); 
    }
}
