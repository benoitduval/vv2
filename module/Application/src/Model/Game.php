<?php

namespace Application\Model;

class Game extends AbstractModel
{
    protected $_id      = null;
    protected $_eventId = null;
    protected $_userId  = null;
    protected $_numero  = null;
    protected $_start   = null;
    protected $_type    = null;
    protected $_quality = null;
    protected $_p1      = null;
    protected $_p2      = null;
    protected $_p3      = null;
    protected $_p4      = null;
    protected $_p5      = null;
    protected $_p6      = null;
    protected $_libero  = null;

    public function rotate() {
        $tmp = $this->p1;
        $this->p1 = $this->p2;
        $this->p2 = $this->p3;
        $this->p3 = $this->p4;
        $this->p4 = $this->p5;
        $this->p6 = $tmp;

        return $this;
    }

}