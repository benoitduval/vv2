<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Application\Model;

/**
 * Class Password
 */
class Password extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('password');

        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
        ]);

        $this->add([
            'type' => Element\Password::class,
            'name' => 'repassword',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}