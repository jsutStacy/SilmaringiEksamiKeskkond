<?php
namespace AmaSchools\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;


class SchoolRepository extends EntityRepository
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
    public function findSchools()
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('s')
            ->from('AmaSchools\Entity\School', 's');

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchools');

        try {
            $result = $getQuery->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param array $args
     * @return array
     */
    public function findSchoolsPagination($args = array())
    {

        $orderBy = $args['orderBy'];
        $order = strtoupper($args['order']);
        $search = $args['search'];
        $start = $args['start'];
        $limit = $args['limit'];

        if ($order == 'DESC') $order = 'DESC';
        else  $order = "ASC";

        switch ($orderBy) {
            case 0:
                $orderBy = 's.id';
                break;
            case 1:
                $orderBy = 's.name';
                break;
            case 2:
                $orderBy = 's.status';
                break;
        }


        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('status', 'status');

        $SQL = "SELECT s.name,s.status, s.id FROM schools s";

        if ($search) {
            $SQL .= " WHERE s.name LIKE :name";
        }

        $SQL .= "
                    ORDER BY " . $orderBy . " " . $order . "
                    LIMIT :start , :limit
                ";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);

        if ($search) {
            $query->setParameter('name', '%' . $search . '%');
        }

        $query
            ->setParameter('start', $start)
            ->setParameter('limit', $limit);

        $query->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolsPaginate');

        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @return array
     */
    public function countSchools()
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('COUNT(s.id)')
            ->from('AmaSchools\Entity\School', 's');

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolsCount');

        try {
            $result = $getQuery->getSingleScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $user
     * @param array $args
     * @return array
     */
    public function findSchoolsByUserPagination($user, $args = array())
    {

        $orderBy = $args['orderBy'];
        $order = strtoupper($args['order']);
        $search = $args['search'];
        $start = $args['start'];
        $limit = $args['limit'];

        if ($order == 'DESC') $order = 'DESC';
        else  $order = "ASC";

        switch ($orderBy) {
            case 0:
                $orderBy = 's.id';
                break;
            case 1:
                $orderBy = 's.name';
                break;
            case 2:
                $orderBy = 's.status';
                break;
        }


        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('status', 'status');

        $SQL = "SELECT s.name,s.status, s.id FROM schools s ";
        $SQL .= "JOIN user_schools us ON us.user_id=:user AND us.school_id=s.id";

        if ($search) {
            $SQL .= " WHERE s.name LIKE :name";
        }

        $SQL .= "
                    ORDER BY " . $orderBy . " " . $order . "
                    LIMIT :start , :limit
                ";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);

        if ($search) {
            $query->setParameter('name', '%' . $search . '%');
        }

        $query
            ->setParameter('user',$user)
            ->setParameter('start', $start)
            ->setParameter('limit', $limit);

        $query->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolsPaginate' . $user->getId());

        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $user
     * @return array
     */
    public function countSchoolsByUser($user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('COUNT(s.id)')
            ->from('AmaSchools\Entity\School', 's')
            ->join('AmaUsers\Entity\UserSchool', 'us', 'WITH', 'us.user=:user AND us.school=s.id');

        $query->setParameter('user',$user);
        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolsCount'. $user->getId());

        try {
            $result = $getQuery->getSingleScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getSchoolByIdAndUser($schoolId, $user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('s')
            ->from('AmaSchools\Entity\School', 's')
            ->join('AmaUsers\Entity\UserSchool', 'us', 'WITH', 'us.user=:user AND us.school=s.id')
            ->where('us.school=:school');

        $query->setParameter('user',$user)
            ->setParameter('school',$schoolId);


        $getQuery = $query->getQuery();
        $getQuery->useResultCache(false);

        try {
            $result = $getQuery->getSingleResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }


    /**
     * @param $school
     * @param array $args
     * @return array
     */
    public function findTeachersBySchoolPagination($school, $args = array())
    {

        $orderBy = $args['orderBy'];
        $order = strtoupper($args['order']);
        $search = $args['search'];
        $start = $args['start'];
        $limit = $args['limit'];

        if ($order == 'DESC') $order = 'DESC';
        else  $order = "ASC";

        switch ($orderBy) {
            case 0:
                $orderBy = 't.first_name';
                break;
            case 1:
                $orderBy = 't.lastname';
                break;
            case 2:
                $orderBy = 't.lastname';
                break;
            case 3:
                $orderBy = 'teacher_classes';
                break;
            case 4:
                $orderBy = 'u.email';
                break;
        }


        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('first_name', 'first_name');
        $rsm->addScalarResult('lastname', 'lastname');
        $rsm->addScalarResult('email', 'email');
        $rsm->addScalarResult('teacher_classes', 'teacher_classes');

        $SQL = "SELECT t.first_name,t.lastname, t.id, u.email,
                 (
                  SELECT  GROUP_CONCAT(DISTINCT scc.class_name SEPARATOR ',') FROM teacher_classes sc
                  INNER JOIN school_classes scc ON scc.id=sc.class_id
                  WHERE sc.teacher_id=t.id
                  ) AS teacher_classes
                FROM teachers t
                LEFT JOIN user_teachers ut ON ut.teacher_id=t.id
                LEFT JOIN users u ON u.id=ut.user_id
                LEFT JOIN teacher_classes sc1 ON sc1.teacher_id=ut.teacher_id
                LEFT JOIN school_classes scc1 ON scc1.id=sc1.class_id";

        $SQL .= " WHERE t.school_id=:school";
        if ($search) {
            $SQL .= " AND ( t.first_name LIKE :first_name";
            $SQL .= " OR t.lastname LIKE :lastname OR u.email LIKE :email OR scc1.class_name LIKE :class_name)";
        }

        $SQL .= "
                    GROUP BY t.id
                    ORDER BY " . $orderBy . " " . $order . "
                    LIMIT :start , :limit
                ";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);

        if ($search) {
            $query->setParameter('first_name', '%' . $search . '%')
                  ->setParameter('lastname', '%' . $search . '%')
                  ->setParameter('email', '%' . $search . '%')
                  ->setParameter('class_name', '%' . $search . '%');
        }

        $query
            ->setParameter('school',$school)
            ->setParameter('start', $start)
            ->setParameter('limit', $limit);

        $query->useResultCache($this->cache, $this->cacheTime, 'AmaTeachersBySchoolPaginate' . $school->getId());

        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $school
     * @return array
     */
    public function countTeachersBySchool($school)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('COUNT(t.id)')
            ->from('AmaUsers\Entity\Teacher', 't')
            ->where("t.school=:school");

        $query->setParameter('school',$school);
        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCountTeachersBySchool'. $school->getId());

        try {
            $result = $getQuery->getSingleScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }


    /**
 * @param $school
 * @return array
 */
    public function findClassesBySchool($school)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('sc')
            ->from('AmaSchools\Entity\SchoolClass', 'sc')
            ->where('sc.school=:school')
            ->setParameter('school', $school);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolClasses' . $school->getId());

        try {
            $result = $getQuery->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $user
     * @return array
     */
    public function findTeacherSchoolsByUser($user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('t')
            ->from('AmaUsers\Entity\Teacher', 't')
            ->leftJoin('AmaUsers\Entity\UserTeacher', 'ut', 'WITH', 'ut.teacher=t.id')
            ->where('t.personalCodeHash=:personalCodeHash')
            ->andWhere('ut.id IS NULL')
            ->setParameter('personalCodeHash', $user->getPersonalCodeHash());

        $getQuery = $query->getQuery();
        //print_r($getQuery->getSQL());
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolClasses' . $school->getId());

        try {
            $result = $getQuery->getResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $user
     * @return array
     */
    public function findStudentschoolsByUser($user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('s')
            ->from('AmaUsers\Entity\Student', 's')
            ->leftJoin('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.student=s.id')
            ->where('s.personalCodeHash=:personalCodeHash')
            ->andWhere('us.id IS NULL')
            ->setParameter('personalCodeHash', $user->getPersonalCodeHash());

        $getQuery = $query->getQuery();
        //print_r($getQuery->getSQL());
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolClasses' . $school->getId());

        try {
            $result = $getQuery->getResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }



    public function findTeacherBySchoolAndTid($school, $tid)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('t', 'u')
            ->from('AmaUsers\Entity\Teacher', 't')
            ->leftJoin('AmaUsers\Entity\UserTeacher', 'ut', 'WITH', 'ut.teacher=t.id')
            ->leftJoin('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=ut.user')
            ->where('t.school=:school')
            ->andWhere('t.id=:tid')
            ->setParameter('school', $school)
            ->setParameter('tid', $tid);

        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolClasses' . $school->getId());

        try {
            $result = $getQuery->getResult();
            if ( !empty($result[0]) ) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }


    /**
     * @param $school
     * @param array $args
     * @return array
     */
    public function findStudentsBySchoolPagination($school, $args = array())
    {

        $orderBy = $args['orderBy'];
        $order = strtoupper($args['order']);
        $search = $args['search'];
        $start = $args['start'];
        $limit = $args['limit'];

        if ($order == 'DESC') $order = 'DESC';
        else  $order = "ASC";

        switch ($orderBy) {
            case 0:
                $orderBy = 't.first_name';
                break;
            case 1:
                $orderBy = 't.lastname';
                break;
            case 2:
                $orderBy = 't.lastname';
                break;
            case 3:
                $orderBy = 'u.email';
                break;
            case 4:
                $orderBy = 'student_classes';
                break;
        }


        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('first_name', 'first_name');
        $rsm->addScalarResult('lastname', 'lastname');
        $rsm->addScalarResult('email', 'email');
        $rsm->addScalarResult('student_classes', 'student_classes');

        $SQL = "SELECT t.first_name,t.lastname, t.id, u.email,
                 (
                  SELECT  GROUP_CONCAT(DISTINCT scc.class_name SEPARATOR ',') FROM student_classes sc
                  INNER JOIN school_classes scc ON scc.id=sc.class_id
                  WHERE sc.student_id=t.id
                  ) AS student_classes
                FROM students t
                LEFT JOIN user_students ut ON ut.student_id=t.id
                LEFT JOIN student_classes sc1 ON sc1.student_id=ut.student_id
                LEFT JOIN school_classes scc1 ON scc1.id=sc1.class_id
                LEFT JOIN users u ON u.id=ut.user_id";

        $SQL .= " WHERE t.school_id=:school";
        if ($search) {
            $SQL .= " AND ( t.first_name LIKE :first_name";
            $SQL .= " OR t.lastname LIKE :lastname OR u.email LIKE :email OR scc1.class_name LIKE :class_name)";
        }

        $SQL .= "
                    GROUP BY t.id
                    ORDER BY " . $orderBy . " " . $order . "
                    LIMIT :start , :limit
                ";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);

        if ($search) {
            $query->setParameter('first_name', '%' . $search . '%')
                    ->setParameter('lastname', '%' . $search . '%')
                    ->setParameter('email', '%' . $search . '%')
                    ->setParameter('class_name', '%' . $search . '%');
        }

        $query
            ->setParameter('school',$school)
            ->setParameter('start', $start)
            ->setParameter('limit', $limit);

        $query->useResultCache($this->cache, $this->cacheTime, 'AmaStudentsBySchoolPaginate' . $school->getId());

        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param $school
     * @return array
     */
    public function countStudentsBySchool($school)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('COUNT(t.id)')
            ->from('AmaUsers\Entity\Student', 't')
            ->where("t.school=:school");

        $query->setParameter('school',$school);
        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaCountStudentsBySchool'. $school->getId());

        try {
            $result = $getQuery->getSingleScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }


    public function findStudentBySchoolAndTid($school, $tid)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('s', 'u')
            ->from('AmaUsers\Entity\Student', 's')
            ->leftJoin('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.student=s.id')
            ->leftJoin('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=us.user')
            ->where('s.school=:school')
            ->andWhere('s.id=:tid')
            ->setParameter('school', $school)
            ->setParameter('tid', $tid);

        $getQuery = $query->getQuery();
        //print_r($getQuery->getSQL());
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaSchoolClasses' . $school->getId());

        try {
            $result = $getQuery->getResult();
            if ( !empty($result[0]) ) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

}