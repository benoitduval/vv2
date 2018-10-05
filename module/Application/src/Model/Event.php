<?php

namespace Application\Model;

class Event extends AbstractModel
{

    const STAT_SERVE_FAULT  = 0;
    const STAT_RECEP_FAULT  = 1;
    const STAT_ATTACK_FAULT = 2;
    const STAT_SERVE_POINT  = 3;
    const STAT_ATTACK_POINT = 4;

    const LOSE              = 0;
    const WIN               = 1;

    protected $_id       = null;
    protected $_groupId  = null;
    protected $_name     = null;
    protected $_date     = null;
    protected $_place    = null;
    protected $_address  = null;
    protected $_city     = null;
    protected $_zipCode  = null;
    protected $_lat      = null;
    protected $_long     = null;
    protected $_sets     = null;
    protected $_stats    = null;
    protected $_score    = null;
    protected $_victory  = null;
    protected $_debrief  = null;
    protected $_reminder = null;
    protected $_video    = null;

    public function getFullAddress()
    {
        return $this->address . ', ' . $this->zipCode . ' ' . $this->city;
    }

    public function getSets()
    {
        return json_decode($this->_sets);
    }

    public function getStatsByType()
    {
        if (!$this->_stats) return [];
        $result = [];
        foreach (json_decode($this->_stats) as $set => $values) {
            foreach ($values as $key => $value) {
                $result[$key][] = $value;
            }
        }
        return $result;
    }

    public function getVideoEmbeding()
    {
        if ($this->video) {
            return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . $this->video . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        }
        return null;  
    }
}