<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class SignInForm
 */
class Reset extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('signin');

        $this->add([
            'type' => Element\Email::class,
            'name' => 'email',
            'options' => [
                'label' => 'E-mail',
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Votre E-mail',
                'required' => 'required',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Connexion',
                'class' => 'btn btn-primary',
            ],
        ]);
    }

}