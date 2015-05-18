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
    public function process($contentType, $data)
    {
        preg_match('/boundary=(.*)$/', $contentType, $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $data);
        array_pop($a_blocks);

        // loop data blocks
        $a_data = [];
        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE)
            {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            }
            // parse all other fields
            else
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }

        return $a_data;
    }
}