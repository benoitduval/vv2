<?php
namespace Application\Form;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class Profile extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter($options['userId']);
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('image-file');
        $file->setLabel('Avatar Image Upload')
             ->setAttribute('id', 'image-file')
             ->setAttribute('data-plugin', 'dropify')
             ->setAttribute('data-max-file-size', '1M')
             ->setAttribute('id', 'image-file');
        $this->add($file);
    }

    public function addInputFilter($userId)
    {
        $inputFilter = new InputFilter\InputFilter();

        // File Input
        $fileInput = new InputFilter\FileInput('image-file');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => getcwd() . '/public/img/avatars/' . $userId . '.png',
                'randomize' => false,
                'overwrite' => true,
            )
        );
        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
    }
}