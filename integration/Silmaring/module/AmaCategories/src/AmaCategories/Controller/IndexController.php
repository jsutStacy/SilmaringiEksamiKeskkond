<?php
namespace AmaCategories\Controller;

use Application\Classes\DebugLog;
use AmaCategories\Entity\Category;
use AmaCategories\Form\AddFilter;
use AmaCategories\Form\AddForm;
use AmaCategories\Form\EditFilter;
use AmaCategories\Form\EditForm;
use AmaCategories\Form\ImportFilter;
use AmaCategories\Form\ImportForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use PHPExcel_IOFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;


class IndexController extends AbstractActionController
{

    /**
     * Main config
     * @var $config
     */
    protected $config;

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    /**
     * @var $translator
     */
    protected $translator;

    /**
     * @var $debug
     */
    protected $debug = false;

    public function indexAction()
    {
        return new ViewModel();
    }

    public function addAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $form = new AddForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'parent' => $this->params('id')
            ))
            ->setTemplate("ama-categories/index/add");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Add category")
        ));
    }

    public function addAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $request = $this->getRequest();
        $message = '';
        $success = true;

        if ($request->isPost()) {
            $form = new AddForm();
            $category = new Category();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaSchools\Entity\Category'))->setObject($category);
            $form->bind($category);

            $post = $request->getPost();
            $form->setInputFilter(new AddFilter($this->getServiceLocator()));
            $form->setData($post);

            if ($form->isValid()) {

                $parentRight = 0;
                //if we have parent cat
                if ($this->params('id') > 0) {
                    $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('id'));
                    if ($category) {
                        $form->getData()->setParent($category);
                        $form->getData()->setDepth($category->getDepth() + 1);
                    }

                    //category left right system
                    $parentRight = $category;
                    if ($parentRight) {
                        $children = $parentRight->getChildren();
                        if (isset($children[0])) {
                            $origChildren = $children;
                            $children = array_reverse($children);
                            $child = $children[0];
                            if (empty($child)) {
                                $child = $origChildren[0];
                            }
                            $parentRight = $child->getRight();
                        } else {
                            $parentRight = $parentRight->getLeft();
                        }
                    }
                }

                if ($parentRight == 0) {
                    $parentRight = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findBy(array('parent' => null), array('right' => 'DESC'));
                    if (isset($parentRight[0]))
                        $parentRight = $parentRight[0]->getRight();
                }

                if (is_array($parentRight)) $parentRight = 0;

                $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->updateLeft($parentRight);
                $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->updateRight($parentRight);

                $form->getData()->setLeft($parentRight + 1);
                $form->getData()->setRight($parentRight + 2);
                $form->getData()->setLanguage($translator->getLocale());
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();
                $this->clearCache();

                $message = $translator->translate('Successfully added!');
            } else {
                $success = false;
                $message = $this->formatMessage()->doFormat($form->getMessages());
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message
        ));
    }

    public function editAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id') == 0) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('id'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $form = new EditForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'category' => $category
            ))
            ->setTemplate("ama-categories/index/edit");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Edit category")
        ));
    }

    public function editAjaxAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id') == 0) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('id'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $message = '';
        $success = true;

        if ($request->isPost()) {
            $form = new EditForm();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaSchools\Entity\Category'))->setObject($category);
            $form->bind($category);

            $post = $request->getPost();
            $form->setInputFilter(new EditFilter($this->getServiceLocator()));
            $form->setData($post);

            if ($form->isValid()) {
                if ($this->params('id') > 0 && $this->params('id') != $category->getId()) {
                    $form->getData()->setParent($this->params('id'));
                }
                $form->getData()->setLanguage($translator->getLocale());
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();
                $this->clearCache();

                $message = $translator->translate('Successfully updated!');
            } else {
                $success = false;
                $message = $this->formatMessage()->doFormat($form->getMessages());
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message
        ));
    }

    public function deleteAction()
    {
        if ($this->params('id') == 0) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('id'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $left = $category->getLeft();
        $right = $category->getRight();
        $width = $right - $left + 1;

        $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->deleteByLeftAndRight($left, $right);
        $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->updateLeftWithWidth($right, $width);
        $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->updateRightWithWidth($right, $width);

        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
        $this->clearCache();

        return $this->redirect()->toRoute('home');
    }

    public function importAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $form = new ImportForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
            ))
            ->setTemplate("ama-categories/index/import");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Import categories from xls")
        ));
    }

    public function importAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getTranslator();
        $request = $this->getRequest();
        $message = '';
        $success = true;
        $this->getConfig();

        if ($request->isPost()) {
            $form = new ImportForm();
            $category = new Category();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaMaterials\Entity\File'))->setObject($category);
            $form->bind($category);

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setInputFilter(new ImportFilter($this->getServiceLocator()));
            $form->setData($post);

            if ($form->isValid()) {

                set_time_limit(0);
                ignore_user_abort(true);
                $this->importCategoriesFromFile($post['file']['tmp_name']);

                $this->clearCache();
                $message = $translator->translate('Successfully imported!');
            } else {
                $success = false;
                $message = $this->formatMessage()->doFormat($form->getMessages());
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message
        ));
    }


    public function viewAction()
    {
        $this->layout()->setVariable('cat', $this->params('id'));
        return new ViewModel();
    }

    public function importCategoriesFromFile($file)
    {
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($file);

        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        //$this->importDepth($objWorksheet);
        $this->importDepth($objWorksheet, 1);
    }

    private function importDepth($objWorksheet, $depth = 0)
    {
        if ($this->debug) {
            //DebugLog::info('Depth ' . $depth . ' Started');
        }
        foreach ($objWorksheet->getRowIterator() as $r => $row) {
            if ($r == 1) continue;
            //if ($r > 4) break;
           //if($depth==1 && $r>20) break;

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            if ($this->debug) {
                //DebugLog::info('Row ' . $r . ' Started');
            }

            $parent = null;
            $substract = false;
            $lastCell = 4;
            foreach ($cellIterator as $c => $cell) {
                if($c > 4) break;
                if($c  > $depth && $depth == 0) break;

                if ($this->debug) {
                    //DebugLog::info('Cell ' . $c . ' Started');
                }

                //value cannot be empty
                $cellValue = $cell->getValue();
                if (empty($cellValue)) continue;

                //ignore lines with [P]
                if ($cell->getValue() == '[P]') {
                    $substract = true;
                    $lastCell = $lastCell - 1;
                    continue;
                }

                //when we ignored a live we substract 1 from $c
                if ($substract) {
                    $c = $c - 1;
                }

                if ($c == $lastCell) {
                    //include categories seprated by dot
                    $categories = explode(".", $cell->getValue());
                    $order =1;
                    foreach ($categories as $category) {
                        $this->importCategoryToDatabase(trim($category), $parent, $c, $order);
                        $order++;
                    }

                } else {
                    $parent = $this->importCategoryToDatabase(trim($cell->getValue()), $parent, $c, $r);
                }
            }
        }
    }

    public function importCategoryToDatabase($categoryName, $categoryParent = null, $depth = 0, $order = 0)
    {
        //if ($depth > 1) return $depth;

        /*if ($depth == 0) {
            $categoryParent = null;
        }*/

        if ($this->debug) {
            DebugLog::info('CurrentCategory ' . $categoryName);

            if (isset($categoryParent))
                DebugLog::info('CategoryParent ' . $categoryParent->getName() . ' ' . $categoryParent->getId() . ' Left  ' . $categoryParent->getLeft() . ' Right ' . $categoryParent->getRight());
            else
                DebugLog::info('CategoryParent null  depth ' . $depth);
        }

        $translator = $this->getTranslator();
        $lang = $translator->getLocale();
        if (empty($categoryName)) return $categoryParent;

        if($depth == 0 )
            $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findOneBy(array('name' => $categoryName, 'depth' => 0, 'language' => $lang));
        else
            $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findOneBy(array('name' => $categoryName, 'parent' => $categoryParent, 'language' => $lang));

        if($category) {
            if ($this->debug) {
                DebugLog::info('Has category ' . $category->getName(). ' ' . $category->getId());
            }
        }

        if (!$category) {
            $category = new Category();
            $category->setDepth($depth);
            $category->setOrder($order);
            $category->setName($categoryName);
            $category->setLanguage($lang);
            if ($categoryParent) {
                //category left right system
                $parentRight = $categoryParent;
                if ($parentRight) {
                    $children = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findBy(array('parent' => $parentRight));

                    if ($this->debug) {
                        DebugLog::info('children ' . is_object($children) .' OR ' . (is_array($children) && isset($children[0])));
                    }

                    //does category has children than use right side
                    if (is_array($children) && isset($children[0])) {
                        if ($this->debug) {
                            DebugLog::info('Has children ' . count($children));
                        }

                        $origChildren = $children;
                        $children = array_reverse($children);
                        $child = $children[0];

                        if (empty($child))
                            $child = $origChildren[0];

                        $parentRight = $child->getRight();

                        if ($this->debug) {
                            DebugLog::info('Child ' . $child->getName() . ' ' . $child->getId() . ' Left ' . $child->getLeft() .' Right' .$child->getRight() );
                            DebugLog::info('Right ' . $parentRight);
                        }

                    } else {
                        //use left side
                        $parentRight = $parentRight->getLeft();
                        if ($this->debug) {
                            DebugLog::info('No child');
                            DebugLog::info('Right ' . $parentRight);
                        }
                    }
                }
            }

            if ($this->debug) {
                DebugLog::info('ParentRight before update left and right ' . $parentRight);
            }

            if (!$categoryParent) {
                $parentRight = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findBy(array('depth' => 0), array('right' => 'DESC'));
                if (isset($parentRight[0])) {
                    $parentRight = $parentRight[0]->getRight();
                    if ($this->debug) {
                        DebugLog::info('Depth 0 parent ' . $parentRight);
                    }
                }
            }

            if (is_array($parentRight)) $parentRight = 0;

            $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->updateLeft($parentRight);
            $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->updateRight($parentRight);

            if ($this->debug) {
                DebugLog::info('ParentRight after update left and right ' . $parentRight);
                if($categoryParent) {
                    $categoryParent = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($categoryParent->getId());
                    DebugLog::info('CategoryParent AFTER ' . $categoryParent->getName() . ' ' . $categoryParent->getId() . ' Left  ' . $categoryParent->getLeft() . ' Right ' . $categoryParent->getRight());
                }
            }

            $setLeft = $parentRight + 1;
            $setRight = $parentRight + 2;

            if ($this->debug) {
                DebugLog::info('Final left ' . $setLeft .' right ' . $setRight);
            }

            if($categoryParent) {
                $categoryParent = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($categoryParent->getId());
                $category->setParent($categoryParent);
            }
            $category->setLeft($setLeft);
            $category->setRight($setRight);
            $this->getEntityManager()->persist($category);
            $this->getEntityManager()->flush();
        } else {
            //$category->setDepth($depth);
            /*if ($categoryParent) {
                $category->setParent($categoryParent);
            }*/
            //$this->getEntityManager()->persist($category);
            //$this->getEntityManager()->flush();
        }
        return $category;
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    private function clearCache()
    {
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCategories');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCategoriesAll');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCategoriesAllS');
        $cache = $this->getServiceLocator()->get('zcache');
        $cache->clearByPrefix('category');
    }

    public function getConfig()
    {
        if (!$this->config) {
            return $this->config = $this->getServiceLocator()->get('config');
        }
        return $this->config;
    }

    public function getTranslator()
    {
        if (!$this->translator) {
            return $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;

    }
}