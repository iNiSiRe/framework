<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 19.05.15
 * Time: 0:27
 */

namespace Framework\Module\HttpServer\Provider;


class MultipartRequestProvider
{
    protected function parseBoundary($header)
    {
        preg_match('#boundary=(.*)$#', $header, $matches);

        return $matches[1];
    }

    protected function parseBlock($string)
    {
        if (strpos($string, 'filename') !== false) {
            $this->uploadFile($string);
            return;
        }

        // This may never be called, if an octet stream
        // has a filename it is catched by the previous
        // condition already.
//        if (strpos($string, 'application/octet-stream') !== false) {
//            $this->octetStream($string);
//            return;
//        }

        $this->parseRequestParameter($string);
    }

    protected function uploadFile($data)
    {

    }

    protected function parseRequestParameter($data)
    {

    }

    protected function parseData($boundary, $data)
    {
        $count = 0;
        $data = preg_replace("/--$boundary--\r\n/", '', $data, -1, $count);

        $isEnd = ($count == 1);

        // split content by boundary and get rid of last -- element
        $blocks = preg_split("#--$boundary\r\n#", $data);

        // loop data blocks
        $parts = [];
        foreach ($blocks as $block)
        {
            if (empty($block)) {
                continue;
            }

            if (strpos($block, 'application/octet-stream') !== FALSE) {
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            } else {

                list ($headers, $body) = explode("\r\n\r\n", $block);

                preg_match('/name=\"(.*)\"/', $headers, $matches);
                if (count($matches) > 0) {
                    $parts[$matches[1]] = $body;
                }
            }
        }

        return $parts;
    }

    public function parseRequest($header, $data)
    {
        $boundary = $this->parseBoundary($header);
        $this->parseData($boundary, $data);
    }
}