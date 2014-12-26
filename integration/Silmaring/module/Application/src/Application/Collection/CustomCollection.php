<?php
namespace Application\Collection;

use Zend\Form\Element\Collection as ZendCollection;
use Zend\Form\Exception;
use Traversable;

class CustomCollection extends ZendCollection
{
    public function populateValues($data)
    {
        //var_dump($data);
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable set of data; received "%s"',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }

        if (sizeof($data)){
            foreach ($this->byName as $name => $element) {
                if (!isset($data[$name])) {
                    $this->remove($name);
                }
            }
        }

        parent::populateValues($data);
    }
}