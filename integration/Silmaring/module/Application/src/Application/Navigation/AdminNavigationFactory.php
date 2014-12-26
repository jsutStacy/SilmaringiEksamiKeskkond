<?php

namespace Application\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class AdminNavigationFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'admin_navigation';
    }
}