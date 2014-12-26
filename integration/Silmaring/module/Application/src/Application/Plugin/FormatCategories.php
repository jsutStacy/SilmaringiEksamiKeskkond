<?php
namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class FormatCategories extends AbstractPlugin
{
    protected $sm;

    protected $em;

    public function __construct($serviceLocator = null)
    {
        $this->sm = $serviceLocator;
    }

    public function doFormat($categories)
    {
        $formatedCategories = array();
        foreach($categories as $category) {
            if (!$category['p_id']){
                $formatedCategories[0][] = $category;
            }
            else {
                $formatedCategories[$category['p_id']][] = $category;
            }
        }
        return $formatedCategories;
    }


    public function getCategoryTree($categories)
    {
        $formatedCategories = array();
        foreach($categories as $category) {
            if (!$category['p_id']){
                $category['subs'] = $this->getCategoryTreeChildren($categories, $category['c_id']);
                $formatedCategories[$category['c_id']] = $category;
            }
        }
        return $formatedCategories;
    }

    public function getCategoryTreeChildren($categories, $pid)
    {
        $formatedCategories = array();
        foreach($categories as $category) {
            if ($category['p_id']==$pid){
                $category['subs'] = $this->getCategoryTreeChildren($categories, $category['c_id']);
                $formatedCategories[$category['c_id']] = $category;
            }
        }
        return $formatedCategories;
    }

    public function getChildren($categories, $curCat)
    {
        $childCats = array();
        if(!isset($categories[$curCat])) return $curCat;
        $cats = $categories[$curCat];
        if ( $cats ) {
            return $this->checkForChildCats($categories, $cats, $childCats);
        }
        return $curCat;
    }

    public function checkForChildCats($categories, $cats, $childCats)
    {
        foreach($cats as $c) {
            $cats2 = $categories[$c['c_id']];
            if($cats2) {
                $childCats =  $this->checkForChildCats($categories, $cats2, $childCats);
            }
            else {
                array_push($childCats, $c['c_id']);
            }
        }
        return $childCats;
    }

    public function getPath($categories, $parent)
    {
        return $this->formatAsPaths($categories, $parent);
    }

    public function formatAsPaths($categories, $parent, $paths = array(), $trueParent = '')
    {
        if(empty($trueParent))
             $trueParent = $parent;

        foreach($categories as $category) {
            if ( $parent !=  $category['c_id'] ) continue;
            $paths[$trueParent][] = $category;
            if ($category['p_id']) {
                return $this->formatAsPaths($categories, $category['p_id'], $paths, $trueParent);
            }
        }
        return $paths;
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->sm()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }
}