<?php
namespace Application\Helper;
use Zend\View\Helper\AbstractHelper;

class GetCategoryPath extends AbstractHelper
{
    public function __invoke($categories, $parent)
    {
        return $this->formatAsPaths($categories, $parent);
    }

    public function formatAsPaths($categories, $parent, $paths = array(), $trueParent = '')
    {
        if(empty($trueParent))
            $trueParent = $parent;

        foreach($categories as $category) {
            if ( $parent !=  $category['c_id'] ) continue;
            $paths[] = $category;
            if ($category['p_id']) {
                return $this->formatAsPaths($categories, $category['p_id'], $paths, $trueParent);
            }
        }
        return $paths;
    }
}