<?php
namespace AmaCategories\Entity\Repository;

use Application\Classes\DebugLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;


class CategoryRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cache = true;

    /**
     * @var int
     */
    protected $cacheTime = 86400;


    /**
     * @return array
     */
    public function findAllCategories()
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c')
            ->from('AmaCategories\Entity\Category', 'c')
            ->orderBy('c.order', 'ASC');
        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCategoriesAll');

        try {
            $result = $getQuery->getResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param int $depth
     * @return array
     */
    public function findAllCategoriesScalar($depth = 1)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c', 'p')
            ->from('AmaCategories\Entity\Category', 'c')
            ->leftJoin('AmaCategories\Entity\Category', 'p', 'WITH', 'p.id=c.parent')
            ->where('c.depth <= :depth')
            ->orderBy('c.order', 'ASC')
            ->setParameter('depth', $depth);
        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCategoriesAllS');

        try {
            $result = $getQuery->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param int $status
     * @return array
     */
    public function findCategories($status = 1)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c')
            ->from('AmaCategories\Entity\Category', 'c')
            ->where('c.status=:status')
            ->andWhere('c.parent IS NULL')
            ->orderBy('c.order', 'ASC')
            ->setParameter('status', $status);
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCategories');

        try {
            $result = $getQuery->getResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $parent
     * @param int $status
     * @return array
     */
    public function findSubCategories($parent, $status = 1)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c')
            ->from('AmaCategories\Entity\Category', 'c')
            ->where('c.status=:status')
            ->andWhere('c.parent=:parent')
            ->orderBy('c.order', 'ASC')
            ->setParameter('status', $status)
            ->setParameter('parent', $parent);
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSubCategories');

        try {
            $result = $getQuery->getResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function findCategoryById($categoryId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c')
            ->from('AmaCategories\Entity\Category', 'c')
            ->where('c.id=:category')
            ->setParameter('category', $categoryId);
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSubCategories');

        try {
            $result = $getQuery->getResult();
            if (isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $right
     * @return array
     */
    public function updateLeft($right)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->update('AmaCategories\Entity\Category', 'c')
            ->set('c.left', 'c.left + :nr')
            ->where('c.left > :right')
            ->setParameter('right', $right)
            ->setParameter('nr', 2);
        $getQuery = $query->getQuery();
        $getQuery->useResultCache(false);
        $getQuery->execute();
        $this->getEntityManager()->clear();
    }

    /**
     * @param $right
     * @return array
     */
    public function updateRight($right)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->update('AmaCategories\Entity\Category', 'c')
            ->set('c.right', 'c.right + :nr')
            ->where('c.right > :right')
            ->setParameter('right', $right)
            ->setParameter('nr', 2);
        $getQuery = $query->getQuery();
        $getQuery->useResultCache(false);
        $getQuery->execute();
        $this->getEntityManager()->clear();
    }

    /**
     * @param $right
     * @param $width
     * @return array
     */
    public function updateLeftWithWidth($right, $width)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->update('AmaCategories\Entity\Category', 'c')
            ->set('c.left', 'c.left - :nr')
            ->where('c.left > :right')
            ->setParameter('right', $right, $width)
            ->setParameter('nr', $width);
        $getQuery = $query->getQuery();
        $getQuery->execute();
    }

    /**
     * @param $right
     * @param $width
     * @return array
     */
    public function updateRightWithWidth($right, $width)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->update('AmaCategories\Entity\Category', 'c')
            ->set('c.right', 'c.right - :nr')
            ->where('c.right > :right')
            ->setParameter('right', $right)
            ->setParameter('nr', $width);
        $getQuery = $query->getQuery();
        $getQuery->execute();
    }

    /**
     * @param $left
     * @param $right
     * @return array
     */
    public function deleteByLeftAndRight($left, $right)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->delete('AmaCategories\Entity\Category', 'c')
            ->where($query->expr()->between('c.left', ':left', ':right'))
            ->setParameter('right', $right)
            ->setParameter('left', $left);
        $getQuery = $query->getQuery();
        $getQuery->execute();
    }

    /**
     * @param $category
     * @return array
     */
    public function findChildren($category)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c', 'cp')
            ->from('AmaCategories\Entity\Category', 'c')
            ->join('AmaCategories\Entity\Category', 'cp')
            ->where($query->expr()->between('c.left', 'cp.left', 'cp.right'))
            ->andWhere('c.id = :category')
            ->setParameter('category', $category)
            ->addOrderBy('c.left', 'ASC')
            ->orderBy('c.order', 'ASC');
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSubCategories');
        try {
            return $getQuery->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $category
     * @return array
     */
    public function setParentNull($category)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->update('AmaCategories\Entity\Category', 'c')
            ->set('c.parent', ':null')
            ->where('c.id = :category')
            ->setParameter('category', $category)
            ->setParameter('null', null);
        $getQuery = $query->getQuery();
        $getQuery->execute();
    }


    /**
     * @return array
     */
    public function findAllTopCategories()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('c', 'cp')
            ->from('AmaCategories\Entity\Category', 'c')
            ->from('AmaCategories\Entity\Category', 'cp')
            ->where($query->expr()->between('c.left', 'cp.left', 'cp.right'))
            ->andWhere('c.parent IS NULL')
            ->groupBy('c.id')
            ->addOrderBy('c.left', 'ASC')
            ->orderBy('c.order', 'ASC');
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCategoriesOnlyTop');

        try {
            $result = $getQuery->getResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }


    /**
     * @param $parent
     * @param $depth
     * @return array
     */
    public function findAllCategoriesById($parent, $depth)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('c', 'cp', 'cpp')
            ->from('AmaCategories\Entity\Category', 'c')
            ->from('AmaCategories\Entity\Category', 'cp')
            ->join('AmaCategories\Entity\Category', 'cpp', 'WITH', 'cpp.id=c.parent')
            ->where($query->expr()->between('c.left', 'cp.left', 'cp.right'))
            ->andWhere('cp.id=:parent')
            ->andWhere('c.id!=:parent')
            ->andWhere('c.depth <= :depth')
            ->groupBy('c.id')
            ->addOrderBy('c.left', 'ASC')
            ->orderBy('c.order', 'ASC')
            ->setParameter('parent', $parent)
            ->setParameter('depth', $depth);
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AllCategoriesById' . $parent);
        //print_r($getQuery->getSQL());
        try {
            $result = $getQuery->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function findCategoryTreeById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('c','cp')
            ->from('AmaCategories\Entity\Category', 'c')
            ->from('AmaCategories\Entity\Category', 'cp')
            ->where($query->expr()->between('c.left', 'cp.left', 'cp.right'))
            ->andWhere('c.id=:parent')
            ->addOrderBy('c.left', 'ASC')
            ->orderBy('c.order', 'ASC')
            ->setParameter('parent', $id);
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AllCategoriesById' . $parent);
       // print_r($getQuery->getSQL());
        try {
            $result = $getQuery->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $category
     * @return array
     */
    public function findCatTreeAsIds($category) {
       $results = $this->findCategoryTreeById($category);
       $returnArray = array();
       foreach($results as $row) {
            if(in_array($row['cp_id'], $returnArray)) continue;
            $returnArray[] = $row['cp_id'];
       }
        arsort($returnArray);
       return $returnArray;
    }

    /**
     * @param $depth
     * @return array
     */
    public function findAllCategoriesByDepth($depth = 1)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('c', 'cp', 'p')
            ->from('AmaCategories\Entity\Category', 'c')
            ->from('AmaCategories\Entity\Category', 'cp')
            ->leftJoin('AmaCategories\Entity\Category', 'p', 'WITH', 'p.id=c.parent')
            ->where($query->expr()->between('c.left', 'cp.left', 'cp.right'))
            ->andWhere('c.depth <= :depth')
            ->groupBy('c.id')
            ->addOrderBy('c.left', 'ASC')
            ->orderBy('c.order', 'ASC')
            ->setParameter('depth', $depth);
        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCategoriesAllS');
        try {
            $result = $getQuery->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

}