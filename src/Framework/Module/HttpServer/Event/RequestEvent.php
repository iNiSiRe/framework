<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 27.07.15
 * Time: 18:13
 */

namespace Framework\Module\HttpServer\Event;

use Framework\Http\Request;
use Framework\Module\EventDispatcher\EventInterface;
use Framework\Module\HttpServer\HttpServerEvents;

/**
 * Class RequestEvent
 *
 * @package Framework\Module\HttpServer\Event
 */
class RequestEvent implements EventInterface
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

    /**
     * @return string
     */
    public function getName()
    {
        return HttpServerEvents::REQUEST;
    }
}