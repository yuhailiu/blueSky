<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ConfirmCaptchaForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/index_label.php';
        
        parent::__construct('ConfirmCaptchaForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'captcha',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'captcha',
                'class' => 'form-element',
                'placeholder' => $labels[password],
                'autofocus' => 'autofocus',
                'maxlength' => 6
            ),
            'options' => array(
                'label' => $labels[randomPassword],
            ),
        )); 
        
        $this->add(array(
        		'name' => 'submit',
        		'attributes' => array(
        				'type'  => 'submit',
        				'value' => $labels[nextStep],
        				'id' => 'captcha-submit',
        				'class' => 'rc-button rc-button-submit'
        		),
        ));
        
        
    }
}
