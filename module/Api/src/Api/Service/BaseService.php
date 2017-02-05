<?php

namespace Api\Service;

class BaseService
{

    protected $sm;

    public function __construct($serviceManager)
    {
        $this->sm = $serviceManager;
    }
    
    public function getServiceLocator()
    {
        return $this->sm;
    }

    public function success($msg, $data = array())
    {
        return array(
            'success' => true,
            'message' => $msg,
            'data' => $data
        );
    }

    public function error($msg, $error = array(), $code = 500)
    {
        $this->sm->get('response')->setStatusCode($code);
        return array(
            'error' => array_merge(
                array(
                    "type" => "Api\\Exception\\ApiException",
                    'message' => $msg,
                    "code" => $code
                ), 
                $error
            ),
        );
    }

    public function getTable($table)
    {
        return $this->sm->get('Api\Table\\' . $table . 'Table');
    }

}
