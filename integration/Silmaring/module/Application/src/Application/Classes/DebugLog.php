<?php

namespace Application\Classes;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class DebugLog
{
    protected static $debug = false;

    public static function __callStatic($method, $args)
    {
        if(!self::$debug) return null;

        $logger = new Logger();
        $writer = new Stream('./data/debug_log_'.(new \DateTime())->format('d_m_Y').'.txt');
        $logger->addWriter($writer);

        return $logger->$method($args[0]);
    }
};