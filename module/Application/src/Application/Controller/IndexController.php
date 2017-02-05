<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $basePath = $this->getRequest()->getBasePath();
        return $this->redirect()->toUrl($basePath . '/admin/source');
    }

}
