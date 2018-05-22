<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class SignInForm
 */
class Training extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('recurent');

        $this->setAttributes(array(
            'method' => 'post',
        ));

        $this->add([
            'type' => Element\Text::class,
            'name' => 'name',
            'options' => [
                'label' => 'Nom',
            ],
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
        ]);


        $this->add([
            'name' => 'eventDay',
            'type' => Element\Select::class,
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control',
            ],
            'options' => [
                'value_options' => [
                    ''          => '',
                    'Monday'    => 'Lundi',
                    'Tuesday'   => 'Mardi',
                    'Wednesday' => 'Mercredi',
                    'Thursday'  => 'Jeudi',
                    'Friday'    => 'Vendredi',
                    'Saturday'  => 'Samedi',
                    'Sunday'    => 'Dimanche',
                ],
            ]
        ]);

        $this->add([
            'name' => 'emailDay',
            'type' => Element\Select::class,
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control',
            ],
            'options' => [
                'value_options' => [
                    ''          => '',
                    'Monday'    => 'Lundi',
                    'Tuesday'   => 'Mardi',
                    'Wednesday' => 'Mercredi',
                    'Thursday'  => 'Jeudi',
                    'Friday'    => 'Vendredi',
                    'Saturday'  => 'Samedi',
                    'Sunday'    => 'Dimanche',
                ],
            ]
        ]);
        $this->add([
            'name' => 'time',
            'type' => Element\Text::class,
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control timepicker',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'place',
            'options' => [
                'label' => 'Nom du Gymnase\Lieu',
            ],
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
        ]);

        $this->add([
            'name' => 'address',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Adresse',
            ],
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'city',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Ville',
            ],
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'zipCode',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Code Postal',
            ],
            'attributes' => [
                'required' => 'required',
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'long',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Longitude',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'lat',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Latittude',
            ],
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