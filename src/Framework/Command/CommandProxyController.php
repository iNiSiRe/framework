<?php

namespace Framework\Command;

use Framework\Controller\Controller;
use Framework\Http\Request;

class CommandProxyController extends Controller
{
    public function runAction(Request $request)
    {
        $command = $request->query->get('command');

        if ($command) {
            $input = new I
        }
    }
}