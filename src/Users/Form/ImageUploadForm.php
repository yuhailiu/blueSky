<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ImageUploadForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('Upload');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'fileupload');
        $this->setAttribute('enctype','multipart/form-data');
        
               
        $this->add(array(
            'name' => 'imageupload',
            'attributes' => array(
                'type'  => 'file',
                'id' => 'uploadFile',
            ),
        )); 
        
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => $user_labels[update],
                'id' => 'submit-button'
            ),
        )); 
    }
}
