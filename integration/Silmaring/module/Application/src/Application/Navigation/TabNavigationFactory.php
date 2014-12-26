<?php

namespace Application\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class TabNavigationFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'tab_navigation';
    }
}