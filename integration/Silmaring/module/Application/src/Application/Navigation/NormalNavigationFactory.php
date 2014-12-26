<?php

namespace Application\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class NormalNavigationFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'normal_navigation';
    }
}