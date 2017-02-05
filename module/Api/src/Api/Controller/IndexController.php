<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class IndexController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/index",
     *     description="get all categories",
     *     tags={"index"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getList()
    {
        //just create table class, access it like this
        // no need of creating config
        // will creat a scipt to automate this as well
        //$productTable = $this->serviceLocator->get('Api\Table\ProductTable');
        // just throw eception, error handler sends json response.
        throw new ApiException('Method not implemented', '405');
    }

}
