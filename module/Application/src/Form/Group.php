<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class SignInForm
 */
class Group extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('group');

        $this->setAttributes(array(
            'method' => 'post',
        ));

        $this->add([
            'type' => Element\Text::class,
            'name' => 'name',
            'options' => [
                'label' => 'Nom du groupe',
            ],
            'attributes' => [
                'class' => 'form-control groupName',
                'required' => 'required',
            ],
        ]);

        $this->add([
            'name' => 'eventDay[]',
            'type' => Element\Select::class,
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'value_options' => [
                    '-'          => '-',
                    'Monday'    => 'Monday',
                    'Tuesday'   => 'Tuesday',
                    'Wednesday' => 'Wednesday',
                    'Thursday'  => 'Thursday',
                    'Friday'    => 'Friday',
                    'Saturday'  => 'Saturday',
                    'Sunday'    => 'Sunday',
                ],
            ]
        ]);

        $this->add([
            'name' => 'time[]',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => '20:00'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'place[]',
            'options' => [
                'label' => 'Nom du Gymnase\Lieu',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'address[]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Adresse',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'city[]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Ville',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'zipCode[]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Code Postal',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'emails',
            'type' => Element\Textarea::class,
            'options' => [
                'label' => 'Emails',
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Emails (coma separated)'
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