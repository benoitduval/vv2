<?php

namespace Admin\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Application\Model;

class Search extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('recurent');

        $this->setAttributes(array(
            'method' => 'post',
        ));

        // USER
        $this->add([
            'name' => 'user-id',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'user-email',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);


        $this->add([
            'name' => 'user-firstname',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'user-lastname',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'user-display',
            'type' => Element\Select::class,
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'value_options' => [
                    0 => '',
                    Model\User::DISPLAY_SMALL => 'Small',
                    Model\User::DISPLAY_LARGE => 'Large',
                    Model\User::DISPLAY_TABLE => 'Table',
                ],
            ]
        ]);

        $this->add([
            'name' => 'user-status',
            'type' => Element\Select::class,
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'value_options' => [
                    0 => '',
                    Model\User::CONFIRMED => 'Confirmed',
                    Model\User::HAS_TO_CONFIRM => 'Not Confirmed',
                ],
            ]
        ]);

        // GROUP
        $this->add([
            'name' => 'group-id',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'group-name',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        // Event
        $this->add([
            'name' => 'event-id',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'event-name',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'event-date',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control date-only-picker',
            ],
        ]);

        $this->add([
            'name' => 'event-groupId',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Rechercher',
                'class' => 'btn btn-fill btn-primary',
            ],
        ]);
    }
}