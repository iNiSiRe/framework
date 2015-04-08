<?php

namespace Framework\Command;

use Framework\Controller\Controller;
use Framework\Http\Request;

class CommandProxyController extends Controller
{
    public function runAction(Request $request)
    {
        $input = $request->query->get('input');
    }
}