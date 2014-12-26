<?php

namespace Application\Navigation;

use AmaCategories\Entity\Category;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Db\Sql\Predicate\Expression AS Expr;

class WpNavigationFactory extends DefaultNavigationFactory
{
    /**
     * @var $adapter
     */
    protected $adapter;

    /**
     * Service locator
     * @var $sm
     */
    protected $sm;

    protected function getName()
    {
        return 'wp_navigation';
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    protected function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);

        if (null === $this->pages) {
            $wpPages = $this->getWpPages();

            if(!$wpPages) return $this->pages;

            $cache = $this->getServiceLocator()->get('zcache');
            $key = 'wp' ;
            $success = false;
            $configuration['navigation'][$this->getName()] = array();
            $navigation = $cache->getItem($key, $success);

            if(empty($navigation)) $success = false;
            $success = false;

            if ( !$success ) {
                foreach ($wpPages as $page) {
                    $navigation[$page['post_title']] = array(
                        'label' => $page['post_title'],
                        'route' => 'home'
                    );
                }
                $cache->setItem($key, $navigation);
            }
            $configuration['navigation'][$this->getName()] = $navigation;

            if (!isset($configuration['navigation'])) {
                throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
            }
            if (!isset($configuration['navigation'][$this->getName()])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Failed to find a navigation container by the name "%s"',
                    $this->getName()
                ));
            }

            $application = $this->getServiceLocator()->get('Application');
            $routeMatch  = $application->getMvcEvent()->getRouteMatch();
            $router      = $application->getMvcEvent()->getRouter();
            $pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);

            $this->pages = $this->injectComponents($pages, $routeMatch, $router);
        }
        return $this->pages;
    }


   public function getWpPages($taxId = 3)
   {
       /*$adapter = $this->getAdapter();
       $sql = new Sql($adapter);
       $select = $sql->select();
       $select->from(array('wtr' => 'wp_term_relationships'))
               ->join(array('op' => 'wp_posts'), 'op.id=wtr.object_id')
               ->join(
                    array('wopm' => 'wp_postmeta'),
                    new Expr("(wopm.post_id=op.id AND wopm.meta_key='_menu_item_object' AND wopm.meta_value='page')"),
                    $select::SQL_STAR,
                    $select::JOIN_LEFT
           )
               ->join(
                   array('wopm_c' => 'wp_postmeta'),
                   new Expr("(wopm_c.post_id=op.id AND wopm_c.meta_key='_menu_item_object' AND wopm_c.meta_value='category')"),
                   $select::SQL_STAR,
                   $select::JOIN_LEFT
           )
               ->join(array('p' => 'wp_posts'), new Expr("(p.id=wopm.meta_value, p.post_type='page' AND p.post_status='publish')"), $select::SQL_STAR, $select::JOIN_LEFT)
               ->join(array('p' => 'wp_posts'), new Expr("(p.id=wopm.meta_value, p.post_type='page' AND p.post_status='publish')"), $select::SQL_STAR, $select::JOIN_LEFT)
               ->where
               ->nest->equalTo('p.post_type', 'page')
               ->and->equalTo('p.post_status', 'publish');

       $statement = $sql->prepareStatementForSqlObject($select);
       $sql_result = $statement->execute();

       //print_r($sql_result);
       if($sql_result->count() > 0){
           $results = new ResultSet();
           $resultsArray = $results->initialize($sql_result)->toArray();
       }*/

       return array();
   }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
    }

    /**
     * @param $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    /**
     * @return mixed
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }

}