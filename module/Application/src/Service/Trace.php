<?php

namespace Volley\Services;

class Trace
{
    protected static $_instance = null;
    protected $_start           = null;
    protected $_stop            = null;
    protected $_trace           = null;
    protected $_debug           = false;

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setStart()
    {
        $this->_start = microtime(true);
    }

    public function setStop()
    {
        $this->_stop = microtime(true);
    }

    public function setMessage($message)
    {
        $delay = round((microtime(true) - $this->_start) * 1000, 4);
        $this->_trace[] = $message . ' - ' . $delay . 'ms';
    }

    public function dump()
    {
        if ($this->_debug) {
            foreach ($this->_trace as $trace) {
                error_log($trace);
                $this->reset();
            }
        }
    }

    public function reset()
    {
        $this->_trace = null;
        $this->_start = null;
        $this->_stop  = null;
    }
}