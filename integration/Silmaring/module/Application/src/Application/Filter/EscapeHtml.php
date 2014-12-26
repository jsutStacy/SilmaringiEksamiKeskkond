<?php

namespace Application\Filter;

use Zend\Escaper\Escaper;
use Zend\Filter\Exception;
use Zend\Filter\FilterInterface;

class EscapeHtml implements FilterInterface
{

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        $escaper = new Escaper('utf-8');
        return $escaper->escapeHtml($value);
    }
}