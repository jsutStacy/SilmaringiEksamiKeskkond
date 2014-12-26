<?php
namespace AmaMaterials\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

class LessonPlanRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cache = true;

    /**
     * @var int
     */
    protected $cacheTime = 86400;


    public function findLessonPlanFiles($user, $categories = '', $categoryId='', $args = array())
    {
        $catArray = array();
        if (!is_array($categories) ) $catArray[] = $categories;
        else $catArray = $categories;

        $start = $args['start'];
        $limit = $args['limit'];



        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('f', 'u', 'c', 'lpf', 'lpfu');
        $query->select($select)
            ->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->join('AmaMaterials\Entity\File', 'f', 'WITH', 'f.id=lpf.file')
            ->join('AmaUsers\Entity\User', 'lpfu', 'WITH', 'lpfu.id=lpf.user')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=f.user')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=lpf.category')
            ->where('lpf.user=:user')
            ->setParameter("user", $user);
        if ($categoryId) {
            $ors = array();
            foreach($catArray as $category) {
                $ors[] = $query->expr()->eq('lpf.category', $category);
            }
            if ($ors[0])
                $query->andWhere(join(' OR ', $ors));
        }

        $query->groupBy('lpf.id')
            ->orderBY('lpf.priority', 'DESC')
            ->addOrderBy('lpf.dateAdded', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $getQuery = $query->getQuery();

        if($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanFiles' .$categoryId . $user->getId() );
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanFiles' . $user->getId() );

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findLessonPlanFilesCount($user, $categories = '', $categoryId='', $args = array())
    {
        $catArray = array();
        if (!is_array($categories) ) $catArray[] = $categories;
        else $catArray = $categories;

        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(lpf.id)')
            ->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->where('lpf.user=:user')
            ->setParameter("user", $user);
        if ($categoryId) {
            $ors = array();
            foreach($catArray as $category) {
                $ors[] = $query->expr()->eq('lpf.category', $category);
            }
            if ($ors[0])
                $query->andWhere(join(' OR ', $ors));
        }

        $query->orderBY('lpf.dateAdded', 'DESC');

        $getQuery = $query->getQuery();

        if($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanFilesCount' .$categoryId . $user->getId() );
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanFilesCount' . $user->getId() );

        try {
            return $getQuery->getSingleScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findLessonPlanCategoriesAndFiles($user, $limit = 5, $search = '')
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('category_id', 'category_id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('date_added', 'date_added');
        $rsm->addScalarResult('file_type', 'type');
        $rsm->addScalarResult('filename', 'filename');
        $rsm->addScalarResult('file_video', 'file_video');
        $rsm->addScalarResult('file_type', 'file_type');
        $rsm->addScalarResult('file_id', 'file_id');
        $rsm->addScalarResult('u_id', 'u_id');
        $rsm->addScalarResult('lpfu_id', 'lpfu_id');

        $searchSQL = '';
        if(!empty($search)) {
            $searchSQL = ' AND f.name LIKE :searchTerm';
        }

        $SQL = "
                SELECT lpf.id,
                   COUNT(lpf2.id) AS cnt,
                   lpf.category_id,
                   lpf.date_added,
                   f.name,
                   f.type,
                   f.filename,
                   f.video AS file_video,
                   f.type AS file_type,
                   f.id AS file_id,
                   u.id AS u_id,
                   lpfu.id AS lpfu_id
            FROM lesson_plan_files AS lpf
            LEFT JOIN lesson_plan_files AS lpf2 ON lpf2.id=lpf.id
            LEFT JOIN files f ON f.id=lpf.file_id
            INNER JOIN users u ON u.id=f.user_id
            INNER JOIN users lpfu ON lpfu.id=lpf.user_id
            WHERE lpf.user_id=:user
            $searchSQL
            GROUP BY lpf.category_id,
                     lpf.id
                     HAVING cnt <= :limit
            ORDER BY lpf.date_added DESC
        ";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query
            ->setParameter('user',$user)
            ->setParameter('limit', $limit);

        if(!empty($search)) {
            $query->setParameter('searchTerm', '%'. $search . '%');
        }
        else {
            $query->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanCategoriesAndFiles' . $user->getId() );
        }
        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

}