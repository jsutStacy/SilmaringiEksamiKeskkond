<?php
namespace AmaUsers\Entity\Repository;

use AmaUsers\Entity\User;
use AmaUsers\Entity\UserLogin;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator as ZendPaginator;

class UserRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cache = true;

    /**
     * @var int
     */
    protected $cacheTime = 86400;

    public function findUsers()
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('u')
            ->from('AmaUsers\Entity\User', 'u');

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaUsers');

        try {
            return $getQuery->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    /**
     * @param array $args
     * @return array
     */
    public function findUsersPagination($args = array())
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
                $orderBy = 'u.id';
                break;
            case 1:
                $orderBy = 'u.email';
                break;
            case 2:
                $orderBy = 'u.first_name';
                break;
            case 3:
                $orderBy = 'u.lastname';
                break;
            case 4:
                $orderBy = 'r.roleId';
                break;
            case 5:
                $orderBy = 'u.state';
                break;
        }


        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('email', 'email');
        $rsm->addScalarResult('state', 'state');
        $rsm->addScalarResult('first_name', 'first_name');
        $rsm->addScalarResult('lastname', 'lastname');
        $rsm->addScalarResult('roleId', 'roleId');

        $SQL = "SELECT u.first_name, u.lastname, u.email, u.state, u.id, r.roleId FROM users u
                JOIN user_role_linker url ON url.user_id=u.id
                JOIN role r ON r.id=url.role_id";

        if ($search) {
            $SQL .= " WHERE u.email LIKE :email";
            $SQL .= " OR u.first_name LIKE :first_name";
            $SQL .= " OR u.lastname LIKE :lastname";
            $SQL .= " OR r.roleId LIKE :role";
        }

        $SQL .= "
                    ORDER BY " . $orderBy . " " . $order . "
                    LIMIT :start , :limit
                ";

        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);

        if ($search) {
            $query->setParameter('email', '%' . $search . '%')
                  ->setParameter('first_name', '%' . $search . '%')
                  ->setParameter('lastname', '%' . $search . '%')
                  ->setParameter('role', '%' . $search . '%');
        }

        $query
            ->setParameter('start', $start)
            ->setParameter('limit', $limit);

        $query->useResultCache($this->cache, $this->cacheTime, 'AmaUsersPaginate');

        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function countUsers()
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('COUNT(u.id)')
            ->from('AmaUsers\Entity\User', 'u');

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaUsersCount');

        try {
            return $getQuery->getSingleScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findUserSchoolsAsId($user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('s.id')
            ->from('AmaUsers\Entity\UserSchool', 'us')
            ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=us.school')
            ->where('us.user=:user')
            ->setParameter('user', $user);

        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaUsersCount');

        try {
            $returnArray = array();
            $results = $result = $getQuery->getScalarResult();
            foreach ( $results as $result ) {
                $returnArray[] = $result['id'];
            }
            return $returnArray;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findSchoolByUser(User $user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('s')
            ->from('AmaUsers\Entity\UserSchool', 'us')
            ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=us.school')
            ->where('us.user=:user')
            ->setParameter('user', $user);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaUsersSchools' . $user->getId());

        try {
            return $getQuery->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findUsersClasses(User $user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c', 's')
            ->from('AmaUsers\Entity\User', 'u');
        if ( $user->hasRole('k_teacher') ) {
            $query->join('AmaUsers\Entity\UserTeacher', 'ut', 'WITH', 'ut.user=u.id')
                ->join('AmaUsers\Entity\TeacherClass', 'uc', 'WITH', 'uc.teacher=ut.teacher')
                ->join('AmaSchools\Entity\SchoolClass', 'c', 'WITH', 'c.id=uc.class')
                ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=c.school' );
        }
         else if ( $user->hasRole('school') ) {
            $query->join('AmaUsers\Entity\UserSchool', 'uc', 'WITH', 'uc.user=u.id')
                ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=uc.school' )
                ->join('AmaSchools\Entity\SchoolClass', 'c', 'WITH', 'c.school=s.id')
               ;
        }
        else {
            $query->join('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.user=u.id')
                ->join('AmaUsers\Entity\StudentClass', 'sc', 'WITH', 'sc.student=us.student')
                ->join('AmaSchools\Entity\SchoolClass', 'c', 'WITH', 'c.id=sc.class')
                ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=c.school' );
        }
        $query->where('u.id=:user')
            ->setParameter('user', $user);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaUsersClasses' . $user->getId());

        try {
            return $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findUserClass(User $user, $classId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c')
            ->from('AmaUsers\Entity\User', 'u');
        if ( $user->hasRole('k_teacher') ) {
            $query->join('AmaUsers\Entity\UserTeacher', 'ut', 'WITH', 'ut.user=u.id')
                ->join('AmaUsers\Entity\TeacherClass', 'uc', 'WITH', 'uc.teacher=ut.teacher')
                ->join('AmaSchools\Entity\SchoolClass', 'c', 'WITH', 'c.id=uc.class')
                ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=c.school' );
        }
        else if ( $user->hasRole('school') ) {
            $query->join('AmaUsers\Entity\UserSchool', 'uc', 'WITH', 'uc.user=u.id')
                ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=uc.school' )
                ->join('AmaSchools\Entity\SchoolClass', 'c', 'WITH', 'c.school=s.id');
        }
        else {
            $query->join('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.user=u.id')
                ->join('AmaUsers\Entity\StudentClass', 'sc', 'WITH', 'sc.student=us.student')
                ->join('AmaSchools\Entity\SchoolClass', 'c', 'WITH', 'c.id=sc.class')
                ->join('AmaSchools\Entity\School', 's', 'WITH', 's.id=c.school' );
        }
        $query->where('u.id=:user')
            ->andWhere('c.id=:class')
            ->setParameter('user', $user)
            ->setParameter('class', $classId);

        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaUserClasses' . $user->getId());

        try {
            $result  = $getQuery->getResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getAlerts(User $user, $status = 0)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('a')
            ->from('AmaUsers\Entity\Alert', 'a')
            ->where('a.user=:user')
            ->andWhere('a.status=:status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('a.dateAdded', 'DESC');

        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaAlertCount');

        try {
            return $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getLastAlerts(User $user, $status = 1, $limit = 3)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('a')
            ->from('AmaUsers\Entity\Alert', 'a')
            ->where('a.user=:user')
            ->andWhere('a.status=:status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->setMaxResults($limit)
            ->orderBy('a.dateAdded', 'DESC');

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaAlerts' . $user->getId());

        try {
            return $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getAlertsCount(User $user, $status = 0)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('COUNT(a.id)')
            ->from('AmaUsers\Entity\Alert', 'a')
            ->where('a.user=:user')
            ->andWhere('a.status=:status')
            ->setParameter('user', $user)
            ->setParameter('status', $status);

        $getQuery = $query->getQuery();
        //$getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaAlertCount');

        try {
            return $getQuery->getSingleScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function markAlertsRead(User $user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $q = $query->update('AmaUsers\Entity\Alert', 'a')
            ->set('a.status', 1)
            ->where('a.user=:user')
            ->andWhere('a.status=0')
            ->setParameter('user', $user)
            ->getQuery();
        $q->execute();
    }

    public function findUserClassesFiles($user, $limit = 5, $search = '')
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('category_id', 'category_id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('date_added', 'date_added');
        $rsm->addScalarResult('file_type', 'file_type');
        $rsm->addScalarResult('filename', 'filename');
        $rsm->addScalarResult('fileclass_id', 'fileclass_id');
        $rsm->addScalarResult('file_video', 'file_video');
        $rsm->addScalarResult('file_id', 'file_id');
        $rsm->addScalarResult('allreadyDoneTest', 'allreadyDoneTest');
        $rsm->addScalarResult('allreadyDoneWorksheet', 'allreadyDoneWorksheet');
        $rsm->addScalarResult('fileclass_id', 'fileclass_id');

        $searchSQL = '';
        if(!empty($search)) {
            $searchSQL = ' AND fi.name LIKE :searchTerm';
        }

        $SQL = "
                SELECT fc.id,
                       fcc.id AS fileclass_id,
                   COUNT(fc2.id) AS cnt,
                   fc.category_id,
                   fc.date_added,
                   fi.name,
                   fi.type AS file_type,
                   fi.filename,
                   fcc.id AS fileclass_id,
                   fi.video AS file_video,
                   fi.id AS file_id,
                   (SELECT COUNT(utaq.id) FROM test_user_answered_question utaq WHERE utaq.test_id=fi.test_id AND utaq.lesson_plan_id=fc.id AND utaq.file_class_id=fcc.id AND utaq.user_id=us.user_id LIMIT 1) AS allreadyDoneTest,
                   (SELECT COUNT(utaq2.id) FROM worksheet_user_answered_question utaq2 WHERE utaq2.worksheet_id=fi.worksheet_id AND utaq2.lesson_plan_id=fc.id AND utaq2.file_class_id=fcc.id AND utaq2.user_id=us.user_id LIMIT 1) AS allreadyDoneWorksheet
            FROM lesson_plan_files AS fc
            LEFT JOIN lesson_plan_files AS fc2 ON fc2.id=fc.id
            LEFT JOIN files AS fi ON fi.id=fc.file_id
            JOIN (
                SELECT * FROM file_classes ORDER BY date_added DESC
            ) AS fcc ON fcc.file_id=fc.id
            JOIN student_classes sc ON sc.class_id=fcc.class_id
            JOIN user_students us ON us.student_id=sc.student_id
            WHERE us.user_id=:user
            $searchSQL
            GROUP BY fc.category_id,
                     fc.id
                     HAVING cnt <= :limit
            ORDER BY fcc.date_added DESC, fcc.id DESC
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query
            ->setParameter('user',$user)
            ->setParameter('limit', $limit);

        if(!empty($search)) {
            $query->setParameter('searchTerm', '%'. $search . '%');
        }
        else {
            $query->useResultCache($this->cache, $this->cacheTime,  'userClassesFiles' . $user->getId() );
        }

        try {
            $result = $query->getScalarResult();
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findUserClassesFilesWithCategory($user, $categories = '', $categoryId='', $args = array())
    {
        $catArray = array();
        if (!is_array($categories) ) $catArray[] = $categories;
        else $catArray = $categories;

        $start = $args['start'];
        $limit = $args['limit'];

        $query1 = $this->getEntityManager()->createQueryBuilder();
        $subQ1 = $query1
            ->select('COUNT(utaq.id)')
            ->from('AmaTests\Entity\UserTestAnsweredQuestion', 'utaq')
            ->where('utaq.test = f.test')
            ->andWhere('utaq.user=us.user')
            ->andWhere('utaq.fileClass=fc.id')
            ->andWhere('utaq.lessonPlan=lpf.id');

        $query2 = $this->getEntityManager()->createQueryBuilder();
        $subQ2 = $query2
            ->select('COUNT(utaq2.id)')
            ->from('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion', 'utaq2')
            ->where('utaq2.worksheet = f.worksheet')
            ->andWhere('utaq2.user=us.user')
            ->andWhere('utaq2.fileClass=fc.id')
            ->andWhere('utaq2.lessonPlan=lpf.id');

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array(
            'lpf.id AS id',
            'fc.id AS fileclass_id',
            'f.name AS name',
            'f.type AS file_type',
            'f.filename AS filename',
            'c.id AS category_id',
            'f.video AS file_video',
            'f.id AS file_id');
        $query->select($select)
            ->addSelect(sprintf('(%s) AS allreadyDoneTest', $subQ1->getDql()))
            ->addSelect(sprintf('(%s) AS allreadyDoneWorksheet', $subQ2->getDql()))
            ->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->leftJoin('AmaMaterials\Entity\File', 'f', 'WITH', 'f.id=lpf.file')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=lpf.category')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.file=lpf.id')
            ->join('AmaUsers\Entity\StudentClass', 'uc', 'WITH', 'uc.class=fc.class')
            ->join('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.student=uc.student')
            ->where('us.user=:user')
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
            ->orderBY('fc.dateAdded', 'DESC');
        if($limit) {
            $query->setFirstResult($start)
            ->setMaxResults($limit);
        }

        $getQuery = $query->getQuery();

        //print_r($getQuery->getSQL());

        if($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'userClassesFilesWithCategory' .$categoryId . $user->getId() );
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'userClassesFilesWithCategory' . $user->getId() );

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findUserClassesFilesCount($user, $categories = '', $categoryId='', $args = array())
    {
        $catArray = array();
        if (!is_array($categories) ) $catArray[] = $categories;
        else $catArray = $categories;

        $start = $args['start'];
        $limit = $args['limit'];

        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(f.id)')
            ->from('AmaMaterials\Entity\File', 'f')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.file=f.id')
            ->join('AmaUsers\Entity\StudentClass', 'uc', 'WITH', 'uc.class=fc.class')
            ->join('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.student=uc.student')
            ->where('us.user=:user')
            ->setParameter("user", $user);

        if ($categoryId) {
            $ors = array();
            foreach($catArray as $category) {
                $ors[] = $query->expr()->eq('f.category', $category);
            }
            if ($ors[0])
                $query->andWhere(join(' OR ', $ors));
        }

        $query
            ->orderBY('fc.dateAdded', 'DESC');
        if($limit) {
            $query->setFirstResult($start)
            ->setMaxResults($limit);
        }

        $getQuery = $query->getQuery();

        /*if($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanFiles' .$categoryId . $user->getId() );
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaLessonPlanFiles' . $user->getId() );
        */
        try {
            return  $getQuery->getSingleScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function updateOnlineTime($user)
    {
        if(empty($user)) return;

        $lastLogin = $user->getUserLastLogin();
        //if user has not logged out and its new day create new login time
        if(isset($lastLogin)) {
            if($lastLogin->getDateAdded()->format('Y-m-d')!=(new \DateTime())->format('Y-m-d')) {
                $userLogin = new UserLogin();
                $userLogin->setDateAdded(new \DateTime());
                $userLogin->setDateEnded(new \DateTime());
                $userLogin->setUser($user);
                $this->getEntityManager()->persist($userLogin);
                $this->getEntityManager()->flush();

                $user->setUserLastLogin($userLogin);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
            }
            else {
                $query2 = $this->getEntityManager()->createQueryBuilder();
                $q = $query2->update('AmaUsers\Entity\UserLogin', 'ul')
                    ->set('ul.date_ended', ':date_ended')
                    ->where('ul.user=:user')
                    ->andWhere('ul.id=:lastLogin')
                    ->setParameter("user", $user)
                    ->setParameter('lastLogin', $user->getUserLastLogin())
                    ->setParameter('date_ended', new \DateTime())
                    ->getQuery();
                $q->execute();
            }
        }
        else {
            $userLogin = new UserLogin();
            $userLogin->setDateAdded(new \DateTime());
            $userLogin->setDateEnded(new \DateTime());
            $userLogin->setUser($user);
            $this->getEntityManager()->persist($userLogin);
            $this->getEntityManager()->flush();

            $user->setUserLastLogin($userLogin);
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();

            $query2 = $this->getEntityManager()->createQueryBuilder();
            $q = $query2->update('AmaUsers\Entity\UserLogin', 'ul')
                ->set('ul.date_ended', ':date_ended')
                ->where('ul.user=:user')
                ->andWhere('ul.id=:lastLogin')
                ->setParameter("user", $user)
                ->setParameter('lastLogin', $user->getUserLastLogin())
                ->setParameter('date_ended', new \DateTime())
                ->getQuery();
            $q->execute();
        }
    }

    public function getDayilyHoursOnline($dateNow, $daysInMonth, $user)
    {
        $rsm = new ResultSetMapping();
        $hoursSQLArray = array();
        for($day=1;$day<=$daysInMonth;++$day) {
        $rsm->addScalarResult('hour' . $day, 'hour' . $day);
           $hoursSQLArray[] = "SUM(IF(DATE(date_added)='2014-10-".$day."' AND DATE(date_ended)='2014-10-".$day."',HOUR(TIMEDIFF(date_added, date_ended)),0)) AS hour".$day."";
        }

        $SQL = "SELECT ";
            $SQL .= implode(",", $hoursSQLArray);
        $SQL .= " FROM user_logins
                WHERE
                user_id = :user
                AND MONTH(date_added) = MONTH(:date_now)
                AND YEAR(date_added) = YEAR(:date_now)
                LIMIT 1
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query
            ->setParameter('user',$user)
            ->setParameter('date_now', $dateNow);

        $query->useResultCache($this->cache, 3600,  'userDailyStatistics' . $user->getId() );
        try {
            $result = $query->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getMonthlyHoursOnline($dateNow, $months, $user)
    {
        $rsm = new ResultSetMapping();
        $hoursSQLArray = array();
        $year = date('Y', strtotime($dateNow));
        foreach($months as $month) {
            $rsm->addScalarResult('hour' . $month['nr'], 'hour' . $month['nr']);
            $hoursSQLArray[] = "SUM(IF((MONTH(date_added)=MONTH('".$year."-".$month['nr']."-01') AND YEAR(date_added)='".$year."' ) AND (MONTH(date_ended)=MONTH('".$year."-".$month['nr']."-01') AND YEAR(date_ended)='".$year."' ),HOUR(TIMEDIFF(date_added, date_ended)),0)) AS hour".$month['nr']."";
        }

        $SQL = "SELECT ";
        $SQL .= implode(",", $hoursSQLArray);
        $SQL .= " FROM user_logins
                WHERE user_id = :user
                AND YEAR(date_added) = YEAR(CURDATE())
                LIMIT 1
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query->setParameter('user',$user);

        $query->useResultCache($this->cache, 3600,  'userMonthlyStatistics' . $user->getId() );
        try {
            $result = $query->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getWeeklyHoursOnline($dateFrom, $dateTo, $weeks, $user)
    {
        $rsm = new ResultSetMapping();
        $hoursSQLArray = array();
        foreach($weeks as $week) {
            $rsm->addScalarResult('hour' . $week, 'hour' . $week);
            $hoursSQLArray[] = "SUM(IF(WEEK(date_added)='".$week."' AND WEEK(date_ended)='".$week."',HOUR(TIMEDIFF(date_added, date_ended)),0)) AS hour".$week."";
        }

        $SQL = "SELECT ";
        $SQL .= implode(",", $hoursSQLArray);
        $SQL .= " FROM user_logins
                WHERE user_id = :user
                AND WEEK(date_added) BETWEEN WEEK(:dateFrom) AND WEEK(:dateTo)
                LIMIT 1
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query->setParameter('user',$user)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo);

        $query->useResultCache($this->cache, 3600,  'userWeeklyStatistics' . $user->getId() );
        try {
            $result = $query->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getDayilyHoursOnlineByClass($dateNow, $daysInMonth, $user, $classId)
    {
        $rsm = new ResultSetMapping();
        $hoursSQLArray = array();
        for($day=1;$day<=$daysInMonth;++$day) {
            $rsm->addScalarResult('hour' . $day, 'hour' . $day);
            $hoursSQLArray[] = "(SUM(IF(DATE(ul.date_added)='2014-10-".$day."' AND DATE(ul.date_ended)='2014-10-".$day."',HOUR(TIMEDIFF(ul.date_added, ul.date_ended)),0))/@student_count) AS hour".$day."";
        }

        $SQL = "SELECT
                @student_count := (SELECT COUNT(id) FROM student_classes WHERE class_id=:class_id),";
        $SQL .= implode(",", $hoursSQLArray);
        $SQL .= " FROM student_classes sc
                INNER JOIN user_students us ON us.student_id=sc.student_id
                INNER JOIN user_logins ul ON ul.user_id=us.user_id
                INNER JOIN user_teachers ut ON ut.user_id=:user
                INNER JOIN teacher_classes tc ON tc.teacher_id=ut.teacher_id AND tc.class_id=:class_id
                WHERE
                sc.class_id = :class_id
                AND MONTH(ul.date_added) = MONTH(:date_now)
                AND YEAR(ul.date_added) = YEAR(:date_now)
                LIMIT 1
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query
            ->setParameter('user',$user)
            ->setParameter('date_now', $dateNow)
            ->setParameter('class_id', $classId);

        $query->useResultCache($this->cache, 3600,  'userDailyStatistics' . $classId .  $user->getId() );
        try {
            $result = $query->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getMonthlyHoursOnlineByClass($dateNow, $months, $user, $classId)
    {
        $rsm = new ResultSetMapping();
        $hoursSQLArray = array();
        $year = date('Y', strtotime($dateNow));
        foreach($months as $month) {
            $rsm->addScalarResult('hour' . $month['nr'], 'hour' . $month['nr']);
            $hoursSQLArray[] = "(SUM(IF((MONTH(ul.date_added)=MONTH('".$year."-".$month['nr']."-01') AND YEAR(ul.date_added)='".$year."' ) AND (MONTH(ul.date_ended)=MONTH('".$year."-".$month['nr']."-01') AND YEAR(ul.date_ended)='".$year."' ),HOUR(TIMEDIFF(ul.date_added, ul.date_ended)),0))/@student_count) AS hour".$month['nr']."";
        }

        $SQL = "SELECT
                @student_count := (SELECT COUNT(id) FROM student_classes WHERE class_id=:class_id),";
        $SQL .= implode(",", $hoursSQLArray);
        $SQL .= " FROM student_classes sc
                INNER JOIN user_students us ON us.student_id=sc.student_id
                INNER JOIN user_logins ul ON ul.user_id=us.user_id
                INNER JOIN user_teachers ut ON ut.user_id=:user
                INNER JOIN teacher_classes tc ON tc.teacher_id=ut.teacher_id AND tc.class_id=:class_id
                WHERE sc.class_id = :class_id
                AND YEAR(ul.date_added) = YEAR(CURDATE())
                LIMIT 1
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query->setParameter('user',$user)
            ->setParameter('class_id', $classId);

        $query->useResultCache($this->cache, 3600,  'userMonthlyStatistics' . $classId . $user->getId() );
        try {
            $result = $query->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getWeeklyHoursOnlineByClass($dateFrom, $dateTo, $weeks, $user, $classId)
    {
        $rsm = new ResultSetMapping();
        $hoursSQLArray = array();
        foreach($weeks as $week) {
            $rsm->addScalarResult('hour' . $week, 'hour' . $week);
            $hoursSQLArray[] = "(SUM(IF(WEEK(ul.date_added)='".$week."' AND WEEK(ul.date_ended)='".$week."',HOUR(TIMEDIFF(ul.date_added, ul.date_ended)),0))/@student_count) AS hour".$week."";
        }

        $SQL = "SELECT
                @student_count := (SELECT COUNT(id) FROM student_classes WHERE class_id=:class_id),";
        $SQL .= implode(",", $hoursSQLArray);
        $SQL .= " FROM student_classes sc
                INNER JOIN user_students us ON us.student_id=sc.student_id
                INNER JOIN user_logins ul ON ul.user_id=us.user_id
                INNER JOIN user_teachers ut ON ut.user_id=:user
                INNER JOIN teacher_classes tc ON tc.teacher_id=ut.teacher_id AND tc.class_id=:class_id
                WHERE sc.class_id = :class_id
                AND WEEK(ul.date_added) BETWEEN WEEK(:dateFrom) AND WEEK(:dateTo)
                LIMIT 1
        ";
        $query = $this->getEntityManager()->createNativeQuery($SQL, $rsm);
        $query->setParameter('user',$user)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->setParameter('class_id', $classId);

        $query->useResultCache($this->cache, 3600,  'userWeeklyStatistics' .  $classId .  $user->getId() );
        try {
            $result = $query->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function getUserCategories(User $user)
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select('c.id')
            ->from('AmaUsers\Entity\UserCategory', 'uc')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=uc.category')
            ->where('uc.user=:user')
            ->setParameter('user', $user);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime, 'AmaUserCategories' . $user->getId());

        try {
            $results = $getQuery->getScalarResult();
            $resultsArray = array();
            foreach($results as $result) {
                $resultsArray[$result['id']] = $result['id'];
            }
            return $resultsArray;
        } catch (NoResultException $e) {
            return array();
        }
    }
}