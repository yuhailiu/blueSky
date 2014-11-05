<?php
/*
 * filename : module/Users/src/Users/Form/UserSetForm.php create by Yuhai at 2014-1-21
 */
namespace Users\Form;

use Zend\Form\Form;

class UserSetForm extends Form
{

    public function __construct($name = null)
    {
        // get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('registerForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'id' => 'user_id_value'
            )
        ));
        
        $this->add(array(
            'name' => 'last_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'last_name'
            ),
            'options' => array(
                'label' => '*' . $user_labels[lastName] . ':'
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'first_name'
            ),
            'options' => array(
                'label' => '*' . $user_labels[firstName] . ':'
            )
        ));
        
        $this->add(array(
            'name' => 'sex',
            'attributes' => array(
                'type' => 'text',
                'id' => 'sex'
            ),
            'options' => array(
                'label' => $user_labels[sex] . ':'
            )
        ));
        $this->add(array(
            'name' => 'telephone1',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'telephone1',
                'placeholder' => $user_labels[telephone1Prompt]
            ),
            'options' => array(
                'label' => $user_labels[telephone1] . ':'
            )
        ));
        $this->add(array(
            'name' => 'telephone2',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'telephone2',
                'placeholder' => $user_labels[telephone2Prompt]
            ),
            'options' => array(
                'label' => $user_labels[telephone2] . ':'
            )
        ));
        $this->add(array(
            'name' => 'address',
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'form-element',
                'id' => 'address',
                'rows' => 8,
                'cols' => 60,
                'placeholder' => $user_labels[addressPrompt]
            )
            ,
            'options' => array(
                'label' => $user_labels[address] . ':'
            )
        ));
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'title',
                'placeholder' => $user_labels[positionPrompt],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $user_labels[position] . ':'
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
