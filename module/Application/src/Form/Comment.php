<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class Comment
 */
class Comment extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('comment');

        $this->setAttributes(array(
            'method' => 'post',
        ));

        $this->add([
            'name' => 'comment',
            'type' => Element\Textarea::class,
            'options' => [
                'label' => 'Commentaire',
            ],
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'rows' => 5
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-fill btn-info',
            ],
        ]);
    }
}