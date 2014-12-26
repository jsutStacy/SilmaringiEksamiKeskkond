<?php
namespace Application\Helper;
use Zend\View\Helper\AbstractHelper;

class GetExistingDbValue extends AbstractHelper
{
    public function __invoke($object, $method, $default = '')
    {
        if(!isset($object) || !is_object($object) || is_null($object)) return $default;
        return $object->$method();
    }
}