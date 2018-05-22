<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class SignInForm
 */
class Holiday extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('recurent');

        $this->setAttributes(array(
            'method' => 'post',
        ));

        $this->add([
            'name' => 'from',
            'type' => Element\Hidden::class,
            'attributes' => [
                'required'    => 'required',
                'class'       => 'form-control holiday-from-input',
                'placeholder' => 'ex: 26/12/2017'
            ],
        ]);

        $this->add([
            'name' => 'to',
            'type' => Element\Hidden::class,
            'attributes' => [
                'required'    => 'required',
                'class'       => 'form-control holiday-to-input',
                'placeholder' => 'ex: 29/12/2017'
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Ajouter',
                'class' => 'btn btn-info btn-fill btn-wd btn-finish pull-right',
            ],
        ]);
    }
}