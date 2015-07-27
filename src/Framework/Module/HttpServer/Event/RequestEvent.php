<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 27.07.15
 * Time: 18:13
 */

namespace Framework\Module\HttpServer\Event;


use Framework\Http\Request;

class RequestEvent
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}