<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Application\Model;

/**
 * Class SignInForm
 */
class SignUp extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('signup');

        $this->add([
            'type' => Element\Text::class,
            'name' => 'firstname',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => 'Prénom',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'lastname',
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Nom',
            ],
        ]);

        $this->add([
            'type' => Element\Email::class,
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'type'     => 'email',
                'placeholder' => 'Email',
            ],
        ]);

        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => 'Mot de passe',
            ],
        ]);

        $this->add([
            'type' => Element\Password::class,
            'name' => 'repassword',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => 'Confirmation de mot de passe',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'phone',
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'licence',
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-info btn-fill btn-wd btn-finish pull-right',
            ],
        ]);
    }
}