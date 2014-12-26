<?php
namespace AmaMaterials\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

class MaterialRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cache = true;

    /**
     * @var int
     */
    protected $cacheTime = 86400;


    public function findFiles($user, $categories = '', $categoryId = '', $args = array())
    {
        $catArray = array();
        if (!is_array($categories)) $catArray[] = $categories;
        else $catArray = $categories;

        $start = $args['start'];
        $limit = $args['limit'];

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('f', 'u', 'c');
        $query->select($select)
            ->from('AmaMaterials\Entity\File', 'f')
            ->leftJoin('AmaMaterials\Entity\FileDeleted', 'fd', 'WITH', 'fd.deleter=f.user AND fd.file=f.id')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=f.user')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=f.category')
            ->where($query->expr()->andX(
                $query->expr()->eq('f.user', ':user'),
                $query->expr()->isNull('fd.file'),
                $query->expr()->orX(
                    $query->expr()->eq('f.type', ':type'),
                    $query->expr()->eq('f.type', ':type2'),
                    $query->expr()->eq('f.type', ':type3')
                )
            ))
            ->setParameter("user", $user)
            ->setParameter('type', 'file')
            ->setParameter('type2', 'image')
            ->setParameter('type3', 'presentation');
        if ($categoryId) {
            $ors = array();
            foreach ($catArray as $category) {
                $ors[] = $query->expr()->eq('f.category', $category);
            }
            if ($ors[0])
                $query->andWhere(join(' OR ', $ors));
        }

        $query->groupBy('f.id')
            ->orderBY('f.date_added', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $getQuery = $query->getQuery();

        if ($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaFiles' . $categoryId . $user->getId());
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaFiles' . $user->getId());

        try {
            return $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findFilesCount($user, $categories = '', $categoryId = '', $args = array())
    {
        $catArray = array();
        if (!is_array($categories)) $catArray[] = $categories;
        else $catArray = $categories;

        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(f.id)')
            ->from('AmaMaterials\Entity\File', 'f')
            ->leftJoin('AmaMaterials\Entity\FileDeleted', 'fd', 'WITH', 'fd.deleter=f.user AND fd.file=f.id')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=f.user')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=f.category')
            ->where($query->expr()->andX(
                $query->expr()->eq('f.user', ':user'),
                $query->expr()->isNull('fd.file'),
                $query->expr()->orX(
                    $query->expr()->eq('f.type', ':type'),
                    $query->expr()->eq('f.type', ':type2'),
                    $query->expr()->eq('f.type', ':type3')
                )
            ))
            ->setParameter("user", $user)
            ->setParameter('type', 'file')
            ->setParameter('type2', 'image')
            ->setParameter('type3', 'presentation');
        if ($categoryId) {
            $ors = array();
            foreach ($catArray as $category) {
                $ors[] = $query->expr()->eq('f.category', $category);
            }
            if ($ors[0])
                $query->andWhere(join(' OR ', $ors));
        }

        $query->orderBY('f.date_added', 'DESC');

        $getQuery = $query->getQuery();

        if ($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaFilesCount' . $categoryId . $user->getId());
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaFilesCount' . $user->getId());

        try {
            return $getQuery->getSingleScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findFilesSentToClasses($user, $file)
    {
        if (empty($file)) return array();

        $limit = 3;

        $query2 = $this->getEntityManager()->createQueryBuilder();
        $subQ2 = $query2
            ->select('COUNT(DISTINCT fv.viewer)')
            ->from('AmaMaterials\Entity\FileView', 'fv')
            ->where('fv.fileClass = fc.id');

        $query1 = $this->getEntityManager()->createQueryBuilder();
        $subQ = $query1
            ->select('COUNT(stc.id)')
            ->from('AmaUsers\Entity\StudentClass', 'stc')
            ->where('stc.class = fc.class');

        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('sc', 'fc', 's')
            ->addSelect(sprintf('(%s) AS viewedCount', $subQ2->getDql()))
            ->addSelect(sprintf('(%s) AS studentCount', $subQ->getDql()))
            ->from('AmaMaterials\Entity\FileClass', 'fc')
            ->join('AmaMaterials\Entity\LessonPlanFile', 'lpf', 'WITH', 'lpf.id=fc.file')
            ->join('AmaSchools\Entity\SchoolClass', 'sc', 'WITH', 'sc.id=fc.class')
            ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=sc.school')
            ->where('fc.sender=:user')
            ->andWhere('lpf.file=:file')
            ->setParameter("user", $user)
            ->setParameter('file', $file);

        $query->orderBY('fc.dateAdded', 'DESC')
            ->setMaxResults($limit);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaFilesSentToClasses' . $file . $user->getId());

        try {
            return $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function countMaterialsByTypes()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('imagesCount', 'imagesCount');
        $rsm->addScalarResult('filesCount', 'filesCount');
        $rsm->addScalarResult('videosCount', 'videosCount');
        $rsm->addScalarResult('testsCount', 'testsCount');
        $rsm->addScalarResult('presentationsCount', 'presentationsCount');
        $rsm->addScalarResult('worksheetsCount', 'worksheetsCount');


        $SQL = "SELECT
                     (SELECT COUNT(DISTINCT f.id) FROM files f LEFT JOIN file_deleted fd ON fd.file_id=f.id WHERE type='image' AND fd.file_id IS NULL) AS imagesCount,
                     (SELECT COUNT(DISTINCT f.id) FROM files f LEFT JOIN file_deleted fd ON fd.file_id=f.id WHERE type='file' AND fd.file_id IS NULL) AS filesCount,
                     (SELECT COUNT(DISTINCT f.id) FROM files f LEFT JOIN file_deleted fd ON fd.file_id=f.id WHERE type='presentation' AND fd.file_id IS NULL) AS presentationsCount,
                     (SELECT COUNT(DISTINCT f.id) FROM files f LEFT JOIN file_deleted fd ON fd.file_id=f.id WHERE type='video' AND fd.file_id IS NULL) AS videosCount,
                     (SELECT COUNT(DISTINCT t.id) FROM files f JOIN tests t ON t.id=f.test_id LEFT JOIN file_deleted fd ON fd.file_id=f.id WHERE  type='test' AND fd.file_id IS NULL) AS testsCount,
                     (SELECT COUNT(DISTINCT w.id) FROM files f JOIN worksheets w ON w.id=f.worksheet_id LEFT JOIN file_deleted fd ON fd.file_id=f.id WHERE type='worksheet' AND fd.file_id IS NULL) AS worksheetsCount
                FROM files LIMIT 1";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query->useResultCache($this->cache, $this->cacheTime, 'AmaMaterialsCount');

        try {
            $result = $query->getScalarResult();
            if (isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findFilesByType($start = 0, $limit, $type = 'image')
    {

        $query1 = $this->getEntityManager()->createQueryBuilder();

        $subQ = $query1
            ->select('COUNT(DISTINCT lpf.id)')
            ->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->where('lpf.file = f.id');

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('f', 'u');
        $query->select($select)
            ->addSelect(sprintf('(%s) AS teacherCount', $subQ->getDql()))
            ->from('AmaMaterials\Entity\File', 'f')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=f.user')
            ->leftJoin('AmaMaterials\Entity\FileDeleted', 'fd', 'WITH', 'fd.deleter=f.user AND fd.file=f.id')
            ->where($query->expr()->andX(
                $query->expr()->eq('f.type', ':type'),
                $query->expr()->isNull('fd.file')
            ))
            ->setParameter("type", $type)
            ->groupBy('f.id')
            ->orderBY('f.date_added', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, 3600, 'AmaFilesByType');
        try {
            return $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

}