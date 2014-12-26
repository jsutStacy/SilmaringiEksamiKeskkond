<?php
namespace Application\Helper;
use Zend\View\Helper\AbstractHelper;

class GetIsNew extends AbstractHelper
{
    public function __invoke($date, $time = '1 week')
    {
        if (strtotime($date . ' +' . $time)>strtotime('now')) {
            return true;
        }
        return false;
    }

}