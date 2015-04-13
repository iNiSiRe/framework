<?php

namespace Framework\Module\Console;

use Framework\Controller\Controller;
use Framework\Http\Request;
use Framework\Http\Response;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleProxyController extends Controller
{
    public function runAction(Request $request)
    {
        $inputString = $request->query->get('input', '');
        $inputArray = array_merge([__FILE__], explode(' ', $inputString));
        $input = new ArgvInput($inputArray);
        $output = new BufferedOutput();
        $this->container->get('console')->run($input, $output);

        $content = $output->fetch();

        return new Response($content);
    }
}