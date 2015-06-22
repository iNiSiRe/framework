<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/21/2015
 * Time: 2:06 AM
 */

namespace Framework\Http;

use Evenement\EventEmitter;
use React\Http\Foundation\HeaderDictionary;
use React\Http\Utils\Dictionary;

class Request extends EventEmitter
{
    const HEADER_CLIENT_IP = 1;
    const GET = 'GET';
    const POST = 'POST';

    private $trustedHeaders = [
        self::HEADER_CLIENT_IP => ['X-Real-IP']
    ];

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    public $query;

    /**
     * @var array
     */
    public $headers;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $clientIp;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $body;

    public $files;

    private $locale;

    /**
     * @param string           $method
     * @param string           $url
     * @param array            $query
     * @param HeaderDictionary $headers
     * @param string           $version
     */
    public function __construct($method, $url, $query = [], HeaderDictionary $headers, $version = '1.1')
    {
        $this->method = strtolower($method);
        $this->url = $url;
        $this->query = new Dictionary($query);
        $this->headers = $headers;
        $this->version = $version;
        $this->processHost();

        $this->files = new Dictionary();
        $this->atributes = new Dictionary();

        $this->cookies = $this->getCookiesFromHeader($this->headers);
    }

    /**
     * @param Dictionary $headers
     *
     * @return Dictionary
     */
    private function getCookiesFromHeader(Dictionary $headers)
    {
        $cookies = new Dictionary();
        $header = $headers->get('Cookie', '');
        $rawCookies = !empty($header) ? explode(';', $header) : [];
        foreach ($rawCookies as $cookie) {
            list ($key, $value) = explode('=', $cookie);
            $cookies->set(trim($key), $value);
        }

        return $cookies;
    }

    /**
     * Find client IP
     *
     * @param $clientIp
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;
    }

    public function findClientIp()
    {
        foreach ($this->trustedHeaders[self::HEADER_CLIENT_IP] as $header) {
            if ($clientIp = $this->headers->get($header)) {
                $this->clientIp = $clientIp;
                break;
            }
        }
    }

    private function processHost()
    {
        $this->host = $this->headers->get('Host');
    }

    /**
     * @param $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->method == strtolower($method);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return Dictionary
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->headers->get('Content-Type', '');
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    public function handleClose()
    {
        $this->emit('end');
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}