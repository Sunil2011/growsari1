<?php

namespace Base\Filter;

use Zend\Filter\FilterInterface;

class HtmlPurifier implements FilterInterface
{

    private $purifier;

    function __construct()
    {
    }

    public function filter($value)
    {
        return $this->getPurifier()->purify($value);
    }

    protected function getPurifier()
    {
        if (!$this->purifier) {
            $config = \HTMLPurifier_Config::createDefault();
            $options = array(
                array(
                    'HTML.Allowed',
                    'ul,li,b,i,strong,p,a[href]'
                ),
                array(
                    'Output.TidyFormat',
                    true
                ),
                array(
                    'HTML.Doctype',
                    'XHTML 1.0 Strict'
                ),
                array(
                    'Cache.DefinitionImpl',
                    null
                )
            );
            foreach ($options as $option) {
                $config->set($option[0], $option[1]);
            }

            $this->purifier = new \HTMLPurifier($config);
        }

        return $this->purifier;
    }

}
