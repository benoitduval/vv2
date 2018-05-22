<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class SignInForm
 */
class Result extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('result');

        $this->setAttributes([
            'method' => 'post',
            'id'     => 'wizardForm'
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set1Team1',
            'attributes' => [
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Set 1'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set1Team2',
            'attributes' => [
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Set 1'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set2Team1',
            'attributes' => [
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Set 2'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set2Team2',
            'attributes' => [
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Set 2'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set3Team1',
            'attributes' => [
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Set 3'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set3Team2',
            'attributes' => [
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Set 3'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set4Team1',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Set 4'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set4Team2',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Set 4'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set5Team1',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Set 5'
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'set5Team2',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Set 5'
            ],
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'debrief',
            'attributes' => [
                'class' => 'form-control',
                'rows' => '8',
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