<?php
namespace Application\Plugin;


use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class FormatMessage extends AbstractPlugin
{
    protected $return;

    public function doFormat($messages = '')
    {
           $this->formatMessage($messages);
           return implode("<br>",$this->return);
    }

    private function formatMessage($messages)
    {
        if(is_array($messages)) {
            foreach ($messages as $message) {
                $this->formatMessage($message);
            }
        }
        else {
            if(!in_array($messages, $this->return))
                $this->return[] = $messages;
        }
    }
}
