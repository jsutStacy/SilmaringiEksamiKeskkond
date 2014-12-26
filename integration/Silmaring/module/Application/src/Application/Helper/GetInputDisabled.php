<?php
namespace Application\Helper;
use Zend\View\Helper\AbstractHelper;

class GetInputDisabled extends AbstractHelper
{
    public function __invoke($result)
    {
       if($result) return 'disabled';

        return null;
    }

}