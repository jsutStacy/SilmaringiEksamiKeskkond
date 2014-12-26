<?php
namespace Application\Plugin;


use Zend\Filter\FilterChain;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Escaper\Escaper;
use Zend\Filter;

class CleanInputs extends AbstractPlugin
{
    public function clean($input = '')
    {
        if(empty($input)) return '';

        $filterChain = new FilterChain();
        $filterChain->attach(new Filter\StripTags());
        $input = $filterChain->filter($input);

        $escaper = new Escaper();
        $input = $escaper->escapeHtml($input);

        return $input;
    }
}
