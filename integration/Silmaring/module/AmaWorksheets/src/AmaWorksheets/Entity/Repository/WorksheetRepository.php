<?php
namespace AmaWorksheets\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;


class WorksheetRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cache = true;

    /**
     * @var int
     */
    protected $cacheTime = 86400;


    public function findWorksheets($user, $categories = '', $categoryId='', $args = array())
    {
        $catArray = array();
        if (!is_array($categories) ) $catArray[] = $categories;
        else $catArray = $categories;

        $start = $args['start'];
        $limit = $args['limit'];

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('f', 'u', 'c', 't');
        $query->select($select)
            ->from('AmaMaterials\Entity\File', 'f')
            ->leftJoin('AmaMaterials\Entity\FileDeleted', 'fd','WITH', 'fd.deleter=f.user AND fd.file=f.id')
            ->join('AmaWorksheets\Entity\Worksheet', 't', 'WITH', 't.id=f.worksheet')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=f.user')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=f.category')
            ->where('f.user=:user')
            ->andWhere('fd.file IS NULL')
            ->andWhere('f.type=:type')
            ->setParameter("user", $user)
            ->setParameter('type', 'worksheet');
        if ($categoryId) {
            $ors = array();
            foreach($catArray as $category) {
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

        if($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheets' .$categoryId . $user->getId() );
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheets' . $user->getId() );

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findWorksheetsCount($user, $categories = '', $categoryId='', $args = array())
    {
        $catArray = array();
        if (!is_array($categories) ) $catArray[] = $categories;
        else $catArray = $categories;

        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(f.id)')
            ->from('AmaMaterials\Entity\File', 'f')
            ->leftJoin('AmaMaterials\Entity\FileDeleted', 'fd','WITH', 'fd.deleter=f.user AND fd.file=f.id')
            ->join('AmaWorksheets\Entity\Worksheet', 't', 'WITH', 't.id=f.worksheet')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=f.user')
            ->join('AmaCategories\Entity\Category', 'c', 'WITH', 'c.id=f.category')
            ->where('f.user=:user')
            ->andWhere('fd.file IS NULL')
            ->andWhere('f.type=:type')
            ->setParameter("user", $user)
            ->setParameter('type', 'worksheet');
        if ($categoryId) {
            $ors = array();
            foreach($catArray as $category) {
                $ors[] = $query->expr()->eq('f.category', $category);
            }
            if ($ors[0])
                $query->andWhere(join(' OR ', $ors));
        }

        $query->orderBY('f.date_added', 'DESC');

        $getQuery = $query->getQuery();

        if($categoryId)
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheetsCount' .$categoryId . $user->getId() );
        else
            $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheetsCount' . $user->getId() );

        try {
            return $getQuery->getSingleScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function checkIfUserCanAccessWorksheet($user, $id, $asLpf = true)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('f');
        $query->select($select)
            ->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->join('AmaMaterials\Entity\File', 'f', 'WITH', 'f.id=lpf.file')
            ->join('AmaWorksheets\Entity\Worksheet', 't', 'WITH', 't.id=f.worksheet')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=lpf.user')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.file=lpf.id')
            ->join('AmaUsers\Entity\StudentClass', 'uc', 'WITH', 'uc.class=fc.class')
            ->join('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.student=uc.student')
            ->where('us.user=:user');
            if($asLpf) {
                $query->andWhere('lpf.id=:file');
            }
            else {
                $query->andWhere('fc.id=:file');
            }
            $query->setParameter("user", $user)
            ->setParameter("file", $id);

        $getQuery = $query->getQuery();

        //$getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaSolveWorksheet' .  $id . $user->getId());

        try {
            $result = $getQuery->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findWorksheetById($id, $asLpf = true, $getPoints = false)
    {

        $query1= $this->getEntityManager()->createQueryBuilder();
        $subQ1 = $query1
            ->select('SUM(q2.points)')
            ->from('AmaWorksheets\Entity\Question', 'q2')
            ->where('q2.worksheet = f.worksheet');

        $query2 = $this->getEntityManager()->createQueryBuilder();
        $subQ2 = $query2
            ->select('SUM(utaq2.points)')
            ->from('AmaWorksheets\Entity\UserWorksheetAnswer', 'utaq2')
            ->where('utaq2.worksheet = f.worksheet')
            ->andWhere('utaq2.lessonPlan = lpf.id')
            ->andWhere('utaq2.fileClass = fc.id');

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('f', 't', 'lpf', 'u', 'fc');
        $query->select($select);
        if($getPoints) {
            $query->addSelect(sprintf('(%s) AS totalPoints', $subQ1->getDql()))
                ->addSelect(sprintf('(%s) AS totalRightPoints', $subQ2->getDql()));
        }
        $query->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->join('AmaMaterials\Entity\File', 'f', 'WITH', 'f.id=lpf.file')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.file=lpf.id')
            ->join('AmaWorksheets\Entity\Worksheet', 't', 'WITH', 't.id=f.worksheet')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=lpf.user');

            if($asLpf) {
                $query->where('lpf.id=:file');
            }
            else {
                $query->where('fc.id=:file');
            }

            $query->setParameter("file", $id);

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaSolveWorksheet' .  $id);

        try {
            $result = $getQuery->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findQuestionsByWorksheetId($id)
    {

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('q');
        $query->select($select)
            ->from('AmaWorksheets\Entity\Question', 'q')
            ->where('q.worksheet=:worksheet')
            ->setParameter("worksheet", $id)
            ->orderBy('q.order', 'ASC');

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaSolveWorksheetQuestions' .  $id);

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findAnswersByQuestionId($id, $worksheetId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('a');
        $query->select($select)
            ->from('AmaWorksheets\Entity\Answer', 'a')
            ->where('a.question=:q')
            ->andWhere('a.worksheet=:worksheet')
            ->setParameter('q', $id)
            ->setParameter('worksheet', $worksheetId);

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaSolveWorksheetAnswers' . $id . $worksheetId);

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }


    public function findRightAnswersByQuestionId($id, $worksheetId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('a.id')
            ->from('AmaWorksheets\Entity\Answer', 'a')
            ->where($query->expr()->andX(
                $query->expr()->eq('a.question', ':q'),
                $query->expr()->eq('a.worksheet', ':worksheet'),
                $query->expr()->eq('a.isRight', ':right')
            ))
            ->setParameter('q', $id)
            ->setParameter('worksheet', $worksheetId)
            ->setParameter('right', 1);

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaSolveWorksheetRightAnswers' . $id . $worksheetId);

        try {
            $resultsArray = array();
            $results = $getQuery->getScalarResult();
            foreach($results as $result) {
                $resultsArray[] = $result['id'];
            }
            return  $resultsArray;
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findImagesByQuestionId($id, $worksheetId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('qi');
        $query->select($select)
            ->from('AmaWorksheets\Entity\QuestionImage', 'qi')
            ->where('qi.question=:q')
            ->setParameter("q", $id);

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaSolveWorksheetImages' . $id .  $worksheetId);

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }


    public function findWorksheetStatistics($lessonPlanId, $fileClassId, $userId)
    {

        $query1 = $this->getEntityManager()->createQueryBuilder();
        $subQ = $query1
            ->select('COUNT(DISTINCT utaq.user)')
            ->from('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion', 'utaq')
            ->where('utaq.worksheet = f.worksheet')
            ->andWhere('utaq.lessonPlan = lpf.id')
            ->andWhere('utaq.fileClass = fc.id');

        $query2 = $this->getEntityManager()->createQueryBuilder();
        $subQ2 = $query2
            ->select('SUM(q.points)')
            ->from('AmaWorksheets\Entity\Question', 'q')
            ->where('q.worksheet = f.worksheet');


        $query3 = $this->getEntityManager()->createQueryBuilder();
        $subQ3 = $query3
            ->select('SUM(utaq2.points)')
            ->from('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion', 'utaq2')
            ->where('utaq2.worksheet = f.worksheet')
            ->andWhere('utaq2.lessonPlan = lpf.id')
            ->andWhere('utaq2.fileClass = fc.id')
            ->andWherE('utaq2.isRight = 1');


        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('t', 'lpf', 'fc');
        $query->select($select)
            ->addSelect(sprintf('(%s) AS finishedUsersCount', $subQ->getDql()))
            ->addSelect(sprintf('(%s) AS totalPoints', $subQ2->getDql()))
            ->addSelect(sprintf('(%s) AS totalRightPoints', $subQ3->getDql()))
            ->from('AmaMaterials\Entity\LessonPlanFile', 'lpf')
            ->join('AmaMaterials\Entity\File', 'f', 'WITH', 'f.id=lpf.file')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.file=lpf.id')
            ->join('AmaWorksheets\Entity\Worksheet', 't', 'WITH', 't.id=f.worksheet')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=lpf.user')
            ->where('f.id=:file')
            ->andWhere('fc.id=:fileClass')
            ->andWhere('lpf.user=:user')
            ->setParameter("file", $lessonPlanId)
            ->setParameter('fileClass', $fileClassId)
            ->setParameter('user', $userId);

        $getQuery = $query->getQuery();
        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheetStatistics' . $lessonPlanId . $fileClassId .  $userId);

        try {
            $result = $getQuery->getScalarResult();
            if(isset($result[0])) $result = $result[0];
            return $result;
        } catch (NoResultException $e) {
            return array();
        }
    }


    public function findWorksheetStatisticsQuestions($worksheet, $lessonPlanId, $fileClassId, $user)
    {

        $query1 = $this->getEntityManager()->createQueryBuilder();
        $subQ = $query1
            ->select('COUNT(utaq1.id)')
            ->from('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion', 'utaq1')
            ->where('utaq1.worksheet = f.worksheet')
            ->andWhere('utaq1.lessonPlan = lpf.id')
            ->andWhere('utaq1.fileClass = fc.id')
            ->andWhere('utaq1.question=q.id')
            ->andWherE('utaq1.isRight = 1');

        $query2 = $this->getEntityManager()->createQueryBuilder();
        $subQ2 = $query2
            ->select('COUNT(utaq2.id)')
            ->from('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion', 'utaq2')
            ->where('utaq2.worksheet = f.worksheet')
            ->andWhere('utaq2.lessonPlan = lpf.id')
            ->andWhere('utaq2.fileClass = fc.id')
            ->andWhere('utaq2.question=q.id')
            ->andWherE('utaq2.isRight != 1');

        $query3= $this->getEntityManager()->createQueryBuilder();
        $subQ3 = $query3
            ->select('SUM(q2.id)')
            ->from('AmaWorksheets\Entity\Question', 'q2')
            ->where('q2.id = q.id');

        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('q');
        $query->select($select)
            ->addSelect(sprintf('(%s) AS rightAnswersCount', $subQ->getDql()))
            ->addSelect(sprintf('(%s) AS wrongAnswersCount', $subQ2->getDql()))
            ->addSelect(sprintf('(%s) AS totalPoints', $subQ3->getDql()))
            ->from('AmaWorksheets\Entity\Question', 'q')
            ->join('AmaWorksheets\Entity\Worksheet', 't', 'WITH', 't.id=q.worksheet')
            ->join('AmaMaterials\Entity\File', 'f', 'WITH', 'f.worksheet=t.id')
            ->join('AmaMaterials\Entity\LessonPlanFile', 'lpf', 'WITH', 'lpf.file=f.id')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.file=lpf.id')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=lpf.user')
            ->where('f.id=:file')
            ->andWhere('fc.id=:fileClass')
            ->andWhere('lpf.user=:user')
            ->andWhere('q.worksheet=:worksheet')
            ->setParameter("file", $lessonPlanId)
            ->setParameter('fileClass', $fileClassId)
            ->setParameter('user', $user)
            ->setParameter('worksheet', $worksheet)
            ->orderBy('q.order', 'ASC');

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheetStatisticsQuestions' . $lessonPlanId . $fileClassId . $user->getId());

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findWorksheetStatisticsQuestionAnswers($lessonPlanId, $fileClassId, $user, $question)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('a', 's', 'ua', 'utaq', 'u');
        $query->select($select)
            ->from('AmaWorksheets\Entity\UserWorksheetAnswer', 'ua')
            ->join('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion', 'utaq', 'WITH', 'utaq.question=ua.question')
            ->leftJoin('AmaWorksheets\Entity\Answer', 'a' , 'WITH', 'a.id=ua.answer')
            ->join('AmaMaterials\Entity\LessonPlanFile', 'lpf', 'WITH', 'lpf.id=ua.lessonPlan')
            ->join('AmaMaterials\Entity\File', 'f', 'WITH', 'f.id=lpf.file')
            ->join('AmaMaterials\Entity\FileClass', 'fc', 'WITH', 'fc.id=ua.fileClass')
            ->join('AmaUsers\Entity\UserStudent', 'us', 'WITH', 'us.user=ua.user')
            ->join('AmaUsers\Entity\User', 'u', 'WITH', 'u.id=us.user')
            ->join('AmaUsers\Entity\Student', 's', 'WITH', 's.id=us.student')
            ->where('f.id=:file')
            ->andWhere('fc.id=:fileClass')
            ->andWhere('ua.question=:question')
            ->setParameter("file", $lessonPlanId)
            ->setParameter('fileClass', $fileClassId)
            ->setParameter('question', $question)
            ->groupBy('ua.id');

        $getQuery = $query->getQuery();

        $getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheetStatisticsQuestionAnswers' . $lessonPlanId . $fileClassId . $question . $user);

        try {
            return  $getQuery->getScalarResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findAnswersByQidAndModified($question, $modified)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $select = array('a');
        $query->select($select)
            ->from('AmaWorksheets\Entity\Answer', 'a')
            ->where('a.question=:question')
            ->andWhere('a.dateModified<:modified')
            ->setParameter('question', $question)
            ->setParameter('modified', $modified);

        $getQuery = $query->getQuery();

        //$getQuery->useResultCache($this->cache, $this->cacheTime,  'AmaWorksheetStatisticsQuestionAnswers' . $lessonPlanId . $fileClassId . $question . $user);

        try {
            return  $getQuery->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

}